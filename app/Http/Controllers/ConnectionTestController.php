<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConnectionTestController extends Controller
{
    public const DOWNLOAD_BYTES = 256 * 1024;

    public const UPLOAD_BYTES = 64 * 1024;

    private const HEADERS = ['Cache-Control' => 'private, no-store, no-transform'];

    public function download(Request $request): Response
    {
        if (! $request->boolean('sample')) {
            return response()->noContent(headers: self::HEADERS);
        }

        return response(random_bytes(self::DOWNLOAD_BYTES), 200, self::HEADERS + [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function upload(Request $request): Response
    {
        abort_unless($request->header('Content-Type') === 'application/octet-stream', 415);

        $sample = stream_get_contents($request->getContent(true), self::UPLOAD_BYTES + 1);
        abort_unless($sample !== false && strlen($sample) === self::UPLOAD_BYTES, 400);

        return response()->noContent(headers: self::HEADERS);
    }
}
