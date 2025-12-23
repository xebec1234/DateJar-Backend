<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    // List the logged-in user's partner (if any)
    public function index(Request $request)
    {
        $userId = $request->user()->id; // ← use $request->user() instead of auth()->id()

        $partner = Partner::where('user_id1', $userId)
            ->orWhere('user_id2', $userId)
            ->first();

        if (!$partner) {
            return response()->json(['message' => 'No partner found'], 404);
        }

        return response()->json($partner);
    }

    // Connect with another user
    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:users,id',
        ]);

        $userId = $request->user()->id; // ← fixed here
        $partnerId = $request->partner_id;

        // Check if either user already has a partner
        $exists = Partner::where(function ($q) use ($userId, $partnerId) {
            $q->where('user_id1', $userId)
              ->orWhere('user_id2', $userId)
              ->orWhere('user_id1', $partnerId)
              ->orWhere('user_id2', $partnerId);
        })->exists();

        if ($exists) {
            return response()->json(['message' => 'One of the users is already connected'], 400);
        }

        $partner = Partner::create([
            'user_id1' => $userId,
            'user_id2' => $partnerId,
        ]);

        return response()->json([
            'message' => 'Partner connected successfully',
            'partner' => $partner,
        ], 201);
    }

    // Disconnect the partner
    public function destroy(Request $request, $id)
    {
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json(['message' => 'Partner not found'], 404);
        }

        $userId = $request->user()->id; // ← fixed here
        if ($partner->user_id1 !== $userId && $partner->user_id2 !== $userId) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $partner->delete();

        return response()->json(['message' => 'Partner disconnected successfully'], 200);
    }
}
