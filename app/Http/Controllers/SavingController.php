<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use Illuminate\Http\Request;

class SavingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         return Saving::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
        ]);

        $saving = saving::create($request->all());

        return response()->json($saving, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Saving $saving)
    {
        return $saving;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Saving $saving)
    {
        $saving->update($request->all());
        return response()->json($saving, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Saving $saving)
    {
        $saving->delete();
        return response()->json(null, 204);
    }
}
