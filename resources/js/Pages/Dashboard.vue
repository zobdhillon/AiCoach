<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalSessions: 0,
            completedSessions: 0,
            bestScore: null,
            currentStreak: 0,
            weeklyActivity: [0, 0, 0, 0, 0, 0, 0],
            skillAvgs: {
                clarity: 0,
                confidence: 0,
                objective: 0,
                adaptability: 0,
            },
            skillDeltas: {
                clarity: 0,
                confidence: 0,
                objective: 0,
                adaptability: 0,
            },
        }),
    },
    recentSessions: { type: Array, default: () => [] },
    featuredScenarios: { type: Array, default: () => [] },
});

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 12) return "Good Morning ";
    if (h < 17) return "Good Afternoon ";
    return "Good Evening ";
});

const userName = computed(() => {
    const name = props.stats.userName ?? user.value?.name ?? "there";
    return name.toUpperCase();
});

function formatDate(d) {
    const date = new Date(d);
    const diff = Math.floor((new Date() - date) / 86400000);
    if (diff === 0) return "Today";
    if (diff === 1) return "Yesterday";
    if (diff < 7) return `${diff}d ago`;
    return date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
}

function scoreColor(score) {
    if (score === null || score === undefined)
        return { text: "var(--text-3)", bg: "var(--border)" };
    if (score >= 80) return { text: "#f59e0b", bg: "rgba(245,158,11,0.1)" };
    if (score >= 60) return { text: "#10b981", bg: "rgba(16,185,129,0.1)" };
    return { text: "#ef4444", bg: "rgba(239,68,68,0.1)" };
}

const weekDays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
const todayIdx = new Date().getDay();

const maxActivity = computed(() =>
    Math.max(...(props.stats.weeklyActivity ?? [0, 0, 0, 0, 0, 0, 0]), 1),
);

// Sparkline SVG path generator
function sparklinePath(points, w, h) {
    if (!points || points.length === 0) return "";
    const max = Math.max(...points, 1);
    const min = Math.min(...points);
    const range = max - min || 1;
    const step = w / (points.length - 1);
    return points
        .map((v, i) => {
            const x = i * step;
            const y = h - ((v - min) / range) * (h - 4) - 2;
            return `${i === 0 ? "M" : "L"} ${x.toFixed(1)} ${y.toFixed(1)}`;
        })
        .join(" ");
}

const sessionSparkData = computed(() => {
    const activity = props.stats.weeklyActivity ?? [0, 0, 0, 0, 0, 0, 0];
    const scores = props.recentSessions
        .slice(0, 10)
        .map((s) => s.score ?? 50)
        .reverse();
    const streakTrend = activity.map((_, i) =>
        activity.slice(0, i + 1).reduce((a, b) => a + b, 0),
    );
    return {
        sessions: streakTrend.length ? streakTrend : [0, 1, 1, 2, 2, 3, 3],
        completed: activity,
        score:
            scores.length >= 2
                ? scores
                : [50, 55, 60, 65, 70, 72, 75, 78, 80, 85],
        streak: activity.map((v, i) =>
            i <= todayIdx ? (v > 0 ? i + 1 : 0) : 0,
        ),
    };
});

