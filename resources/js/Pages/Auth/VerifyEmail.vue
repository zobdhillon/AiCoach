<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    status: { type: String },
});

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent",
);
</script>

<template>
    <Head title="Verify Email — Rehearse" />

    <div
        class="relative min-h-screen flex items-center justify-center overflow-hidden px-4"
        style="background: var(--bg)"
    >
        <div
            class="pointer-events-none absolute rounded-full blur-3xl opacity-40"
            style="
                width: 440px;
                height: 440px;
                top: -120px;
                left: -140px;
                background: var(--gradient-accent);
            "
        ></div>
        <div
            class="pointer-events-none absolute rounded-full blur-3xl opacity-30"
            style="
                width: 320px;
                height: 320px;
                bottom: -80px;
                right: -100px;
                background: var(--gradient-brand);
            "
        ></div>

        <div
            class="relative w-full max-w-sm rounded-2xl p-8 shadow-xl"
            style="
                background: var(--bg-surface);
                border: 1px solid var(--border);
                backdrop-filter: blur(16px);
            "
        >
            <!-- Brand -->
            <div class="flex items-center gap-3 mb-6">
                <div
                    class="flex items-center justify-center w-9 h-9 rounded-xl flex-shrink-0"
                    style="background: var(--gradient-brand)"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                            stroke="white"
                            stroke-width="2.2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>
                <span class="text-lg font-bold" style="color: var(--text)"
                    >Rehearse</span
                >
            </div>

            <h1 class="text-2xl font-bold mb-1" style="color: var(--text)">
                Verify your email
            </h1>
            <p class="text-sm mb-6" style="color: var(--text-2)">
                Thanks for signing up! Check your inbox for a verification link.
                If you didn't receive it, we can send another.
            </p>

            <!-- Success notice -->
            <p
                v-if="verificationLinkSent"
                class="text-sm text-center mb-4"
                style="color: #16a34a"
            >
                A new verification link has been sent to your email address.
            </p>

            <form @submit.prevent="submit">
                <button
                    type="submit"
                    class="btn-primary w-full"
                    :disabled="form.processing"
                    :class="{ 'opacity-50': form.processing }"
                >
                    {{
                        form.processing
                            ? "Sending…"
                            : "Resend Verification Email"
                    }}
                </button>
            </form>

            <div
                class="my-6 border-t"
                style="border-color: var(--border)"
            ></div>

            <p class="text-sm text-center" style="color: var(--text-2)">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="font-medium transition-colors"
                    style="color: var(--accent)"
                >
                    Log out
                </Link>
            </p>
        </div>
    </div>
</template>
