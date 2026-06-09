<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, nextTick, onMounted } from "vue";

const props = defineProps({
    conversation: Object,
});

const localMessages = ref([...props.conversation.messages]);
const messagesEl = ref(null);
const messageText = ref("");
const processing = ref(false);

const isCompleted = ref(props.conversation.status === "completed");
const scores = ref(props.conversation.scores);

watch(
    () => props.conversation.status,
    (val) => {
        isCompleted.value = val === "completed";
    },
);

watch(
    () => props.conversation.scores,
    (val) => {
        scores.value = val;
    },
);
const showEndConfirm = ref(false);

const endSession = () => {
    showEndConfirm.value = false;
    router.post(`/conversations/${props.conversation.id}/complete`);
};

// Voice input
const isListening = ref(false);
let recognition = null;
let savedText = "";

const toggleVoice = () => {
    // Check browser support
    if (
        !("webkitSpeechRecognition" in window) &&
        !("SpeechRecognition" in window)
    ) {
        alert(
            "Voice input is not supported in your browser. Please use Chrome.",
        );
        return;
    }

    if (isListening.value) {
        recognition.stop();
        return;
    }

    const SpeechRecognition =
        window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();

    recognition.lang = "en-US";
    recognition.continuous = true;
    recognition.interimResults = true;

    savedText = messageText.value ? messageText.value + " " : "";

    recognition.onstart = () => {
        isListening.value = true;
    };

    recognition.onresult = (event) => {
        const transcript = Array.from(event.results)
            .map((result) => result[0].transcript)
            .join("");
        messageText.value = savedText + transcript;
    };

    recognition.onend = () => {
        isListening.value = false;
        savedText = messageText.value ? messageText.value + " " : "";
    };

    recognition.onerror = () => {
        isListening.value = false;
    };

    recognition.start();
};

watch(
    () => props.conversation.messages,
    (newMessages) => {
        if (newMessages.length >= localMessages.value.length) {
            localMessages.value = [...newMessages];
        }
    },
    { deep: true },
);

// Scroll to bottom whenever a new message appears
watch(
    () => localMessages.value.length,
    async () => {
        await nextTick();
        scrollToBottom();
    },
);

watch(processing, async (isProcessing) => {
    if (isProcessing) {
        await nextTick();
        scrollToBottom();
    }
});

function scrollToBottom() {
    const el = messagesEl.value;
    if (el) el.scrollTop = el.scrollHeight;
}

onMounted(() => nextTick(scrollToBottom));

const isUser = (role) => role === "user";

const sendMessage = () => {
    if (!messageText.value.trim() || processing.value) return;

    // Optimistically push the user message immediately
    localMessages.value.push({
        id: Date.now(),
        role: "user",
        content: messageText.value,
    });

    const payload = messageText.value;
    messageText.value = "";
    processing.value = true;

    axios
        .post(`/conversations/${props.conversation.id}/messages`, {
            message_text: payload,
        })
        .then((response) => {
            if (response.data.auto_complete) {
                scores.value = response.data.scores;
                isCompleted.value = true;
            } else {
                localMessages.value.push(response.data.message);
            }
        })
        .catch(() => {
            messageText.value = payload;
        })
        .finally(() => {
            processing.value = false;
        });
};
</script>

