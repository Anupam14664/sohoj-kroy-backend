<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CKEditorController extends Controller
{
    public function upload(Request $request)
    {
        if($request->hasFile('upload')) {
            // Validate the file
            $request->validate([
                'upload' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Store the file
            $path = $request->file('upload')->store('public/ckeditor');
            $url = Storage::url($path);

            // Return the response
            return response()->json([
                'uploaded' => true,
                'url' => asset($url)
            ]);
        }

        return response()->json([
            'uploaded' => false,
            'error' => ['message' => 'File upload failed']
        ]);
    }
}