const statCards = computed(() => [
    {
        label: "Total Sessions",
        value: props.stats.totalSessions,
        sub: `+${props.stats.thisWeek ?? 0} this week`,
        subColor: "#7c3aed",
        icon: "layers",
        accent: "#7c3aed",
        glow: "rgba(124,58,237,0.08)",
        sparkColor: "#7c3aed",
        sparkData: sessionSparkData.value.sessions,
    },
    {
        label: "Completed Sessions",
        value: props.stats.completedSessions,
        sub: `+${props.stats.completedThisWeek ?? 0} this week`,
        subColor: "#10b981",
        icon: "check-circle",
        accent: "#10b981",
        glow: "rgba(16,185,129,0.08)",
        sparkColor: "#10b981",
        sparkData: sessionSparkData.value.completed,
    },
    {
        label: "Best Score",
        value:
            props.stats.bestScore !== null
                ? props.stats.bestScore + "/100"
                : "—",
        sub:
            props.stats.bestScore !== null
                ? `+${props.stats.scoreImprovement ?? 0} from last month`
                : "No sessions yet",
        subColor: "#f59e0b",
        icon: "star",
        accent:
            props.stats.bestScore >= 80
                ? "#f59e0b"
                : props.stats.bestScore >= 60
                  ? "#f59e0b"
                  : "#7c3aed",
        glow: "rgba(245,158,11,0.08)",
        sparkColor: "#f59e0b",
        sparkData: sessionSparkData.value.score,
    },
    {
        label: "Day Streak",
        value: props.stats.currentStreak,
        sub: `Best: ${props.stats.bestStreak ?? props.stats.currentStreak} days`,
        subColor: "#f97316",
        icon: "flame",
        accent: "#f97316",
        glow: "rgba(249,115,22,0.08)",
        sparkColor: "#f97316",
        sparkData: sessionSparkData.value.streak,
    },
]);

const skills = [
    {
        key: "clarity",
        label: "Clarity",
        // chat bubble icon path
        iconPath:
            "M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z",
    },
    {
        key: "confidence",
        label: "Confidence",
        // user icon
        iconPath:
            "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z",
    },
    {
        key: "objective",
        label: "Objective",
        // target icon
        iconPath:
            "M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12z M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4z",
    },
    {
        key: "adaptability",
        label: "Adaptability",
        // refresh icon
        iconPath:
            "M23 4v6h-6 M1 20v-6h6 M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15",
    },
];

