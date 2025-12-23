<?php


namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'id' => $user->id,   // ← return 10-digit ID
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
            'id' => $user->id,   // ← include the 10-digit ID
            'name' => $user->name,
            'email' => $user->email,
        ], 
            'token' => $token], 200);
    }
}
