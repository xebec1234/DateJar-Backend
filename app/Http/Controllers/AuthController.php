<?php


namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' =>$request->name,
            'email' =>$request->email,
            'password' => $request->password,
        ]);

         $token = $user->createToken('API Token')->plainTextToken;

    return response()->json([
        'user' => [
            'id' => $user->id,  
            'name' => $user->name,
            'email' => $user->email,
        ],
        'token' => $token], 201);
    }
    
    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
        'user' => [
            'id' => $user->id,  
            'name' => $user->name,
            'email' => $user->email,
        ], 
            'token' => $token], 200);
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            // Verify token with Google
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $request->id_token,
            ]);

            // Check if response is valid
            if (!$response || $response->failed()) {
                return response()->json(['message' => 'Invalid Google token'], 401);
            }

            $googleUser = $response->json();

            if (!is_array($googleUser) || empty($googleUser)) {
                return response()->json(['message' => 'Invalid Google token data'], 401);
            }

            // Verify audience
            if (!isset($googleUser['aud']) || $googleUser['aud'] !== config('services.google.client_id')) {
                return response()->json(['message' => 'Token audience mismatch'], 401);
            }

            // Find or create user
            $user = User::where('google_id', $googleUser['sub'] ?? null)
                ->orWhere('email', $googleUser['email'] ?? null)
                ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser['name'] ?? 'Google User',
                    'email' => $googleUser['email'] ?? null,
                    'google_id' => $googleUser['sub'] ?? null,
                    'avatar' => $googleUser['picture'] ?? null,
                    'email_verified_at' => now(),
                    'password' => null,
                ]);
            } else {
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser['sub'] ?? null,
                        'avatar' => $googleUser['picture'] ?? null,
                    ]);
                }
            }

            // Create Sanctum token
            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ],
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Google token verification failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
