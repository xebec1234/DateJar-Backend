<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SavingController extends Controller
{
    // List all savings for the logged-in user
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $savings = Saving::where('user_id', $userId)->get();

        return response()->json($savings);
    }

public function active(Request $request)
{
    $userId = $request->user()->id;
    $today = Carbon::today();
    $weekStart = Carbon::now()->startOfWeek();

    // Get active goal for the partner of the user
    $activeGoal = Goal::where('partner_id', function ($q) use ($userId) {
            $q->select('id')
              ->from('partners')
              ->where(function ($sub) use ($userId) {
                  $sub->where('user_id1', $userId)
                      ->orWhere('user_id2', $userId);
              });
        })
        ->whereDate('target_date', '>=', $today)
        ->orderBy('target_date')
        ->first();

    if (!$activeGoal) {
        return response()->json(null, 200);
    }

    // Get the partner ID
    $partner = $activeGoal->partner;
    $partnerId = $partner->user_id1 === $userId ? $partner->user_id2 : $partner->user_id1;

    // User savings
    $userDaily = Saving::where('user_id', $userId)
        ->where('goal_id', $activeGoal->id)
        ->whereDate('created_at', $today)
        ->sum('daily_savings');

    $userWeekly = Saving::where('user_id', $userId)
        ->where('goal_id', $activeGoal->id)
        ->whereBetween('created_at', [$weekStart, $today])
        ->sum('daily_savings');

    $userTotal = Saving::where('user_id', $userId)
        ->where('goal_id', $activeGoal->id)
        ->sum('daily_savings');

    // Partner savings
    $partnerDaily = Saving::where('user_id', $partnerId)
        ->where('goal_id', $activeGoal->id)
        ->whereDate('created_at', $today)
        ->sum('daily_savings');

    $partnerWeekly = Saving::where('user_id', $partnerId)
        ->where('goal_id', $activeGoal->id)
        ->whereBetween('created_at', [$weekStart, $today])
        ->sum('daily_savings');

    $partnerTotal = Saving::where('user_id', $partnerId)
        ->where('goal_id', $activeGoal->id)
        ->sum('daily_savings');

    return response()->json([
        'goal_id' => $activeGoal->id,
        'daily_savings_user' => $userDaily,
        'daily_savings_partner' => $partnerDaily,
        'weekly_savings_user' => $userWeekly,
        'weekly_savings_partner' => $partnerWeekly,
        'total_savings' => $userTotal + $partnerTotal,
    ]);
}

    // Create or update a saving for today
    public function store(Request $request)
    {
        $request->validate([
            'goal_id' => 'required|exists:goals,id',
            'partner_id' => 'required|exists:partners,id',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $userId = $request->user()->id;
        $goal = Goal::findOrFail($request->goal_id);
        $today = Carbon::today();
        $currentWeekStart = $today->startOfWeek(); // Monday

        // Check if there's already a saving record for this user + goal + today
        $saving = Saving::where('goal_id', $goal->id)
            ->where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->first();

        if (!$saving) {
            // Create new saving
            $saving = Saving::create([
                'goal_id' => $goal->id,
                'partner_id' => $request->partner_id,
                'user_id' => $userId,
                'daily_savings' => $request->amount,
                'weekly_savings' => $request->amount,
                'total_savings' => $request->amount,
                'note' => $request->note,
            ]);
        } else {
            // Update existing daily savings
            $saving->daily_savings += $request->amount;
            $saving->note = $request->note ?? $saving->note;

            // Weekly savings: recalc for the current week
            $weeklySavings = Saving::where('user_id', $userId)
                ->where('goal_id', $goal->id)
                ->whereBetween('created_at', [$currentWeekStart, $today])
                ->sum('daily_savings');

            $saving->weekly_savings = $weeklySavings;

            // Total savings
            $totalSavings = Saving::where('user_id', $userId)
                ->where('goal_id', $goal->id)
                ->sum('daily_savings');

            $saving->total_savings = $totalSavings;

            $saving->save();
        }

        return response()->json($saving, 201);
    }

    public function show(Request $request, Saving $saving)
    {
        abort_if($saving->user_id !== $request->user()->id, 403);
        return $saving;
    }

    public function update(Request $request, Saving $saving)
    {
        abort_if($saving->user_id !== $request->user()->id, 403);

        $saving->update($request->only(['daily_savings', 'note']));

        return response()->json($saving);
    }

    public function destroy(Request $request, Saving $saving)
    {
        abort_if($saving->user_id !== $request->user()->id, 403);

        $saving->delete();

        return response()->json(null, 204);
    }
}
