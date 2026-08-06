<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class ChatBotController extends Controller
{
    public function sendChat(Request $request)
    {
        $faqs = $this->loadFAQs(storage_path('app/public/faqs.json'));

        $result = OpenAI::chat()->create([
            'model' => 'gpt-5',
            'messages' => array_merge($faqs, [
                ['role' => 'user', 'content' => $request->input('message')]
            ]),
        ]);

        $response = array_reduce(
            $result->toArray()['choices'],
            fn($carry, $choice) => $carry . $choice['message']['content'],
            ''
        );

        return response()->json(['response' => $response]);
    }

    private function loadFAQs($path)
    {
        if (!file_exists($path)) return [];
        $data = json_decode(file_get_contents($path), true);
        return $data ?? [];
    }
}
