<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Help;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HelpController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'problem' => 'required|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        try {
            $filePath = null;
            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('help_files', 'public');
            }

        $user = $request->user(); 
        

        
        $help = Help::create([
            'customer_id' => $user->id, 
            'problem' => $validated['problem'],
            'file' => asset('storage/'.$filePath),
        ]);

            return response()->json([
                'success' => true,
                'message' => 'Help request submitted successfully',
                'data' => $help
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit help request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}