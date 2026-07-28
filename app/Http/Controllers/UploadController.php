<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /** 5 MB — matches every other CLIuno backend template. */
    private const MAX_UPLOAD_KILOBYTES = 5120;

    /** The extension comes from the detected mime, never from the client's filename. */
    private const ALLOWED_IMAGE_TYPES = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /**
     * Store the bytes and hand back a URL. Attaching it to a user or a post is the
     * caller's job (PATCH /users/current, POST /posts) — upload mutates nothing.
     */
    public function image(Request $request): JsonResponse
    {
        if (! $request->hasFile('file')) {
            return response()->json([
                'status' => 'warning',
                'message' => 'No image file provided',
            ], 400);
        }

        $file = $request->file('file');

        if (! array_key_exists($file->getMimeType(), self::ALLOWED_IMAGE_TYPES)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unsupported image type: '.$file->getMimeType(),
            ], 400);
        }

        if ($file->getSize() > self::MAX_UPLOAD_KILOBYTES * 1024) {
            return response()->json([
                'status' => 'error',
                'message' => 'Image exceeds the 5 MB limit',
            ], 413);
        }

        $filename = Str::uuid().'.'.self::ALLOWED_IMAGE_TYPES[$file->getMimeType()];

        // public/ is the docroot, so this is served at /uploads/<filename> with no symlink.
        $file->move(public_path('uploads'), $filename);

        // Absolute so a frontend on another origin can use it straight as an image source.
        $base = rtrim(env('PUBLIC_BASE_URL') ?: $request->getSchemeAndHttpHost(), '/');

        return response()->json([
            'status' => 'success',
            'message' => 'Image uploaded',
            'data' => [
                'url' => $base.'/uploads/'.$filename,
                'filename' => $filename,
            ],
        ], 201);
    }
}
