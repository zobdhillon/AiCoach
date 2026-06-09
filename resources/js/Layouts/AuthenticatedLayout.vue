<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";

const page = usePage();
const user = computed(() => page.props.auth.user);
const currentUrl = computed(() => page.url);

const stats = computed(
    () =>
        page.props.userStats ?? {
            total_sessions: 0,
            completed_sessions: 0,
            avg_score: null,
            best_score: null,
            this_week: 0,
            weekly_activity: [0, 0, 0, 0, 0, 0, 0],
        },
);

// ── Sidebar state ─────────────────────────────────────────
const collapsed = ref(false);
const isMobile = ref(false);

let breakpoint = "desktop";
const syncViewport = () => {
    const w = window.innerWidth;
    isMobile.value = w < 768;
    const newBp = w < 768 ? "mobile" : w < 1025 ? "tablet" : "desktop";
    if (newBp !== breakpoint) {
        collapsed.value = newBp !== "desktop";
        breakpoint = newBp;
    }
};

const toggleSidebar = () => {
    collapsed.value = !collapsed.value;
};

// Called on nav link clicks — closes sidebar on mobile only
const closeSidebarOnMobile = () => {
    if (isMobile.value) collapsed.value = true;
};

const showBackdrop = computed(() => isMobile.value && !collapsed.value);

// ── Mobile smooth navigation ─────────────────────────────
let closeTimeout = null;

const handleMobileNavClick = (href, e) => {
    if (!isMobile.value || collapsed.value) {
        // Desktop or sidebar already closed – navigate immediately
        return true;
    }

    e.preventDefault(); // Stop Inertia from navigating right away

    // Close the sidebar
    collapsed.value = true;

    // Clear any previous timeout
    if (closeTimeout) clearTimeout(closeTimeout);

    // Wait for the sidebar collapse animation (matches duration-300 in CSS)
    closeTimeout = setTimeout(() => {
        router.visit(href);
        closeTimeout = null;
    }, 300);

    return false;
};

const handleMobileLogout = async (e) => {
    if (!isMobile.value || collapsed.value) {
        // Desktop or sidebar already closed – logout immediately
        router.post(route("logout"));
        return;
    }

    e.preventDefault();

    // Close the sidebar
    collapsed.value = true;

    if (closeTimeout) clearTimeout(closeTimeout);
    closeTimeout = setTimeout(() => {
        router.post(route("logout"));
        closeTimeout = null;
    }, 300);
};

// ── Theme ─────────────────────────────────────────────────
const isDark = ref(false);
const applyTheme = (dark) => {
    document.documentElement.classList.toggle("dark", dark);
    localStorage.setItem("theme", dark ? "dark" : "light");
    isDark.value = dark;
};
const toggleDark = () => applyTheme(!isDark.value);

// ── Avatar panel ──────────────────────────────────────────
const showPanel = ref(false);
const avatarBtn = ref(null);
const panelPos = ref({});

const togglePanel = () => {
    if (!showPanel.value && avatarBtn.value) {
        const r = avatarBtn.value.getBoundingClientRect();
        panelPos.value = {
            top: r.bottom + 10 + "px",
            right: window.innerWidth - r.right + "px",
        };
    }
    showPanel.value = !showPanel.value;
};

// Only closes the avatar panel, never touches the sidebar
const closePanel = (e) => {
    if (!showPanel.value) return;
    const avatarEl = document.getElementById("avatar-btn");
    const panelEl = document.getElementById("admin-panel");
    if (avatarEl?.contains(e.target) || panelEl?.contains(e.target)) return;
    showPanel.value = false;
};

// ── Lifecycle ─────────────────────────────────────────────
onMounted(() => {
    const saved = localStorage.getItem("theme");
    const prefersDark = window.matchMedia(
        "(prefers-color-scheme: dark)",
    ).matches;
    applyTheme(saved ? saved === "dark" : prefersDark);
    syncViewport();
    window.addEventListener("resize", syncViewport);
    document.addEventListener("click", closePanel);

});

onUnmounted(() => {
    window.removeEventListener("resize", syncViewport);
    document.removeEventListener("click", closePanel);
    if (closeTimeout) clearTimeout(closeTimeout);
});

