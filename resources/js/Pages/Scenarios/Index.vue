<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
    scenarios: Array,
});

const selectedScenario = ref(null);
const activeCategory = ref("All");
const activeDifficulty = ref("All");

const categories = computed(() => {
    const cats = [...new Set(props.scenarios.map((s) => s.category))];
    return ["All", ...cats];
});

const filteredScenarios = computed(() => {
    return props.scenarios.filter((s) => {
        const catMatch =
            activeCategory.value === "All" ||
            s.category === activeCategory.value;
        const diffMatch =
            activeDifficulty.value === "All" ||
            s.difficulty === activeDifficulty.value;
        return catMatch && diffMatch;
    });
});

const openPreview = (scenario) => (selectedScenario.value = scenario);
const closePreview = () => (selectedScenario.value = null);
const startSession = (scenarioId) => {
    router.post("/conversations", { scenario_id: scenarioId });
};

const difficultyMap = {
    Beginner: { bg: "var(--green-bg)", text: "var(--green)" },
    Intermediate: { bg: "var(--accent-bg)", text: "var(--accent)" },
    Advanced: { bg: "var(--red-bg)", text: "var(--red)" },
};

const colorMap = {
    Interviews: { bg: "var(--accent-bg)", color: "var(--accent)" },
    Career: { bg: "rgba(107, 79, 196, 0.12)", color: "#6b4fc4" },
    Sales: { bg: "rgba(30, 95, 138, 0.12)", color: "#1e5f8a" },
    Leadership: { bg: "rgba(180, 83, 9, 0.12)", color: "var(--amber)" },
    "Customer Service": { bg: "var(--green-bg)", color: "var(--green)" },
    Freelance: { bg: "rgba(45, 156, 138, 0.12)", color: "#2d9c8a" },
};
const getColor = (category) => colorMap[category] ?? colorMap["Interviews"];

const getDiff = (diff) => difficultyMap[diff] ?? difficultyMap["Intermediate"];
</script>

