<script setup>
import { Link, useForm, usePage } from "@inertiajs/vue3";

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <!-- Section header -->
        <div class="mb-5">
            <h2 class="text-sm font-bold" style="color: var(--text)">
                Profile Information
            </h2>
            <p
                class="mt-1 text-xs leading-relaxed"
                style="color: var(--text-2)"
            >
                Update your account name and email address.
            </p>
        </div>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-4"
        >
            <!-- Name -->
            <div>
                <label
                    for="name"
                    class="mb-1 block text-[11px] font-semibold uppercase tracking-widest"
                    style="color: var(--text-2)"
                >
                    Name
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="form-input"
                    required
                    autofocus
                    autocomplete="name"
                />
                <p
                    v-if="form.errors.name"
                    class="mt-1 text-[11px]"
                    style="color: var(--red)"
                >
                    {{ form.errors.name }}
                </p>
            </div>

            <!-- Email -->
            <div>
                <label
                    for="email"
                    class="mb-1 block text-[11px] font-semibold uppercase tracking-widest"
                    style="color: var(--text-2)"
                >
                    Email
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-input"
                    required
                    autocomplete="username"
                />
                <p
                    v-if="form.errors.email"
                    class="mt-1 text-[11px]"
                    style="color: var(--red)"
                >
                    {{ form.errors.email }}
                </p>
            </div>

            <!-- Email verification notice -->
            <div
                v-if="mustVerifyEmail && user.email_verified_at === null"
                class="rounded-xl border p-3 text-xs"
                style="
                    background: var(--amber-bg);
                    border-color: rgba(217, 119, 6, 0.2);
                    color: var(--amber);
                "
            >
                Your email address is unverified.
                <Link
                    :href="route('verification.send')"
                    method="post"
                    as="button"
                    class="ml-1 font-semibold underline"
                >
                    Resend verification email.
                </Link>
                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-semibold"
                    style="color: var(--green)"
                >
                    Verification link sent.
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-4 pt-1">
                <button
                    type="submit"
                    class="btn-primary text-sm"
                    :disabled="form.processing"
                >
                    Save changes
                </button>

                <Transition
                    enter-active-class="transition-opacity duration-200"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <span
                        v-if="form.recentlySuccessful"
                        class="text-xs font-semibold"
                        style="color: var(--green)"
                    >
                        ✓ Saved
                    </span>
                </Transition>
            </div>
        </form>
    </section>
</template>
