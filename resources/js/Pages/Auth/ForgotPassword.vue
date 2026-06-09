<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    status: { type: String },
});

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <Head title="Forgot Password — Rehearse" />

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
                Forgot password?
            </h1>
            <p class="text-sm mb-6" style="color: var(--text-2)">
                No problem — enter your email and we'll send a reset link.
            </p>

            <!-- Success status -->
            <p
                v-if="status"
                class="text-sm text-center mb-4"
                style="color: #16a34a"
            >
                {{ status }}
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label
                        class="block text-sm font-medium mb-1.5"
                        style="color: var(--text-2)"
                        for="email"
                        >Email</label
                    >
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        class="form-input"
                        placeholder="you@example.com"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-1.5 text-xs"
                        style="color: var(--red-text)"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <button
                    type="submit"
                    class="btn-primary w-full mt-2"
                    :disabled="form.processing"
                    :class="{ 'opacity-50': form.processing }"
                >
                    {{ form.processing ? "Sending…" : "Email Reset Link" }}
                </button>
            </form>

            <div
                class="my-6 border-t"
                style="border-color: var(--border)"
            ></div>

            <p class="text-sm text-center" style="color: var(--text-2)">
                Remembered it?
                <Link
                    :href="route('login')"
                    class="font-medium transition-colors"
                    style="color: var(--accent)"
                    >Back to sign in</Link
                >
            </p>
        </div>
    </div>
</template>
