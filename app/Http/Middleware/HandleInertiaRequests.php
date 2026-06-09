<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user(),
        ],
        'userStats' => function () use ($request) {
            if (! $request->user()) return null;

            $user = $request->user();

            $weeklyActivity = [];
            $weekStart = now()->startOfWeek(0);
            for ($i = 0; $i < 7; $i++) {
                $day = $weekStart->copy()->addDays($i);
                $weeklyActivity[] = \App\Models\Conversation::where('user_id', $user->id)
                    ->whereDate('created_at', $day)
                    ->count();
            }

            $completedScores = \App\Models\Conversation::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereNotNull('scores')
                ->get(['scores'])
                ->map(fn($c) => is_array($c->scores) ? $c->scores : json_decode($c->scores, true))
                ->filter();

            return [
                'total_sessions'     => \App\Models\Conversation::where('user_id', $user->id)->count(),
                'completed_sessions' => \App\Models\Conversation::where('user_id', $user->id)->where('status', 'completed')->count(),
                'avg_score'          => $completedScores->count()
                                            ? (int) round($completedScores->avg(fn($s) => $s['final'] ?? 0))
                                            : null,
                'best_score'         => $completedScores->count()
                                            ? (int) round($completedScores->max(fn($s) => $s['final'] ?? 0))
                                            : null,
                'this_week'          => array_sum($weeklyActivity),
                'weekly_activity'    => $weeklyActivity,
            ];
        },
    ]);
}
}
