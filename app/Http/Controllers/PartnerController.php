<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    // List the logged-in user's partner (if any)
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Fetch partner with related user names
        $partner = Partner::with(['userOne', 'userTwo'])
            ->where('user_id1', $userId)
            ->orWhere('user_id2', $userId)
            ->first();

        if (!$partner) {
            return response()->json(['message' => 'No partner found'], 404);
        }

        // Determine who is the partner (the "other" user)
        $partnerUser = $partner->user_id1 == $userId ? $partner->userTwo : $partner->userOne;

        return response()->json([
            'id' => $partner->id,
            'user_id1' => $partner->user_id1,
            'user_id2' => $partner->user_id2,
            'partner_name' => $partnerUser ? $partnerUser->name : null,
            'created_at' => $partner->created_at,
            'updated_at' => $partner->updated_at,
        ]);
    }

    // Search for a user by ID
    public function search(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $searchId = $request->user_id;

        // Get the user
        $user = \App\Models\User::find($searchId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check if this user already has a partner
        $hasPartner = Partner::where('user_id1', $user->id)
            ->orWhere('user_id2', $user->id)
            ->exists();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'hasPartner' => $hasPartner,
        ]);
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
