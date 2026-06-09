<script setup>
import { useForm } from "@inertiajs/vue3";
import { nextTick, ref } from "vue";

const confirmingDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({ password: "" });

const confirmDeletion = () => {
    confirmingDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route("profile.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <!-- Section header -->
        <div class="mb-5">
            <h2 class="text-sm font-bold" style="color: var(--red)">
                Delete Account
            </h2>
            <p
                class="mt-1 text-xs leading-relaxed"
                style="color: var(--text-2)"
            >
                Once deleted, all your data will be permanently removed and
                cannot be recovered.
            </p>
        </div>

        <button
            @click="confirmDeletion"
            class="inline-flex items-center justify-center gap-2 rounded-full border px-5 py-[10px] text-xs font-semibold transition-all duration-200 hover:opacity-90"
            style="
                background: var(--red-bg);
                color: var(--red);
                border-color: rgba(220, 38, 38, 0.25);
            "
        >
            Delete my account
        </button>

        <!-- Confirmation modal -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="confirmingDeletion"
                class="fixed inset-0 z-50 flex items-center justify-center p-6"
                @click.self="closeModal"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                    @click="closeModal"
                />

                <!-- Modal card -->
                <div class="card relative z-10 w-full max-w-md p-6">
                    <h2 class="text-sm font-bold" style="color: var(--text)">
                        Are you sure?
                    </h2>
                    <p
                        class="mt-2 text-xs leading-relaxed"
                        style="color: var(--text-2)"
                    >
                        This will permanently delete your account and all
                        associated data. Enter your password to confirm.
                    </p>

                    <!-- Password input -->
                    <div class="mt-5">
                        <label
                            for="delete_password"
                            class="mb-1 block text-[11px] font-semibold uppercase tracking-widest"
                            style="color: var(--text-2)"
                        >
                            Your password
                        </label>
                        <input
                            id="delete_password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="form-input"
                            placeholder="Enter your password"
                            @keyup.enter="deleteUser"
                        />
                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-[11px]"
                            style="color: var(--red)"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="mt-5 flex items-center justify-end gap-3">
                        <button
                            @click="closeModal"
                            class="rounded-full border px-5 py-[10px] text-xs font-semibold transition-all duration-200 hover:opacity-80"
                            style="
                                background: var(--bg-surface2);
                                color: var(--text-2);
                                border-color: var(--border);
                            "
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteUser"
                            class="inline-flex items-center justify-center rounded-full px-5 py-[10px] text-xs font-semibold text-white transition-all duration-200 hover:opacity-90"
                            :class="
                                form.processing
                                    ? 'opacity-40 cursor-not-allowed'
                                    : ''
                            "
                            :disabled="form.processing"
                            style="
                                background: var(--red);
                                box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
                            "
                        >
                            {{
                                form.processing
                                    ? "Deleting…"
                                    : "Yes, delete account"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </section>
</template>
