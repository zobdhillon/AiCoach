<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    conversations: {
        type: Array,
        default: () => [],
    },
});

// Clean client-side timestamp parser
const formatDate = (dateString) => {
    if (!dateString) return "Just now";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

// Computes dynamic performance colors based on AI feedback scoring
const getEvaluationStatus = (score, isCompleted) => {
    if (!isCompleted) {
        return {
            text: "In Progress",
            bg: "rgba(124, 58, 237, 0.1)",
            color: "var(--accent)",
        };
    }
    if (score === null || score === undefined) {
        return {
            text: "Ungraded",
            bg: "rgba(107, 91, 154, 0.1)",
            color: "var(--text-2)",
        };
    }
    if (score >= 80) {
        return {
            text: `Score: ${score}/100`,
            bg: "rgba(16, 185, 129, 0.1)",
            color: "#10b981",
        };
    }
    if (score >= 60) {
        return {
            text: `Score: ${score}/100`,
            bg: "rgba(245, 158, 11, 0.1)",
            color: "#f59e0b",
        };
    }
    return {
        text: `Score: ${score}/100`,
        bg: "rgba(239, 68, 68, 0.1)",
        color: "#ef4444",
    };
};
</script>

<template>
    <Head title="My Practice Sessions" />

    <AuthenticatedLayout>
        <template #title>Sessions</template>

        <div class="p-4 md:p-6">
            <!-- Header Frame -->
            <div class="mb-8">
                <h1
                    class="text-[clamp(1.3rem,4vw,1.9rem)] font-extrabold leading-tight tracking-tight"
                    style="color: var(--text)"
                >
                    Your Performance <span class="grad-text">Ledger</span>
                </h1>
                <p
                    class="mt-1.5 text-[12px] sm:text-[13px] font-normal leading-relaxed"
                    style="color: var(--text-2)"
                >
                    Review historical transcripts, trace performance breakdowns,
                    and hop back into uncompleted training simulations.
                </p>
            </div>

            <!-- Main Portfolio View Container -->
            <div
                v-if="conversations.length > 0"
                class="card overflow-hidden p-0"
            >
                <!-- Desktop Matrix Layout -->
                <div class="overflow-x-auto rounded-xl">
                    <table
                        class="min-w-[600px] w-full border-collapse text-left"
                    >
                        <thead>
                            <tr
                                class="border-b text-[10px] sm:text-[11px] font-bold tracking-wider uppercase select-none"
                                style="
                                    border-color: var(--border);
                                    color: var(--text-3);
                                "
                            >
                                <th class="py-4 px-6 font-semibold">
                                    Scenario Module
                                </th>
                                <th class="py-4 px-6 font-semibold">
                                    Date Attempted
                                </th>
                                <th class="py-4 px-6 font-semibold">
                                    AI Evaluation
                                </th>
                                <th class="py-4 px-6 font-semibold text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-[var(--border)] text-xs font-medium"
                            style="color: var(--text)"
                        >
                            <tr
                                v-for="session in conversations"
                                :key="session.id"
                                class="transition-colors duration-150 hover:bg-[rgba(255,255,255,0.25)] dark:hover:bg-[rgba(255,255,255,0.02)]"
                            >
                                <!-- Title + Meta Block -->
                                <td class="py-4 px-6">
                                    <div
                                        class="font-bold text-sm"
                                        style="color: var(--text)"
                                    >
                                        {{
                                            session.scenario?.title ||
                                            "Custom Simulation Rehearsal"
                                        }}
                                    </div>
                                    <div
                                        class="text-[11px] font-normal mt-0.5"
                                        style="color: var(--text-2)"
                                    >
                                        Coach Identity:
                                        {{
                                            session.scenario?.ai_role ||
                                            "Assessor"
                                        }}
                                    </div>
                                </td>

                                <!-- Date Frame -->
                                <td
                                    class="py-4 px-6 whitespace-nowrap"
                                    style="color: var(--text-2)"
                                >
                                    {{ formatDate(session.created_at) }}
                                </td>

                                <!-- Grade Matrix Badge -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span
                                        class="inline-block px-2.5 py-1 rounded-lg text-[11px] font-bold"
                                        :style="{
                                            backgroundColor:
                                                getEvaluationStatus(
                                                    session.score,
                                                    session.is_completed,
                                                ).bg,
                                            color: getEvaluationStatus(
                                                session.score,
                                                session.is_completed,
                                            ).color,
                                        }"
                                    >
                                        {{
                                            getEvaluationStatus(
                                                session.score,
                                                session.is_completed,
                                            ).text
                                        }}
                                    </span>
                                </td>

                                <!-- Operational Navigation Trigger -->
                                <td
                                    class="py-4 px-6 text-right whitespace-nowrap"
                                >
                                    <Link
                                        :href="`/conversations/${session.id}`"
                                        class="inline-flex items-center gap-1 font-bold no-underline transition-transform duration-200 hover:translate-x-px"
                                        :style="{
                                            color: session.is_completed
                                                ? 'var(--text-2)'
                                                : 'var(--accent)',
                                        }"
                                    >
                                        {{
                                            session.is_completed
                                                ? "Review Feedback"
                                                : "Resume Session"
                                        }}
                                        <svg
                                            width="12"
                                            height="12"
                                            viewBox="0 0 12 12"
                                            fill="none"
                                        >
                                            <path
                                                d="M2 6h8M6 2l4 4-4 4"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Responsive Layout Fallback for Mobile Viewports -->
                <div class="block md:hidden divide-y divide-[var(--border)]">
                    <div
                        v-for="session in conversations"
                        :key="session.id"
                        class="p-4 flex flex-col gap-3 text-xs"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="font-bold text-sm truncate"
                                    style="color: var(--text)"
                                >
                                    {{
                                        session.scenario?.title ||
                                        "Custom Simulation Rehearsal"
                                    }}
                                </h3>
                                <p
                                    class="text-[11px] mt-0.5 truncate"
                                    style="color: var(--text-2)"
                                >
                                    Date: {{ formatDate(session.created_at) }}
                                </p>
                            </div>
                            <span
                                class="px-2 py-0.5 rounded-md text-[10px] font-bold whitespace-nowrap flex-shrink-0"
                                :style="{
                                    backgroundColor: getEvaluationStatus(
                                        session.score,
                                        session.is_completed,
                                    ).bg,
                                    color: getEvaluationStatus(
                                        session.score,
                                        session.is_completed,
                                    ).color,
                                }"
                            >
                                {{
                                    getEvaluationStatus(
                                        session.score,
                                        session.is_completed,
                                    ).text
                                }}
                            </span>
                        </div>

                        <Link
                            :href="`/conversations/${session.id}`"
                            class="flex w-full items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-xs font-semibold transition-all border text-center no-underline min-h-[44px]"
                            :style="{
                                backgroundColor: session.is_completed
                                    ? 'transparent'
                                    : 'var(--accent-bg)',
                                borderColor: 'var(--border)',
                                color: session.is_completed
                                    ? 'var(--text)'
                                    : 'var(--accent)',
                            }"
                        >
                            {{
                                session.is_completed
                                    ? "Review Transcript & Grades"
                                    : "Resume Open Rehearsal"
                            }}
                            <svg
                                width="12"
                                height="12"
                                viewBox="0 0 12 12"
                                fill="none"
                            >
                                <path
                                    d="M2 6h8M6 2l4 4-4 4"
                                    stroke="currentColor"
                                    stroke-width="1.4"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty History State Placeholder -->
            <div
                v-else
                class="card text-center p-6 sm:p-10 max-w-md mx-auto mt-12 flex flex-col items-center gap-4"
            >
                <div
                    class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
                    style="background: var(--bg); color: var(--accent)"
                >
                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M12 8v4l3 3" />
                        <circle cx="12" cy="12" r="10" />
                    </svg>
                </div>
                <div>
                    <h3
                        class="text-sm font-bold mb-1"
                        style="color: var(--text)"
                    >
                        No sessions found
                    </h3>
                    <p
                        class="text-xs leading-relaxed max-w-xs"
                        style="color: var(--text-2)"
                    >
                        Your portfolio dashboard is empty because you haven't
                        initiated any training scenarios yet.
                    </p>
                </div>
                <Link
                    href="/scenarios"
                    class="btn-primary px-5 py-2.5 text-xs font-semibold rounded-xl inline-flex items-center gap-1.5 no-underline text-white min-h-[44px]"
                >
                    Browse Scenario Library
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path
                            d="M2 6h8M6 2l4 4-4 4"
                            stroke="currentColor"
                            stroke-width="1.4"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