<template>
    <Head title="Scenarios" />

    <AuthenticatedLayout>
        <template #title>Scenarios</template>

        <div class="p-4 md:p-7">
            <!-- Heading -->
            <div class="mb-6">
                <div class="flex items-center gap-3">
                    <h1
                        class="text-[clamp(1.3rem,4vw,1.9rem)] font-extrabold leading-tight tracking-tight"
                        style="color: var(--text)"
                    >
                        What will you
                        <span class="grad-text">practice today?</span>
                    </h1>
                    <span
                        class="text-[11px] font-semibold px-2.5 py-1 rounded-full flex-shrink-0"
                        style="
                            background: var(--accent-bg);
                            color: var(--accent);
                            border: 1px solid var(--accent);
                        "
                    >
                        {{ scenarios.length }} scenarios
                    </span>
                </div>
                <p
                    class="mt-1.5 text-[12px] sm:text-[13px]"
                    style="color: var(--text-2)"
                >
                    Every simulation is a real conversation. The more you
                    practice, the more confident you get.
                </p>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-4 mb-4 md:mb-6">
                <!-- Category group -->
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="text-[10px] font-bold uppercase tracking-wider flex-shrink-0"
                        style="color: var(--text-3)"
                        >Category</span
                    >
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="cat in categories"
                            :key="cat"
                            @click="activeCategory = cat"
                            class="px-3 py-1.5 rounded-full text-[11px] sm:text-[12px] font-semibold cursor-pointer transition-all duration-200 min-h-[36px]"
                            :style="
                                activeCategory === cat
                                    ? 'background: var(--accent-bg); color: var(--accent); border: 1.5px solid var(--accent);'
                                    : 'background: var(--bg-surface); color: var(--text-2); border: 1px solid var(--border-strong);'
                            "
                        >
                            {{ cat }}
                        </button>
                    </div>
                </div>

                <!-- Divider -->
                <div
                    class="hidden sm:block w-px h-5 self-center flex-shrink-0"
                    style="background: var(--border-strong)"
                ></div>

                <!-- Difficulty group -->
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="text-[10px] font-bold uppercase tracking-wider flex-shrink-0"
                        style="color: var(--text-3)"
                        >Difficulty</span
                    >
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="diff in [
                                'All',
                                'Beginner',
                                'Intermediate',
                                'Advanced',
                            ]"
                            :key="diff"
                            @click="activeDifficulty = diff"
                            class="px-3 py-1.5 rounded-full text-[11px] sm:text-[12px] font-semibold cursor-pointer transition-all duration-200 min-h-[36px]"
                            :style="
                                activeDifficulty === diff
                                    ? 'background: var(--accent-bg); color: var(--accent); border: 1.5px solid var(--accent);'
                                    : 'background: var(--bg-surface); color: var(--text-2); border: 1px solid var(--border-strong);'
                            "
                        >
                            {{ diff }}
                        </button>
                    </div>
                </div>

                <!-- Count -->
                <span
                    class="sm:ml-auto text-[11px] sm:text-[12px]"
                    style="color: var(--text-3)"
                >
                    {{ filteredScenarios.length }} scenario{{
                        filteredScenarios.length !== 1 ? "s" : ""
                    }}
                </span>
            </div>

            <!-- Empty state -->
            <div
                v-if="filteredScenarios.length === 0"
                class="flex flex-col items-center justify-center py-20 gap-3"
            >
                <p
                    class="text-xs sm:text-sm font-medium"
                    style="color: var(--text-2)"
                >
                    No scenarios match your filters.
                </p>
                <button
                    @click="
                        activeCategory = 'All';
                        activeDifficulty = 'All';
                    "
                    class="btn-primary text-xs px-5 py-2 min-h-[44px]"
                >
                    Clear filters
                </button>
            </div>

            <!-- Scenarios grid -->
            <div
                v-else
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
            >
                <div
                    v-for="scenario in filteredScenarios"
                    :key="scenario.id"
                    class="card card-hover flex flex-col gap-3 p-4 md:p-5 h-full min-h-[220px] cursor-pointer"
                    @click="openPreview(scenario)"
                >
                    <!-- Top row: icon + difficulty badge -->
                    <div class="flex items-start justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl text-white flex-shrink-0"
                            :style="`background: ${getColor(scenario.category).bg}; color: ${getColor(scenario.category).color}`"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                                />
                            </svg>
                        </div>
                        <span
                            class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                            :style="`background: ${getDiff(scenario.difficulty).bg}; color: ${getDiff(scenario.difficulty).text}`"
                        >
                            {{ scenario.difficulty }}
                        </span>
                    </div>

                    <!-- Category + duration -->
                    <div class="flex items-center gap-2">
                        <span
                            class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                            style="
                                background: var(--bg-surface);
                                color: var(--text-2);
                                border: 1px solid var(--border-strong);
                            "
                        >
                            {{ scenario.category }}
                        </span>
                        <span
                            class="text-[11px] flex items-center gap-1"
                            style="color: var(--text-3)"
                        >
                            <svg
                                width="11"
                                height="11"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            {{ scenario.estimated_duration }} min
                        </span>
                    </div>

                    <!-- Title + description -->
                    <div class="flex flex-col gap-1">
                        <h2
                            class="text-sm font-bold line-clamp-2"
                            style="color: var(--text)"
                        >
                            {{ scenario.title }}
                        </h2>
                        <p
                            class="text-[12px] font-medium line-clamp-3"
                            style="color: var(--text-2); opacity: 0.75"
                        >
                            {{ scenario.description }}
                        </p>
                    </div>

                    <!-- CTA -->
                    <button
                        @click.stop="openPreview(scenario)"
                        class="explore-btn mt-auto w-full justify-center text-xs py-[10px] rounded-lg font-semibold inline-flex items-center gap-2 transition-all duration-200"
                    >
                        Explore Scenario
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
                    </button>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div
            v-if="selectedScenario"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-3 sm:p-4 bg-black/30 backdrop-blur-sm"
            @click.self="closePreview"
        >
            <div
                class="card w-full sm:max-w-xl max-h-[85vh] overflow-y-auto p-4 sm:p-6 lg:p-7 relative flex flex-col gap-5 rounded-t-2xl sm:rounded-2xl"
                style="background: var(--bg-surface)"
            >
                <!-- Close -->
                <button
                    @click="closePreview"
                    class="absolute top-4 right-4 p-1.5 rounded-xl bg-transparent border-0 cursor-pointer transition-colors"
                    style="color: var(--text-2)"
                >
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>

                <!-- Header -->
                <div class="flex items-center gap-3 mt-2">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl text-white flex-shrink-0"
                        :class="getColor(selectedScenario.color).iconClass"
                    >
                        <svg
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                            />
                        </svg>
                    </div>
                    <div>
                        <div
                            class="text-[10px] font-bold tracking-[0.1em] uppercase"
                            style="color: var(--text-3)"
                        >
                            Scenario Preview
                        </div>
                        <h2
                            class="text-base font-extrabold"
                            style="color: var(--text)"
                        >
                            {{ selectedScenario.title }}
                        </h2>
                    </div>
                </div>

                <!-- Meta badges -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span
                        class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full"
                        :style="`background: ${getDiff(selectedScenario.difficulty).bg}; color: ${getDiff(selectedScenario.difficulty).text}`"
                    >
                        {{ selectedScenario.category }}
                    </span>
                    <span
                        class="text-[11px] font-bold px-2.5 py-0.5 rounded-full"
                        :style="`background: ${getDiff(selectedScenario.difficulty).bg}; color: ${getDiff(selectedScenario.difficulty).text}`"
                    >
                        {{ selectedScenario.difficulty }}
                    </span>
                    <span
                        class="text-[11px] flex items-center gap-1"
                        style="color: var(--text-3)"
                    >
                        <svg
                            width="11"
                            height="11"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        {{ selectedScenario.estimated_duration }} min
                    </span>
                </div>

                <!-- Description -->
                <p class="text-xs leading-relaxed" style="color: var(--text-2)">
                    {{ selectedScenario.description }}
                </p>

                <!-- Roles -->
                <div
                    class="grid grid-cols-2 gap-4 p-4 rounded-xl text-xs"
                    style="
                        background: var(--bg-surface2);
                        border: 1px solid var(--border);
                    "
                >
                    <div class="flex flex-col gap-0.5">
                        <span
                            class="text-[10px] font-bold uppercase tracking-wider"
                            style="color: var(--text-3)"
                        >
                            Your role
                        </span>
                        <span class="font-semibold" style="color: var(--text)">
                            {{ selectedScenario.user_role || "Practitioner" }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span
                            class="text-[10px] font-bold uppercase tracking-wider"
                            style="color: var(--text-3)"
                        >
                            AI plays
                        </span>
                        <span class="font-semibold" style="color: var(--text)">
                            {{ selectedScenario.ai_role || "AI Coach" }}
                        </span>
                    </div>
                </div>

                <!-- Objectives -->
                <div
                    v-if="selectedScenario.objectives?.length"
                    class="flex flex-col gap-2"
                >
                    <h3
                        class="text-[11px] font-bold uppercase tracking-wider"
                        style="color: var(--text-2)"
                    >
                        Practice Objectives
                    </h3>
                    <ul class="list-none m-0 p-0 space-y-2">
                        <li
                            v-for="(obj, i) in selectedScenario.objectives"
                            :key="i"
                            class="flex items-start gap-2 text-xs leading-normal"
                            style="color: var(--text-2)"
                        >
                            <span
                                class="text-[14px] leading-none select-none"
                                style="color: var(--accent)"
                                >•</span
                            >
                            <span>{{ obj }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-end gap-2 pt-3 mt-2 border-t"
                    style="border-color: var(--border)"
                >
                    <button
                        @click="closePreview"
                        class="px-4 py-2 text-xs font-medium bg-transparent border-0 rounded-xl cursor-pointer"
                        style="color: var(--text-2)"
                    >
                        Back to Library
                    </button>
                    <button
                        @click="startSession(selectedScenario.id)"
                        class="btn-primary text-xs px-5 py-2"
                    >
                        Begin Session
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
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
