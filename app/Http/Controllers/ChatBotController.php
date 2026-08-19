<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class ChatBotController extends Controller
{
    public function sendChat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);
        $faqs = $this->loadFAQs(storage_path('app/public/faqs.json'));

        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-5',
                'messages' => [...$faqs, ['role' => 'user', 'content' => $validated['message']]],
            ]);
        } catch (Throwable $exception) {
            Log::error('Chat request failed.', ['exception' => $exception]);

            return response()->json(['message' => 'The chat service is temporarily unavailable.'], 503);
        }

        $response = collect($result->toArray()['choices'] ?? [])
            ->pluck('message.content')
            ->filter()
            ->implode('');

        if ($response === '') {
            return response()->json(['message' => 'The chat service returned an empty response.'], 502);
        }

        return response()->json(['response' => $response]);
    }

    private function loadFAQs(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }
}
