<script setup>
import { Head, useForm } from "@inertiajs/vue3";

const form = useForm({
    password: "",
});

const submit = () => {
    form.post(route("password.confirm"), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Confirm Password — Rehearse" />

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
                Confirm your password
            </h1>
            <p class="text-sm mb-6" style="color: var(--text-2)">
                This is a secure area. Please re-enter your password to
                continue.
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label
                        class="block text-sm font-medium mb-1.5"
                        style="color: var(--text-2)"
                        for="password"
                        >Password</label
                    >
                    <input
                        id="password"
                        type="password"
                        v-model="form.password"
                        class="form-input"
                        placeholder="••••••••"
                        required
                        autofocus
                        autocomplete="current-password"
                    />
                    <p
                        v-if="form.errors.password"
                        class="mt-1.5 text-xs"
                        style="color: var(--red-text)"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <button
                    type="submit"
                    class="btn-primary w-full mt-2"
                    :disabled="form.processing"
                    :class="{ 'opacity-50': form.processing }"
                >
                    {{ form.processing ? "Confirming…" : "Confirm" }}
                </button>
            </form>
        </div>
    </div>
</template>