function scenarioAccent(scenario) {
    const map = {
        purple: { color: "var(--accent)", bg: "var(--accent-bg)" },
        pink: { color: "#ec4899", bg: "rgba(236,72,153,0.1)" },
        teal: { color: "#10b981", bg: "rgba(16,185,129,0.1)" },
        amber: { color: "#f59e0b", bg: "rgba(245,158,11,0.1)" },
        blue: { color: "#6366f1", bg: "rgba(99,102,241,0.1)" },
        coral: { color: "#ef4444", bg: "rgba(239,68,68,0.1)" },
    };
    return map[scenario?.color] ?? map.purple;
}
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #title>Dashboard</template>

        <div class="p-4 md:p-6 space-y-5">
            <!-- ── GREETING BANNER ─────────────────────────────── -->
            <div
                class="relative overflow-hidden rounded-2xl border"
                style="
                    background: var(--banner-bg);
                    border-color: var(--banner-border);
                "
            >
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-6 px-4 py-5 sm:px-7 sm:py-6"
                >
                    <!-- Left: greeting text -->
                    <div class="relative z-10 flex-1 min-w-0">
                        <p
                            class="text-[11px] sm:text-[13px] font-medium"
                            style="color: var(--text-2)"
                        >
                            {{ greeting }},
                            <strong
                                class="font-extrabold"
                                style="color: var(--text)"
                                >{{ userName }}</strong
                            >
                        </p>
                        <h2
                            class="mt-1 text-[clamp(1rem,4vw,1.6rem)] font-extrabold leading-tight tracking-tight"
                            style="color: var(--text)"
                        >
                            Keep practicing, keep growing!
                        </h2>
                        <p
                            class="mt-1 text-[11px] sm:text-[12px]"
                            style="color: var(--text-2)"
                        >
                            {{
                                stats.currentStreak > 0
                                    ? `You're on a great streak. Let's continue your progress.`
                                    : "Ready to practice? Pick a scenario below."
                            }}
                        </p>
                        <Link
                            :href="route('scenarios.index')"
                            class="mt-4 inline-flex items-center gap-1.5 rounded-full px-4 sm:px-5 py-2 text-[11px] sm:text-[12px] font-semibold text-white transition-all min-h-[44px] sm:min-h-auto justify-center sm:justify-start"
                            style="
                                background: var(--gradient-accent);
                                box-shadow: var(--shadow-btn);
                            "
                            onmouseover="
                                this.style.opacity = '0.9';
                                this.style.transform = 'translateY(-1px)';
                            "
                            onmouseout="
                                this.style.opacity = '1';
                                this.style.transform = 'translateY(0)';
                            "
                        >
                            Start Practice
                            <svg
                                width="12"
                                height="12"
                                viewBox="0 0 12 12"
                                fill="none"
                            >
                                <path
                                    d="M2 6h8M6 2l4 4-4 4"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </Link>
                    </div>

                    <!-- Right: level card (hidden on mobile) -->
                    <div
                        class="relative z-10 hidden sm:flex flex-col items-center gap-2 flex-shrink-0 rounded-2xl px-5 py-4"
                        style="
                            background: var(--banner-level-bg);
                            border: 1px solid var(--border-strong);
                            box-shadow: var(--shadow-md);
                        "
                    >
                        <div class="flex items-center gap-3">
                            <!-- Hexagon icon badge -->
                            <svg
                                width="36"
                                height="36"
                                viewBox="0 0 36 36"
                                fill="none"
                            >
                                <defs>
                                    <linearGradient
                                        id="hexGrad"
                                        x1="0"
                                        y1="0"
                                        x2="1"
                                        y2="1"
                                    >
                                        <stop
                                            offset="0%"
                                            stop-color="#7c3aed"
                                        />
                                        <stop
                                            offset="100%"
                                            stop-color="#a855f7"
                                        />
                                    </linearGradient>
                                </defs>
                                <path
                                    d="M18 3 L31 10.5 L31 25.5 L18 33 L5 25.5 L5 10.5 Z"
                                    fill="url(#hexGrad)"
                                />
                                <polygon
                                    points="18 10 20.5 14.5 25.5 15.5 22 19 22.5 24 18 21.5 13.5 24 14 19 10.5 15.5 15.5 14.5"
                                    fill="white"
                                    opacity="0.95"
                                />
                            </svg>

                            <div>
                                <div
                                    class="text-[10px] font-semibold uppercase tracking-wider"
                                    style="color: var(--text-3)"
                                >
                                    Current Level
                                </div>
                                <div
                                    class="text-[14px] font-extrabold leading-tight"
                                    style="color: var(--text)"
                                >
                                    {{
                                        stats.completedSessions >= 20
                                            ? "Level 4"
                                            : stats.completedSessions >= 10
                                              ? "Level 3"
                                              : stats.completedSessions >= 5
                                                ? "Level 2"
                                                : "Level 1"
                                    }}
                                </div>
                                <div
                                    class="text-[10px]"
                                    style="color: var(--text-3)"
                                >
                                    {{
                                        stats.completedSessions >= 20
                                            ? "Advanced Communicator"
                                            : stats.completedSessions >= 10
                                              ? "Intermediate Communicator"
                                              : stats.completedSessions >= 5
                                                ? "Developing Speaker"
                                                : "Beginner"
                                    }}
                                </div>
                            </div>

                            <!-- Progress ring -->
                            <svg width="52" height="52" viewBox="0 0 52 52">
                                <defs>
                                    <linearGradient
                                        id="ringGrad"
                                        x1="0"
                                        y1="0"
                                        x2="1"
                                        y2="0"
                                    >
                                        <stop
                                            offset="0%"
                                            stop-color="#7c3aed"
                                        />
                                        <stop
                                            offset="100%"
                                            stop-color="#a855f7"
                                        />
                                    </linearGradient>
                                </defs>
                                <circle
                                    cx="26"
                                    cy="26"
                                    r="20"
                                    fill="none"
                                    stroke="var(--border-strong)"
                                    stroke-width="3.5"
                                />
                                <circle
                                    cx="26"
                                    cy="26"
                                    r="20"
                                    fill="none"
                                    stroke="url(#ringGrad)"
                                    stroke-width="3.5"
                                    stroke-linecap="round"
                                    :stroke-dasharray="2 * Math.PI * 20"
                                    :stroke-dashoffset="
                                        2 *
                                        Math.PI *
                                        20 *
                                        (1 -
                                            Math.min(
                                                (stats.completedSessions % 10) /
                                                    10,
                                                1,
                                            ))
                                    "
                                    transform="rotate(-90 26 26)"
                                    style="
                                        transition: stroke-dashoffset 1s ease;
                                    "
                                />
                                <text
                                    x="26"
                                    y="30"
                                    text-anchor="middle"
                                    font-size="10"
                                    font-weight="800"
                                    fill="var(--text)"
                                >
                                    {{
                                        Math.round(
                                            Math.min(
                                                (stats.completedSessions % 10) /
                                                    10,
                                                1,
                                            ) * 100,
                                        )
                                    }}%
                                </text>
                            </svg>
                        </div>
                        <div class="text-[10px]" style="color: var(--text-3)">
                            to next level
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── STAT CARDS ──────────────────────────────────── -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div
                    v-for="(card, i) in statCards"
                    :key="i"
                    class="card flex flex-col sm:flex-row items-start justify-between gap-3 p-3 sm:p-4"
                    style="
                        transition:
                            box-shadow 250ms ease,
                            transform 250ms ease;
                    "
                    onmouseover="
                        this.style.transform = 'translateY(-2px)';
                        this.style.boxShadow = '0 12px 32px rgba(0,0,0,0.1)';
                    "
                    onmouseout="
                        this.style.transform = 'translateY(0)';
                        this.style.boxShadow = '';
                    "
                >
                    <!-- Left: icon + text -->
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                            :style="{ background: card.glow }"
                        >
                            <!-- Layers -->
                            <svg
                                v-if="card.icon === 'layers'"
                                width="17"
                                height="17"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                :style="{ color: card.accent }"
                            >
                                <polygon points="12 2 2 7 12 12 22 7 12 2" />
                                <polyline points="2 17 12 22 22 17" />
                                <polyline points="2 12 12 17 22 12" />
                            </svg>
                            <!-- Check -->
                            <svg
                                v-else-if="card.icon === 'check-circle'"
                                width="17"
                                height="17"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                :style="{ color: card.accent }"
                            >
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            <!-- Star -->
                            <svg
                                v-else-if="card.icon === 'star'"
                                width="17"
                                height="17"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                :style="{ color: card.accent }"
                            >
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"
                                />
                            </svg>
                            <!-- Flame -->
                            <svg
                                v-else-if="card.icon === 'flame'"
                                width="17"
                                height="17"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                :style="{ color: card.accent }"
                            >
                                <path
                                    d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"
                                />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div
                                class="text-[1.65rem] font-extrabold leading-none tracking-tight"
                                :style="{ color: card.accent }"
                            >
                                {{ card.value }}
                            </div>
                            <div
                                class="mt-0.5 text-[12px] font-semibold"
                                style="color: var(--text)"
                            >
                                {{ card.label }}
                            </div>
                            <div
                                class="text-[11px] font-medium"
                                :style="{ color: card.subColor }"
                            >
                                {{ card.sub }}
                            </div>
                        </div>
                    </div>

                    <!-- Right: sparkline -->
                    <div class="flex-shrink-0 self-center overflow-hidden">
                        <svg
                            width="48"
                            height="24"
                            viewBox="0 0 64 32"
                            fill="none"
                            class="w-full max-w-[64px]"
                        >
                            <path
                                :d="sparklinePath(card.sparkData, 64, 32)"
                                :stroke="card.sparkColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                fill="none"
                                opacity="0.7"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- ── BOTTOM 3-COLUMN ROW ─────────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Your Skills -->
                <div class="card p-4 sm:p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <span
                                class="text-[13px] font-bold"
                                style="color: var(--text)"
                                >Your Skills</span
                            >
                            <p class="text-[11px]" style="color: var(--text-3)">
                                Average across completed sessions
                            </p>
                        </div>
                        <Link
                            :href="route('conversations.index')"
                            class="text-[11px] font-semibold transition-opacity hover:opacity-70"
                            style="color: var(--accent)"
                        >
                            View details
                        </Link>
                    </div>

                    <div class="space-y-4">
                        <div v-for="skill in skills" :key="skill.key">
                            <div
                                class="mb-1.5 flex items-center justify-between"
                            >
                                <div class="flex items-center gap-1.5">
                                    <!-- skill icon -->
                                    <div
                                        class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-md"
                                        style="
                                            background: rgba(124, 58, 237, 0.1);
                                        "
                                    >
                                        <svg
                                            width="11"
                                            height="11"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="#8b5cf6"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path :d="skill.iconPath" />
                                        </svg>
                                    </div>
                                    <span
                                        class="text-[12px] font-semibold"
                                        style="color: var(--text-2)"
                                    >
                                        {{ skill.label }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="text-[11px] font-bold"
                                        style="color: var(--text)"
                                    >
                                        {{
                                            stats.completedSessions === 0
                                                ? "—"
                                                : (stats.skillAvgs?.[
                                                      skill.key
                                                  ] ?? 0) + "%"
                                        }}
                                    </span>
                                    <span
                                        v-if="
                                            stats.completedSessions > 0 &&
                                            (stats.skillDeltas?.[skill.key] ??
                                                0) !== 0
                                        "
                                        class="text-[10px] font-semibold"
                                        style="color: #10b981"
                                    >
                                        ▲ {{ stats.skillDeltas?.[skill.key] }}%
                                    </span>
                                </div>
                            </div>
                            <!-- Progress bar — purple gradient matching reference -->
                            <div
                                class="h-2 w-full overflow-hidden rounded-full"
                                style="background: var(--border)"
                            >
                                <div
                                    class="h-full rounded-full transition-all duration-700"
                                    :style="{
                                        width:
                                            stats.completedSessions === 0
                                                ? '0%'
                                                : (stats.skillAvgs?.[
                                                      skill.key
                                                  ] ?? 0) + '%',
                                        background: `linear-gradient(90deg, #8b5cf6, #8b5cf6cc)`,
                                    }"
                                />
                            </div>
                        </div>
                    </div>

                    <span
                        v-if="stats.completedSessions === 0"
                        class="mt-4 inline-block rounded-full px-2.5 py-1 text-[10px] font-semibold"
                        style="background: var(--amber-bg); color: var(--amber)"
                    >
                        Complete a session to unlock
                    </span>
                </div>

                <!-- Weekly Activity -->
                <div class="card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <span
                                class="text-[13px] font-bold"
                                style="color: var(--text)"
                                >Weekly Activity</span
                            >
                            <p class="text-[11px]" style="color: var(--text-3)">
                                Sessions this week
                            </p>
                        </div>
                        <Link
                            :href="route('conversations.index')"
                            class="text-[11px] font-semibold transition-opacity hover:opacity-70"
                            style="color: var(--accent)"
                        >
                            View all →
                        </Link>
                    </div>

                    <div
                        class="flex items-end justify-between gap-1.5"
                        style="height: clamp(80px, 20vw, 120px)"
                    >
                        <div
                            v-for="(count, idx) in stats.weeklyActivity ?? [
                                0, 0, 0, 0, 0, 0, 0,
                            ]"
                            :key="idx"
                            class="flex flex-1 flex-col items-center justify-end gap-1.5"
                        >
                            <div
                                class="relative w-full transition-all duration-700"
                                :style="{
                                    height:
                                        count > 0
                                            ? Math.max(
                                                  14,
                                                  Math.round(
                                                      (count / maxActivity) *
                                                          88,
                                                  ),
                                              ) + 'px'
                                            : '6px',
                                    background:
                                        idx === todayIdx
                                            ? 'linear-gradient(180deg, #7c3aed, #a855f7)'
                                            : count > 0
                                              ? 'rgba(124,58,237,0.25)'
                                              : 'var(--border)',
                                    borderRadius: '5px 5px 3px 3px',
                                    boxShadow:
                                        idx === todayIdx && count > 0
                                            ? '0 4px 12px rgba(124,58,237,0.35)'
                                            : 'none',
                                }"
                            >
                                <div
                                    v-if="count > 0"
                                    class="absolute inset-x-0 -top-4 text-center text-[9px] font-bold"
                                    :style="{
                                        color:
                                            idx === todayIdx
                                                ? 'var(--accent)'
                                                : 'var(--text-2)',
                                    }"
                                >
                                    {{ count }}
                                </div>
                            </div>
                            <span
                                class="text-[10px] font-semibold"
                                :style="
                                    idx === todayIdx
                                        ? 'color: var(--accent)'
                                        : 'color: var(--text-3)'
                                "
                            >
                                {{ weekDays[idx].slice(0, 3) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Recent Sessions -->
                <div class="card overflow-hidden">
                    <div
                        class="flex items-center justify-between border-b px-5 py-4"
                        style="border-color: var(--border)"
                    >
                        <span
                            class="text-[13px] font-bold"
                            style="color: var(--text)"
                            >Recent Sessions</span
                        >
                        <Link
                            :href="route('conversations.index')"
                            class="text-[11px] font-semibold transition-opacity hover:opacity-70"
                            style="color: var(--accent)"
                        >
                            View all
                        </Link>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="recentSessions.length === 0"
                        class="flex flex-col items-center py-10 text-center px-5"
                    >
                        <div
                            class="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl"
                            style="background: var(--accent-bg)"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                style="color: var(--text-3)"
                            >
                                <path
                                    d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                                />
                            </svg>
                        </div>
                        <p
                            class="text-[13px] font-semibold"
                            style="color: var(--text-2)"
                        >
                            No sessions yet
                        </p>
                        <p
                            class="mt-1 text-[11px]"
                            style="color: var(--text-3)"
                        >
                            Pick a scenario to start.
                        </p>
                        <Link
                            :href="route('scenarios.index')"
                            class="mt-3 rounded-xl px-4 py-2 text-xs font-semibold text-white"
                            style="
                                background: var(--gradient-accent);
                                box-shadow: var(--shadow-btn);
                            "
                            onmouseover="
                                this.style.opacity = '0.9';
                                this.style.transform = 'translateY(-1px)';
                            "
                            onmouseout="
                                this.style.opacity = '1';
                                this.style.transform = 'translateY(0)';
                            "
                        >
                            Browse Scenarios
                        </Link>
                    </div>

                    <!-- Session rows -->
                    <div v-else>
                        <Link
                            v-for="session in recentSessions.slice(0, 4)"
                            :key="session.id"
                            :href="route('conversations.show', session.id)"
                            class="flex items-center gap-3 border-b px-5 py-3 transition-colors last:border-b-0"
                            style="border-color: var(--border)"
                            onmouseover="
                                this.style.background = 'var(--accent-bg)'
                            "
                            onmouseout="this.style.background = 'transparent'"
                        >
                            <!-- Scenario icon -->
                            <div
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl"
                                :style="{
                                    background: scenarioAccent(session.scenario)
                                        .bg,
                                }"
                            >
                                <svg
                                    width="13"
                                    height="13"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    :style="{
                                        color: scenarioAccent(session.scenario)
                                            .color,
                                    }"
                                >
                                    <path
                                        d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                                    />
                                </svg>
                            </div>

                            <!-- Info -->
                            <div class="min-w-0 flex-1">
                                <div
                                    class="truncate text-[12px] font-semibold"
                                    style="color: var(--text)"
                                >
                                    {{ session.scenario?.title ?? "Session" }}
                                </div>
                                <div
                                    class="text-[10px]"
                                    style="color: var(--text-3)"
                                >
                                    {{ formatDate(session.created_at) }}
                                </div>
                            </div>

                            <!-- Score + Review -->
                            <div
                                class="flex flex-shrink-0 items-center gap-1.5"
                            >
                                <span
                                    v-if="session.score !== null"
                                    class="rounded-lg px-1.5 py-0.5 text-[10px] font-bold"
                                    :style="{
                                        color: scoreColor(session.score).text,
                                        background: scoreColor(session.score)
                                            .bg,
                                    }"
                                >
                                    {{ session.score }}/100
                                </span>
                                <span
                                    class="rounded-lg px-2 py-1 text-[10px] font-semibold"
                                    :style="
                                        session.is_completed
                                            ? 'background: var(--accent-bg); color: var(--accent)'
                                            : 'background: linear-gradient(135deg, #7c3aed, #a855f7); color: white'
                                    "
                                >
                                    {{
                                        session.is_completed
                                            ? "Review"
                                            : "Resume"
                                    }}
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
