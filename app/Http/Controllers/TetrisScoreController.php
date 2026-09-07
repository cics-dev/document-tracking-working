<?php

namespace App\Http\Controllers;

use App\Models\TetrisScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TetrisScoreController extends Controller
{
    public function index(): JsonResponse
    {
        $scores = TetrisScore::query()
            ->select('player_name', 'user_id')
            ->selectRaw('MAX(score) as score')
            ->with('user:id,name')
            ->groupBy('player_name', 'user_id')
            ->orderByDesc('score')
            ->orderBy('player_name')
            ->limit(10)
            ->get()
            ->values()
            ->map(fn (TetrisScore $score, int $index) => [
                'rank' => $index + 1,
                'name' => $score->player_name ?: $score->user?->name ?: 'Anonymous player',
                'score' => $score->score,
                'is_current_user' => auth()->id() === $score->user_id,
            ]);

        return response()->json([
            'scores' => $scores,
            'current_user' => auth()->user()?->only(['id', 'name']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:40'],
            'score' => ['required', 'integer', 'min:0', 'max:999999999'],
        ]);

        $best = TetrisScore::query()
            ->whereRaw('LOWER(player_name) = ?', [mb_strtolower($validated['name'])])
            ->max('score');

        if ($best === null || $validated['score'] > $best) {
            TetrisScore::create([
                'user_id' => $request->user()?->id,
                'player_name' => trim($validated['name']),
                'score' => $validated['score'],
            ]);
        }

        return $this->index();
    }
}