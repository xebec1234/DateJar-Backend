<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GoalController extends Controller
{
    // List all goals for the logged-in user's partner
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $goals = Goal::whereHas('partner', function($q) use ($userId) {
            $q->where('user_id1', $userId)
              ->orWhere('user_id2', $userId);
        })->get();

        return response()->json($goals);
    }

    // Create a new goal
    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'total_goal' => 'required|integer|min:1',
            'target_date' => 'required|date|after_or_equal:today',
        ]);

        $partnerId = $request->partner_id;
        $totalGoal = $request->total_goal;
        $targetDate = Carbon::parse($request->target_date);
        $today = Carbon::today();

        // Split goal between 2 users
        $individualGoal = $totalGoal / 2;

        // Count weeks from today to target date
        $weekCount = max(1, $today->diffInWeeks($targetDate));

        // Compute weekly goal per user
        $weeklyGoal = $individualGoal / $weekCount;

        // Create goal
        $goal = Goal::create([
            'partner_id' => $partnerId,
            'total_goal' => $totalGoal,
            'individual_goal' => $individualGoal,
            'weekly_goal' => $weeklyGoal,
            'target_date' => $targetDate,
        ]);

        return response()->json([
            'message' => 'Goal created successfully',
            'goal' => $goal,
        ], 201);
    }

    // Delete a goal
    public function destroy(Request $request, $id)
    {
        $goal = Goal::find($id);
        if (!$goal) {
            return response()->json(['message' => 'Goal not found'], 404);
        }

        $userId = $request->user()->id;
        $partner = $goal->partner;

        if ($partner->user_id1 !== $userId && $partner->user_id2 !== $userId) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $goal->delete();

        return response()->json(['message' => 'Goal deleted successfully'], 200);
    }
}
