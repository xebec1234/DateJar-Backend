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