<template>
    <Head :title="conversation.scenario.title" />

    <AuthenticatedLayout>
        <template #title>{{ conversation.scenario.title }}</template>

        <div class="flex h-full flex-col overflow-hidden px-5 pb-5 pt-4">
            <div class="card flex min-h-0 flex-1 flex-col overflow-hidden">
                <!-- Chat header -->
                <header
                    class="flex-shrink-0 border-b px-6 py-4"
                    style="
                        background: var(--bg-surface);
                        border-color: var(--border-strong);
                        backdrop-filter: blur(20px);
                    "
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p
                                class="mb-1 text-[11px] font-bold uppercase tracking-widest"
                                style="color: var(--text-3)"
                            >
                                {{
                                    isCompleted
                                        ? "Session Complete"
                                        : "Active simulation"
                                }}
                            </p>
                            <h2
                                class="text-sm font-bold"
                                style="color: var(--text)"
                            >
                                {{ conversation.scenario.title }}
                            </h2>
                            <p
                                class="mt-1 max-w-lg text-xs leading-relaxed"
                                style="color: var(--text-2)"
                            >
                                {{ conversation.scenario.description }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <!-- End Session button — only show when active -->
                            <button
                                v-if="!isCompleted"
                                @click="showEndConfirm = true"
                                class="rounded-full border px-3 py-1 text-[11px] font-semibold transition-all duration-200 hover:opacity-80"
                                style="
                                    background: var(--red-bg);
                                    color: var(--red);
                                    border-color: rgba(220, 38, 38, 0.2);
                                "
                            >
                                End Session
                            </button>

                            <!-- Session badge -->
                            <span
                                class="rounded-full border px-3 py-1 text-[11px] font-semibold"
                                style="
                                    background: var(--accent-bg);
                                    color: var(--accent);
                                    border-color: rgba(124, 58, 237, 0.2);
                                "
                            >
                                Session #{{ conversation.id }}
                            </span>
                        </div>
                    </div>
                </header>

                <!-- Results panel — shown when completed -->
                <div
                    v-if="isCompleted && scores"
                    class="flex-1 overflow-y-auto px-6 py-6"
                >
                    <!-- Overall score -->
                    <div class="mb-6 text-center">
                        <p
                            class="text-[11px] font-bold uppercase tracking-widest mb-2"
                            style="color: var(--text-3)"
                        >
                            Overall Performance
                        </p>
                        <div
                            class="inline-flex items-center justify-center w-24 h-24 rounded-full border-4 mb-3"
                            :style="{
                                borderColor:
                                    scores.final >= 80
                                        ? 'var(--green)'
                                        : scores.final >= 60
                                          ? 'var(--amber)'
                                          : 'var(--red)',
                                background:
                                    scores.final >= 80
                                        ? 'var(--green-bg)'
                                        : scores.final >= 60
                                          ? 'var(--amber-bg)'
                                          : 'var(--red-bg)',
                            }"
                        >
                            <span
                                class="text-3xl font-extrabold"
                                :style="{
                                    color:
                                        scores.final >= 80
                                            ? 'var(--green)'
                                            : scores.final >= 60
                                              ? 'var(--amber)'
                                              : 'var(--red)',
                                }"
                            >
                                {{ scores.final }}
                            </span>
                        </div>
                        <p class="text-sm font-bold" style="color: var(--text)">
                            {{
                                scores.final >= 80
                                    ? "Excellent"
                                    : scores.final >= 60
                                      ? "Good effort"
                                      : "Keep practising"
                            }}
                        </p>
                    </div>

                    <!-- Breakdown scores -->
                    <div
                        class="card p-5 mb-4"
                        style="background: var(--bg-surface2)"
                    >
                        <h3
                            class="text-[11px] font-bold uppercase tracking-widest mb-4"
                            style="color: var(--text-3)"
                        >
                            Performance Breakdown
                        </h3>
                        <div class="flex flex-col gap-4">
                            <div
                                v-for="(label, key) in {
                                    clarity: 'Clarity',
                                    confidence: 'Confidence',
                                    objective: 'Objective',
                                    adaptability: 'Adaptability',
                                }"
                                :key="key"
                            >
                                <div class="flex justify-between mb-1">
                                    <span
                                        class="text-xs font-semibold"
                                        style="color: var(--text-2)"
                                        >{{ label }}</span
                                    >
                                    <span
                                        class="text-xs font-bold"
                                        style="color: var(--text)"
                                        >{{ scores[key] }}/100</span
                                    >
                                </div>
                                <div
                                    class="w-full rounded-full h-2"
                                    style="background: var(--accent-bg)"
                                >
                                    <div
                                        class="h-2 rounded-full transition-all duration-700"
                                        :style="{
                                            width: scores[key] + '%',
                                            background:
                                                scores[key] >= 80
                                                    ? 'var(--green)'
                                                    : scores[key] >= 60
                                                      ? 'var(--amber)'
                                                      : 'var(--red)',
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Feedback -->
                    <div
                        class="card p-5 mb-6"
                        style="
                            background: var(--accent-bg);
                            border-color: rgba(124, 58, 237, 0.2);
                        "
                    >
                        <h3
                            class="text-[11px] font-bold uppercase tracking-widest mb-2"
                            style="color: var(--accent)"
                        >
                            Coach Feedback
                        </h3>
                        <p
                            class="text-xs leading-relaxed"
                            style="color: var(--text-2)"
                        >
                            {{ scores.feedback }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 justify-center">
                        <Link
                            href="/scenarios"
                            class="rounded-xl border px-5 py-2.5 text-xs font-semibold transition-all hover:opacity-80 no-underline"
                            style="
                                border-color: var(--border);
                                color: var(--text-2);
                            "
                        >
                            ← Back to Scenarios
                        </Link>
                        <button
                            @click="
                                router.post('/conversations', {
                                    scenario_id: conversation.scenario_id,
                                })
                            "
                            class="btn-primary rounded-xl px-5 py-2.5 text-xs"
                        >
                            Try Again →
                        </button>
                    </div>
                </div>

                <!-- Completed but no scores yet -->
                <div
                    v-else-if="isCompleted && !scores"
                    class="flex-1 flex items-center justify-center"
                >
                    <p class="text-sm" style="color: var(--text-2)">
                        Session completed. No evaluation available.
                    </p>
                </div>

                <!-- Active chat messages -->
                <div
                    v-else
                    ref="messagesEl"
                    class="flex flex-1 flex-col gap-3 overflow-y-auto px-6 py-5"
                    style="scroll-behavior: smooth"
                >
                    <!-- Objective banner -->
                    <div
                        class="rounded-xl border p-4 text-xs leading-relaxed"
                        style="
                            background: var(--accent-bg);
                            border-color: rgba(124, 58, 237, 0.2);
                            color: var(--text-2);
                        "
                    >
                        <div
                            class="mb-2 flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest"
                            style="color: var(--accent)"
                        >
                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <circle cx="12" cy="12" r="10" />
                                <circle cx="12" cy="12" r="6" />
                                <circle cx="12" cy="12" r="2" />
                            </svg>
                            Your objective
                        </div>
                        <p class="m-0">
                            Stay in character and guide the conversation toward
                            a successful outcome. Read each reply carefully
                            before you respond.
                        </p>
                    </div>

                    <!-- Message rows -->
                    <div
                        v-for="msg in localMessages"
                        :key="msg.id"
                        class="flex items-end gap-2"
                        :class="
                            isUser(msg.role) ? 'justify-end' : 'justify-start'
                        "
                    >
                        <div
                            v-if="!isUser(msg.role)"
                            class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full border text-[10px] font-bold"
                            style="
                                background: rgba(124, 58, 237, 0.15);
                                color: var(--accent);
                                border-color: rgba(124, 58, 237, 0.2);
                            "
                            aria-hidden="true"
                        >
                            AI
                        </div>
                        <div
                            class="max-w-[68%] rounded-[18px] px-4 py-[11px] text-[13px] leading-relaxed"
                            :class="
                                isUser(msg.role)
                                    ? 'rounded-br-[4px]'
                                    : 'rounded-bl-[4px] border'
                            "
                            :style="
                                isUser(msg.role)
                                    ? 'background: linear-gradient(135deg,#7c3aed,#a855f7); color: white; box-shadow: 0 4px 16px rgba(124,58,237,0.35);'
                                    : 'background: var(--msg-ai-bg, rgba(255,255,255,0.8)); color: var(--msg-ai-text, #3b1d8a); border-color: rgba(167,139,250,0.2); box-shadow: 0 2px 12px rgba(124,58,237,0.08);'
                            "
                        >
                            {{ msg.content }}
                        </div>
                    </div>

                    <!-- Typing indicator -->
                    <div v-if="processing" class="flex items-end gap-2">
                        <div
                            class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full border text-[10px] font-bold"
                            style="
                                background: rgba(124, 58, 237, 0.15);
                                color: var(--accent);
                                border-color: rgba(124, 58, 237, 0.2);
                            "
                            aria-hidden="true"
                        >
                            AI
                        </div>
                        <div
                            class="flex items-center gap-[5px] rounded-[18px] rounded-bl-[4px] border px-[18px] py-[14px]"
                            style="
                                background: var(
                                    --msg-ai-bg,
                                    rgba(255, 255, 255, 0.8)
                                );
                                border-color: rgba(167, 139, 250, 0.2);
                            "
                        >
                            <span
                                class="typing-dot h-[7px] w-[7px] rounded-full"
                                style="background: rgba(124, 58, 237, 0.4)"
                            />
                            <span
                                class="typing-dot h-[7px] w-[7px] rounded-full"
                                style="background: rgba(124, 58, 237, 0.4)"
                            />
                            <span
                                class="typing-dot h-[7px] w-[7px] rounded-full"
                                style="background: rgba(124, 58, 237, 0.4)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Footer — hidden when completed -->
                <footer
                    v-if="!isCompleted"
                    class="flex-shrink-0 border-t px-5 py-3"
                    style="
                        background: var(--bg-surface);
                        border-color: var(--border-strong);
                        backdrop-filter: blur(20px);
                    "
                >
                    <form
                        class="chat-form flex items-center gap-2 rounded-full border px-[18px] py-[6px] pr-[6px] backdrop-blur"
                        style="
                            background: var(--bg-input);
                            border-color: rgba(167, 139, 250, 0.25);
                            box-shadow: var(--shadow-sm);
                        "
                        @submit.prevent="sendMessage"
                    >
                        <input
                            ref="inputRef"
                            v-model="messageText"
                            type="text"
                            class="flex-1 border-0 bg-transparent text-[13px] outline-none focus:outline-none focus:ring-0"
                            style="font-family: inherit; color: var(--text)"
                            :placeholder="
                                isListening
                                    ? 'Listening...'
                                    : `Reply as yourself in ${conversation.scenario.title}…`
                            "
                            :disabled="processing"
                            autocomplete="off"
                        />

                        <!-- Mic button -->
                        <button
                            type="button"
                            @click="toggleVoice"
                            class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full border-0 transition-all duration-200"
                            :style="
                                isListening
                                    ? 'background: linear-gradient(135deg,#dc2626,#ef4444); box-shadow: 0 4px 12px rgba(220,38,38,0.4);'
                                    : 'background: var(--bg-surface2); box-shadow: var(--shadow-sm);'
                            "
                            :disabled="processing"
                            :aria-label="
                                isListening
                                    ? 'Stop recording'
                                    : 'Start voice input'
                            "
                        >
                            <svg
                                v-if="!isListening"
                                width="15"
                                height="15"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                style="color: var(--accent)"
                            >
                                <path
                                    d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"
                                />
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                                <line x1="12" y1="19" x2="12" y2="23" />
                                <line x1="8" y1="23" x2="16" y2="23" />
                            </svg>
                            <span
                                v-else
                                class="live-dot h-3 w-3 rounded-full bg-white"
                            />
                        </button>

                        <!-- Send button -->
                        <button
                            type="submit"
                            class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full border-0 transition-all duration-200"
                            style="
                                background: linear-gradient(
                                    135deg,
                                    #7c3aed,
                                    #a855f7
                                );
                                box-shadow: var(--shadow-btn);
                            "
                            :disabled="processing || !messageText.trim()"
                            :class="
                                processing || !messageText.trim()
                                    ? 'opacity-35 cursor-not-allowed'
                                    : 'hover:opacity-90 hover:scale-105'
                            "
                            aria-label="Send message"
                        >
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="white"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <line x1="22" y1="2" x2="11" y2="13" />
                                <polygon points="22 2 15 22 11 13 2 9 22 2" />
                            </svg>
                        </button>
                    </form>

                    <p
                        class="mt-2 text-center text-[11px]"
                        style="color: var(--text-3)"
                    >
                        <Link
                            href="/scenarios"
                            class="font-semibold transition-opacity duration-200 hover:opacity-75"
                            style="color: var(--accent)"
                        >
                            ← Back to scenarios
                        </Link>
                    </p>
                </footer>
            </div>
        </div>

        <!-- End Session confirmation modal -->
        <div
            v-if="showEndConfirm"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm"
            @click.self="showEndConfirm = false"
        >
            <div
                class="card w-full max-w-sm p-6 flex flex-col gap-4"
                style="background: var(--bg-surface)"
            >
                <h2 class="text-base font-bold" style="color: var(--text)">
                    End this session?
                </h2>
                <p class="text-xs leading-relaxed" style="color: var(--text-2)">
                    Your conversation will be evaluated and you'll receive a
                    detailed performance score. This cannot be undone.
                </p>
                <div class="flex gap-3 justify-end">
                    <button
                        @click="showEndConfirm = false"
                        class="px-4 py-2 text-xs font-medium rounded-xl border transition-all hover:opacity-80"
                        style="
                            border-color: var(--border);
                            color: var(--text-2);
                            background: transparent;
                        "
                    >
                        Cancel
                    </button>
                    <button
                        @click="endSession"
                        class="px-4 py-2 text-xs font-semibold rounded-xl border-0 text-white transition-all hover:opacity-80"
                        style="
                            background: linear-gradient(
                                135deg,
                                #dc2626,
                                #ef4444
                            );
                        "
                    >
                        End & Evaluate
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.chat-form:focus-within {
    border-color: rgba(124, 58, 237, 0.45) !important;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.08) !important;
}
</style>
