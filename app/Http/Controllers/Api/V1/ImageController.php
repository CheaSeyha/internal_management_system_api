<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function show($filename)
    {
        $path = 'private/' . $filename;

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        $file = Storage::get($path);
        $type = Storage::mimeType($path);

        return response($file, 200)
            ->header('Content-Type', $type);
    }
}