// ── User helpers ──────────────────────────────────────────
const initials = computed(() => {
    if (!user.value?.name) return "??";
    return user.value.name
        .split(" ")
        .map((n) => n[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
});

const upperName = computed(() => user.value?.name?.toUpperCase() ?? "");

// ── Stats helpers (for admin panel) ──────────────────────
const scoreColor = (score) => {
    if (score === null || score === undefined) return "var(--text-3)";
    if (score >= 80) return "var(--green)";
    if (score >= 60) return "var(--amber)";
    return "var(--red)";
};

const scoreLabel = (score) => {
    if (score === null || score === undefined) return "—";
    return score + "/100";
};

const completionRate = computed(() => {
    const t = stats.value.total_sessions;
    const c = stats.value.completed_sessions;
    if (!t) return 0;
    return Math.round((c / t) * 100);
});

const weekDays = ["S", "M", "T", "W", "T", "F", "S"];
const todayIndex = computed(() => new Date().getDay());
const maxActivity = computed(() =>
    Math.max(...(stats.value.weekly_activity ?? [0, 0, 0, 0, 0, 0, 0]), 1),
);

const logout = () => router.post(route("logout"));
</script>

<template>
    <div
        class="relative h-screen overflow-hidden"
        style="background: var(--bg)"
    >
        <!-- Mobile backdrop — clicking closes sidebar -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showBackdrop"
                class="fixed inset-0 z-[98] bg-black/50"
                @click.stop="collapsed = true"
            />
        </Transition>

        <div class="relative flex h-full">
            <!-- SIDEBAR -->
            <aside
                :class="[
                    'flex flex-col flex-shrink-0 border-r transition-all duration-300 ease-in-out',
                    isMobile && collapsed
                        ? 'w-0 border-r-0 overflow-hidden'
                        : '',
                    isMobile && !collapsed
                        ? 'fixed inset-y-0 left-0 z-[150] w-[180px] overflow-hidden'
                        : '',
                    !isMobile && collapsed ? 'w-[52px] overflow-hidden' : '',
                    !isMobile && !collapsed ? 'w-[180px] overflow-hidden' : '',
                ]"
                style="
                    background: var(--bg);
                    border-color: var(--border-strong);
                "
            >
                <!-- Sidebar header -->
                <div
                    class="flex h-[56px] flex-shrink-0 items-center border-b"
                    style="border-color: var(--border)"
                >
                    <template v-if="!collapsed">
                        <div
                            class="flex flex-1 items-center gap-2 pl-4 min-w-0 overflow-hidden"
                        >
                            <div
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg"
                                style="
                                    background: var(--gradient-accent);
                                    box-shadow: var(--shadow-btn);
                                "
                            >
                                <svg
                                    width="18"
                                    height="18"
                                    viewBox="0 0 18 18"
                                    fill="none"
                                >
                                    <rect
                                        x="3"
                                        y="2"
                                        width="3"
                                        height="14"
                                        rx="1.5"
                                        fill="white"
                                        opacity="0.95"
                                    />
                                    <path
                                        d="M6 2h4.5a3.5 3.5 0 0 1 0 7H6"
                                        stroke="white"
                                        stroke-width="2.2"
                                        stroke-linecap="round"
                                        fill="none"
                                        opacity="0.95"
                                    />
                                    <path
                                        d="M9 9l4.5 7"
                                        stroke="white"
                                        stroke-width="2.2"
                                        stroke-linecap="round"
                                        opacity="0.8"
                                    />
                                </svg>
                            </div>
                            <span
                                class="grad-text whitespace-nowrap text-sm font-extrabold tracking-tight"
                            >
                                Rehearse
                            </span>
                        </div>
                        <!-- X to close on mobile, collapse arrow on desktop -->
                        <button
                            v-if="isMobile"
                            @click.stop="collapsed = true"
                            class="mr-3 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg border-0 bg-transparent transition-all duration-200"
                            style="color: var(--text-3)"
                        >
                            <svg
                                width="15"
                                height="15"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                        <button
                            v-else
                            @click.stop="collapsed = true"
                            title="Collapse sidebar"
                            class="mr-3 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg border-0 bg-transparent transition-all duration-200"
                            style="color: var(--text-3)"
                            onmouseover="
                                this.style.background = 'var(--accent-bg)';
                                this.style.color = 'var(--accent)';
                            "
                            onmouseout="
                                this.style.background = 'transparent';
                                this.style.color = 'var(--text-3)';
                            "
                        >
                            <svg
                                width="15"
                                height="15"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="2"
                                />
                                <line x1="9" y1="3" x2="9" y2="21" />
                                <polyline points="6 10 4.5 12 6 14" />
                            </svg>
                        </button>
                    </template>

                    <template v-else>
                        <button
                            @click.stop="collapsed = false"
                            title="Expand sidebar"
                            class="mx-auto flex h-7 w-7 items-center justify-center rounded-lg border-0 bg-transparent transition-all duration-200"
                            style="color: var(--text-3)"
                            onmouseover="
                                this.style.background = 'var(--accent-bg)';
                                this.style.color = 'var(--accent)';
                            "
                            onmouseout="
                                this.style.background = 'transparent';
                                this.style.color = 'var(--text-3)';
                            "
                        >
                            <svg
                                width="15"
                                height="15"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="2"
                                />
                                <line x1="9" y1="3" x2="9" y2="21" />
                                <polyline points="12 10 13.5 12 12 14" />
                            </svg>
                        </button>
                    </template>
                </div>

                <!-- Nav links -->
                <nav class="flex-1 overflow-y-auto py-4">
                    <Link
                        v-for="item in [
                            {
                                href: route('dashboard'),
                                label: 'Dashboard',
                                active: route().current('dashboard'),
                                icon: 'M3 3h7v7H3zm11 0h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z',
                            },
                            {
                                href: route('scenarios.index'),
                                label: 'Scenarios',
                                active: route().current('scenarios.*'),
                                icon: 'M5 3l14 9-14 9V3z',
                            },
                            {
                                href: route('conversations.index'),
                                label: 'Sessions',
                                active: route().current('conversations.*'),
                                icon: 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z',
                            },
                        ]"
                        :key="item.href"
                        :href="item.href"
                        :title="collapsed ? item.label : ''"
                        @click="handleMobileNavClick(item.href, $event)"
                        :class="[
                            'relative mx-2 my-1 flex items-center gap-2.5 rounded-xl px-2.5 py-2.5 text-xs font-medium transition-all duration-200',
                            collapsed ? 'justify-center' : '',
                            item.active
                                ? 'font-semibold'
                                : 'hover:bg-[var(--accent-bg)] hover:text-[var(--accent)]',
                        ]"
                        :style="
                            item.active
                                ? 'background: var(--accent-bg); color: var(--accent); border-left: 3px solid var(--accent); padding-left: 9px;'
                                : 'color: var(--text);'
                        "
                    >
                        <svg
                            class="h-[15px] w-[15px] flex-shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path :d="item.icon" />
                        </svg>
                        <span v-if="!collapsed" class="truncate">{{
                            item.label
                        }}</span>
                    </Link>
                </nav>

                <!-- Sidebar footer -->
                <div
                    class="flex-shrink-0 border-t pb-3 pt-2"
                    style="border-color: var(--border)"
                >
                    <Link
                        :href="route('profile.edit')"
                        :title="collapsed ? 'Edit profile' : ''"
                        @click="
                            handleMobileNavClick(route('profile.edit'), $event)
                        "
                        :class="[
                            'mx-2 flex items-center gap-2.5 rounded-xl px-2.5 py-2 transition-all duration-200 hover:bg-[var(--accent-bg)] hover:text-[var(--accent)]',
                            collapsed ? 'justify-center' : '',
                        ]"
                        style="color: var(--text-2)"
                    >
                        <div
                            class="avatar !h-[26px] !w-[26px] !text-[10px] flex-shrink-0"
                        >
                            {{ initials }}
                        </div>
                        <div v-if="!collapsed" class="min-w-0 overflow-hidden">
                            <div
                                class="truncate text-[11px] font-semibold"
                                style="color: var(--text)"
                            >
                                {{ upperName }}
                            </div>
                            <div
                                class="text-[10px]"
                                style="color: var(--text-3)"
                            >
                                Edit profile →
                            </div>
                        </div>
                    </Link>

                    <button
                        @click="logout"
                        :title="collapsed ? 'Log out' : ''"
                        :class="[
                            'mx-2 mt-0.5 flex w-[calc(100%-16px)] items-center gap-2.5 rounded-xl border-0 bg-transparent px-2.5 py-2 font-[inherit] text-xs transition-all duration-200',
                            collapsed ? 'justify-center' : '',
                        ]"
                        style="color: var(--red); cursor: pointer"
                        onmouseover="this.style.background = 'var(--red-bg)'"
                        onmouseout="this.style.background = 'transparent'"
                    >
                        <svg
                            class="h-[15px] w-[15px] flex-shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                        <span v-if="!collapsed">Log out</span>
                    </button>
                </div>
            </aside>

            <!-- MAIN COLUMN -->
            <div
                class="flex min-w-0 flex-1 flex-col overflow-hidden"
                style="background: var(--bg-surface)"
            >
                <!-- Topbar -->
                <header
                    class="relative flex h-[56px] flex-shrink-0 items-center justify-between border-b px-4 md:px-6"
                    style="
                        background: var(--bg-surface);
                        border-color: var(--border-strong);
                    "
                >
                    <div class="flex min-w-0 items-center gap-2 flex-1">
                        <!-- Hamburger — mobile only -->
                        <button
                            v-if="isMobile"
                            @click.stop="toggleSidebar"
                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border-0 bg-transparent transition-all duration-200"
                            style="color: var(--text-2)"
                            aria-label="Open menu"
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
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <line x1="3" y1="12" x2="21" y2="12" />
                                <line x1="3" y1="18" x2="21" y2="18" />
                            </svg>
                        </button>

                        <h1
                            class="truncate text-[13px] sm:text-[15px] font-extrabold leading-none tracking-tight"
                            style="color: var(--text)"
                        >
                            <slot name="title">Rehearse</slot>
                        </h1>
                    </div>

                    <!-- Right controls -->
                    <div class="flex items-center gap-1 sm:gap-2">
                        <button
                            @click="toggleDark"
                            :title="
                                isDark
                                    ? 'Switch to light mode'
                                    : 'Switch to dark mode'
                            "
                            class="flex h-8 w-8 items-center justify-center rounded-lg border-0 bg-transparent transition-all duration-200"
                            style="color: var(--text-3)"
                            onmouseover="
                                this.style.background = 'var(--accent-bg)';
                                this.style.color = 'var(--accent)';
                            "
                            onmouseout="
                                this.style.background = 'transparent';
                                this.style.color = 'var(--text-3)';
                            "
                        >
                            <svg
                                v-if="isDark"
                                width="15"
                                height="15"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <circle cx="12" cy="12" r="5" />
                                <line x1="12" y1="1" x2="12" y2="3" />
                                <line x1="12" y1="21" x2="12" y2="23" />
                                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                                <line
                                    x1="18.36"
                                    y1="18.36"
                                    x2="19.78"
                                    y2="19.78"
                                />
                                <line x1="1" y1="12" x2="3" y2="12" />
                                <line x1="21" y1="12" x2="23" y2="12" />
                                <line
                                    x1="4.22"
                                    y1="19.78"
                                    x2="5.64"
                                    y2="18.36"
                                />
                                <line
                                    x1="18.36"
                                    y1="5.64"
                                    x2="19.78"
                                    y2="4.22"
                                />
                            </svg>
                            <svg
                                v-else
                                width="15"
                                height="15"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"
                                />
                            </svg>
                        </button>

                        <div class="hidden md:block text-right">
                            <div
                                class="text-[11px] font-semibold"
                                style="color: var(--text)"
                            >
                                {{ upperName }}
                            </div>
                            <div
                                class="text-[10px]"
                                style="color: var(--text-3)"
                            >
                                {{ user?.email }}
                            </div>
                        </div>

                        <button
                            id="avatar-btn"
                            ref="avatarBtn"
                            @click.stop="togglePanel"
                            class="avatar !h-8 sm:!h-9 !w-8 sm:!w-9 !text-xs cursor-pointer border-0 bg-transparent p-0 transition-all duration-150"
                            :style="
                                showPanel
                                    ? 'box-shadow: 0 0 0 2px var(--accent), 0 0 0 4px var(--accent-bg-hover)'
                                    : ''
                            "
                        >
                            {{ initials }}
                        </button>
                    </div>
                </header>

                <!-- Page content -->
                <main class="flex-1 overflow-y-auto min-h-0">
                    <div class="min-h-[calc(100vh-56px)]">
                        <Transition name="v" mode="out-in">
                            <div :key="$page.url">
                                <slot />
                            </div>
                        </Transition>
                    </div>
                </main>
            </div>
        </div>

        <!-- ADMIN PANEL -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200 origin-top-right"
                enter-from-class="opacity-0 scale-95 -translate-y-1"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition-all duration-150 origin-top-right"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 -translate-y-1"
            >
                <div
                    v-if="showPanel"
                    id="admin-panel"
                    class="fixed z-[9999] w-[290px] sm:w-[300px] overflow-hidden rounded-2xl border"
                    :style="{
                        top: panelPos.top,
                        right: panelPos.right,
                        background: 'var(--bg-surface)',
                        borderColor: 'var(--border-strong)',
                        boxShadow: 'var(--shadow-lg)',
                    }"
                >
                    <!-- Panel header -->
                    <div
                        class="relative overflow-hidden px-5 py-4"
                        style="background: var(--accent-bg)"
                    >
                        <div
                            class="pointer-events-none absolute inset-0"
                            style="
                                background: radial-gradient(
                                    circle at 80% 50%,
                                    var(--accent-bg-hover),
                                    transparent 60%
                                );
                            "
                        />
                        <div class="relative">
                            <div
                                class="mb-0.5 text-[10px] font-bold uppercase tracking-widest"
                                style="color: var(--accent); opacity: 0.85"
                            >
                                My Progress
                            </div>
                            <div
                                class="text-sm font-bold"
                                style="color: var(--text)"
                            >
                                {{ upperName }}
                            </div>
                            <div
                                class="mt-0.5 text-[11px]"
                                style="color: var(--text-3)"
                            >
                                {{ user?.email }}
                            </div>
                        </div>
                    </div>

                    <!-- Stats row -->
                    <div
                        class="grid grid-cols-3 border-b"
                        style="border-color: var(--border)"
                    >
                        <div
                            v-for="(stat, i) in [
                                {
                                    label: 'Sessions',
                                    value: stats.total_sessions,
                                    colored: false,
                                },
                                {
                                    label: 'Done',
                                    value: stats.completed_sessions,
                                    colored: false,
                                },
                                {
                                    label: 'Avg',
                                    value: scoreLabel(stats.avg_score),
                                    colored: true,
                                    raw: stats.avg_score,
                                },
                            ]"
                            :key="i"
                            class="flex flex-col items-center justify-center px-2 py-3 text-center"
                            :class="i < 2 ? 'border-r' : ''"
                            :style="i < 2 ? 'border-color: var(--border)' : ''"
                        >
                            <span
                                class="text-lg font-extrabold leading-none"
                                :style="
                                    stat.colored
                                        ? `color: ${scoreColor(stat.raw)}`
                                        : 'color: var(--text)'
                                "
                                >{{ stat.value }}</span
                            >
                            <span
                                class="mt-1 text-[10px] font-medium"
                                style="color: var(--text-3)"
                                >{{ stat.label }}</span
                            >
                        </div>
                    </div>

                    <!-- Completion rate -->
                    <div class="px-5 pb-3 pt-4">
                        <div class="mb-1.5 flex items-center justify-between">
                            <span
                                class="text-[11px] font-semibold"
                                style="color: var(--text-2)"
                                >Completion Rate</span
                            >
                            <span
                                class="text-[11px] font-bold"
                                style="color: var(--accent)"
                                >{{ completionRate }}%</span
                            >
                        </div>
                        <div
                            class="h-1.5 w-full overflow-hidden rounded-full"
                            style="background: var(--border)"
                        >
                            <div
                                class="h-full rounded-full transition-all duration-700"
                                style="background: var(--gradient-accent)"
                                :style="{ width: completionRate + '%' }"
                            />
                        </div>
                    </div>

                    <!-- Weekly bars -->
                    <div class="px-5 pb-4">
                        <div class="mb-3 flex items-center justify-between">
                            <span
                                class="text-[11px] font-semibold"
                                style="color: var(--text-2)"
                                >This Week</span
                            >
                            <span
                                class="text-[11px]"
                                style="color: var(--text-3)"
                            >
                                {{ stats.this_week }} session{{
                                    stats.this_week !== 1 ? "s" : ""
                                }}
                            </span>
                        </div>
                        <div class="flex h-10 items-end justify-between gap-1">
                            <div
                                v-for="(
                                    count, idx
                                ) in stats.weekly_activity ?? [
                                    0, 0, 0, 0, 0, 0, 0,
                                ]"
                                :key="idx"
                                class="flex flex-1 flex-col items-center gap-1"
                            >
                                <div
                                    class="w-full transition-all duration-500"
                                    :style="{
                                        height:
                                            count > 0
                                                ? Math.max(
                                                      6,
                                                      Math.round(
                                                          (count /
                                                              maxActivity) *
                                                              32,
                                                      ),
                                                  ) + 'px'
                                                : '4px',
                                        background:
                                            idx === todayIndex
                                                ? 'var(--gradient-accent)'
                                                : count > 0
                                                  ? 'var(--accent-bg-hover)'
                                                  : 'var(--border)',
                                        boxShadow:
                                            idx === todayIndex && count > 0
                                                ? 'var(--shadow-btn)'
                                                : 'none',
                                        borderRadius: '3px',
                                    }"
                                />
                                <span
                                    class="text-[9px] font-medium"
                                    :style="
                                        idx === todayIndex
                                            ? 'color: var(--accent)'
                                            : 'color: var(--text-3)'
                                    "
                                    >{{ weekDays[idx] }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Best score -->
                    <div
                        class="flex items-center justify-between border-t px-5 py-3"
                        style="border-color: var(--border)"
                    >
                        <div>
                            <span
                                class="text-[10px] font-semibold uppercase tracking-wider"
                                style="color: var(--text-3)"
                                >Best Score</span
                            >
                            <div
                                class="text-sm font-extrabold"
                                :style="`color: ${scoreColor(stats.best_score)}`"
                            >
                                {{ scoreLabel(stats.best_score) }}
                            </div>
                        </div>
                        <svg width="40" height="40" viewBox="0 0 40 40">
                            <circle
                                cx="20"
                                cy="20"
                                r="15"
                                fill="none"
                                stroke="var(--border-strong)"
                                stroke-width="3"
                            />
                            <circle
                                cx="20"
                                cy="20"
                                r="15"
                                fill="none"
                                :stroke="scoreColor(stats.best_score)"
                                stroke-width="3"
                                stroke-linecap="round"
                                :stroke-dasharray="2 * Math.PI * 15"
                                :stroke-dashoffset="
                                    2 *
                                    Math.PI *
                                    15 *
                                    (1 - (stats.best_score ?? 0) / 100)
                                "
                                transform="rotate(-90 20 20)"
                                style="transition: stroke-dashoffset 0.7s ease"
                            />
                            <text
                                x="20"
                                y="24"
                                text-anchor="middle"
                                font-size="9"
                                font-weight="700"
                                fill="var(--text)"
                            >
                                {{ stats.best_score ?? "—" }}
                            </text>
                        </svg>
                    </div>

                    <!-- Panel footer -->
                    <div
                        class="flex border-t"
                        style="border-color: var(--border)"
                    >
                        <Link
                            :href="route('profile.edit')"
                            @click="showPanel = false"
                            class="flex flex-1 items-center justify-center gap-1.5 border-r py-2.5 text-xs font-medium transition-colors duration-150"
                            style="
                                color: var(--text-2);
                                border-color: var(--border);
                            "
                            onmouseover="
                                this.style.background = 'var(--accent-bg)';
                                this.style.color = 'var(--accent)';
                            "
                            onmouseout="
                                this.style.background = 'transparent';
                                this.style.color = 'var(--text-2)';
                            "
                        >
                            <svg
                                class="h-3.5 w-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"
                                />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Edit Profile
                        </Link>
                        <button
                            @click="logout"
                            class="flex flex-1 cursor-pointer items-center justify-center gap-1.5 border-0 bg-transparent py-2.5 font-[inherit] text-xs font-medium transition-colors duration-150"
                            style="color: var(--red)"
                            onmouseover="
                                this.style.background = 'var(--red-bg)'
                            "
                            onmouseout="this.style.background = 'transparent'"
                        >
                            <svg
                                class="h-3.5 w-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"
                                />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" y1="12" x2="9" y2="12" />
                            </svg>
                            Log out
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
