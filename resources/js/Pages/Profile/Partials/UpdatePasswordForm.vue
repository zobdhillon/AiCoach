<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    form.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset("password", "password_confirmation");
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset("current_password");
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <!-- Section header -->
        <div class="mb-5">
            <h2 class="text-sm font-bold" style="color: var(--text)">
                Update Password
            </h2>
            <p
                class="mt-1 text-xs leading-relaxed"
                style="color: var(--text-2)"
            >
                Use a long, random password to keep your account secure.
            </p>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-4">
            <!-- Current password -->
            <div>
                <label
                    for="current_password"
                    class="mb-1 block text-[11px] font-semibold uppercase tracking-widest"
                    style="color: var(--text-2)"
                >
                    Current Password
                </label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="form-input"
                    autocomplete="current-password"
                />
                <p
                    v-if="form.errors.current_password"
                    class="mt-1 text-[11px]"
                    style="color: var(--red)"
                >
                    {{ form.errors.current_password }}
                </p>
            </div>

            <!-- New password -->
            <div>
                <label
                    for="password"
                    class="mb-1 block text-[11px] font-semibold uppercase tracking-widest"
                    style="color: var(--text-2)"
                >
                    New Password
                </label>
                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="form-input"
                    autocomplete="new-password"
                />
                <p
                    v-if="form.errors.password"
                    class="mt-1 text-[11px]"
                    style="color: var(--red)"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Confirm password -->
            <div>
                <label
                    for="password_confirmation"
                    class="mb-1 block text-[11px] font-semibold uppercase tracking-widest"
                    style="color: var(--text-2)"
                >
                    Confirm Password
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="form-input"
                    autocomplete="new-password"
                />
                <p
                    v-if="form.errors.password_confirmation"
                    class="mt-1 text-[11px]"
                    style="color: var(--red)"
                >
                    {{ form.errors.password_confirmation }}
                </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-4 pt-1">
                <button
                    type="submit"
                    class="btn-primary text-sm"
                    :disabled="form.processing"
                >
                    Update password
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
