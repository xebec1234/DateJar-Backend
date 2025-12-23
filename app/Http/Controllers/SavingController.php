<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use Illuminate\Http\Request;

class SavingController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->savings;
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'note' => 'nullable|string',
        ]);

        $saving = Saving::create([
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'note' => $request->note,
        ]);

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

        $saving->update($request->only(['amount', 'note']));
        return response()->json($saving);
    }

    public function destroy(Request $request, Saving $saving)
    {
        abort_if($saving->user_id !== $request->user()->id, 403);

        $saving->delete();
        return response()->json(null, 204);
    }
}
