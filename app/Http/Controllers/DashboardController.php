<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Scenario;
use Inertia\Inertia;
use App\Models\UserStat;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ── Core counts ───────────────────────────────────────────
        $totalSessions = Conversation::where('user_id', $user->id)->count();

        $completedSessions = Conversation::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $scenariosTried = Conversation::where('user_id', $user->id)
            ->distinct('scenario_id')
            ->count('scenario_id');

        // ── Best score (parse JSON scores column, get highest final) ──
        $bestScore = Conversation::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('scores')
            ->get(['scores'])
            ->map(fn($c) => is_array($c->scores) ? ($c->scores['final'] ?? null) : data_get(json_decode($c->scores, true), 'final'))
            ->filter()
            ->max();

        // ── Current streak (consecutive days with at least 1 session) ──
        $currentStreak = 0;
        $checkDate     = now()->startOfDay();

        while (true) {
            $hasSession = Conversation::where('user_id', $user->id)
                ->whereDate('created_at', $checkDate)
                ->exists();

            if (! $hasSession) break;

            $currentStreak++;
            $checkDate = $checkDate->subDay();
        }

        // ── Weekly activity (Sun–Sat of current week) ─────────────
        $weeklyActivity = [];
        $weekStart = now()->startOfWeek(0); // 0 = Sunday

        for ($i = 0; $i < 7; $i++) {
            $day              = $weekStart->copy()->addDays($i);
            $weeklyActivity[] = Conversation::where('user_id', $user->id)
                ->whereDate('created_at', $day)
                ->count();
        }

        // ── Recent sessions (last 5) ──────────────────────────────
        $recentSessions = Conversation::where('user_id', $user->id)
            ->with('scenario:id,title,color')
            ->latest()
            ->take(5)
            ->get(['id', 'scenario_id', 'created_at', 'status', 'scores'])
            ->map(function ($c) {
                $scores = is_array($c->scores) ? $c->scores : ($c->scores ? json_decode($c->scores, true) : null);
                return [
                    'id'           => $c->id,
                    'created_at'   => $c->created_at,
                    'is_completed' => $c->status === 'completed',
                    'score'        => $scores['final'] ?? null,
                    'scenario' => $c->scenario ? [
                        'id'    => $c->scenario->id,
                        'title' => $c->scenario->title,
                        'color' => $c->scenario->color ?? 'purple',
                    ] : null,
                ];
            });

        // ── Featured scenarios ─────────────────────────────────────
        $featuredScenarios = Scenario::select('id', 'title', 'description')
            ->take(3)
            ->get();

        // ── Skill averages (from scores JSON across completed sessions) ──
        $completedScores = Conversation::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('scores')
            ->get(['scores'])
            ->map(fn($c) => is_array($c->scores) ? $c->scores : json_decode($c->scores, true))
            ->filter();

        $skillAvgs = [
            'clarity'      => 0,
            'confidence'   => 0,
            'objective'    => 0,
            'adaptability' => 0,
        ];

        if ($completedScores->count() > 0) {
            foreach (array_keys($skillAvgs) as $skill) {
                $skillAvgs[$skill] = (int) round(
                    $completedScores->avg(fn($s) => $s[$skill] ?? 0)
                );
            }
        }

        $avgThisMonth = Conversation::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get(['scores'])
            ->map(fn($c) => data_get(is_array($c->scores) ? $c->scores : json_decode($c->scores, true), 'final'))
            ->filter()->avg() ?? 0;

        $avgLastMonth = Conversation::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->get(['scores'])
            ->map(fn($c) => data_get(is_array($c->scores) ? $c->scores : json_decode($c->scores, true), 'final'))
            ->filter()->avg() ?? 0;

        $scoreImprovement = (int) round($avgThisMonth - $avgLastMonth);

        $lastMonthScores = Conversation::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereNotNull('scores')
            ->get(['scores'])
            ->map(fn($c) => is_array($c->scores) ? $c->scores : json_decode($c->scores, true))
            ->filter();

        $skillDeltas = [];
        foreach (array_keys($skillAvgs) as $skill) {
            $lastAvg = $lastMonthScores->count() > 0
                ? $lastMonthScores->avg(fn($s) => $s[$skill] ?? 0)
                : null;

            $skillDeltas[$skill] = ($lastAvg !== null)
                ? (int) round($skillAvgs[$skill] - $lastAvg)
                : 0;
        }

        $completedThisWeek = Conversation::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [now()->startOfWeek(0), now()->endOfWeek(6)])
            ->count();

        $userStat = UserStat::firstOrCreate(['user_id' => $user->id]);
        if ($currentStreak > $userStat->best_streak) {
            $userStat->update(['best_streak' => $currentStreak]);
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'userName'           => $user->name,
                'totalSessions'      => $totalSessions,
                'completedSessions'  => $completedSessions,
                'scenariosTried'     => $scenariosTried,
                'bestScore'          => $bestScore,
                'currentStreak'      => $currentStreak,
                'bestStreak'         => $userStat->best_streak,
                'thisWeek'           => array_sum($weeklyActivity),
                'completedThisWeek'  => $completedThisWeek,
                'scoreImprovement'   => $scoreImprovement,
                'weeklyActivity'     => $weeklyActivity,
                'skillAvgs'          => $skillAvgs,
                'skillDeltas'        => $skillDeltas,
            ],
            'recentSessions'    => $recentSessions,
            'featuredScenarios' => $featuredScenarios,
        ]);
    }
}
