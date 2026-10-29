<script setup lang="ts">
import { AlertTriangle, X } from '@lucide/vue';

defineProps<{
    open: boolean;
    title: string;
    description: string;
    processing?: boolean;
}>();

defineEmits<{
    cancel: [];
    confirm: [];
}>();
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-150"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[80] grid place-items-end bg-[#34292d]/45 p-0 backdrop-blur-[1px] sm:place-items-center sm:p-4"
                role="dialog"
                aria-modal="true"
                :aria-label="title"
                @click.self="$emit('cancel')"
            >
                <div
                    class="w-full max-w-full rounded-t-[2rem] bg-white p-4 shadow-2xl min-[375px]:p-5 sm:max-w-md sm:rounded-[2rem] sm:p-6"
                >
                    <div class="flex items-start justify-between gap-4">
                        <span
                            class="grid size-11 shrink-0 place-items-center rounded-2xl bg-[#fae8e5] text-[#a65353]"
                        >
                            <AlertTriangle class="size-5" />
                        </span>
                        <button
                            type="button"
                            class="grid size-9 place-items-center rounded-xl text-[#8d7c81] hover:bg-[#f8f1ef]"
                            aria-label="Fermer"
                            @click="$emit('cancel')"
                        >
                            <X class="size-4.5" />
                        </button>
                    </div>
                    <h2
                        class="mt-5 font-serif text-2xl font-semibold text-[#403438]"
                    >
                        {{ title }}
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-[#817176]">
                        {{ description }}
                    </p>
                    <div
                        class="mt-6 grid grid-cols-1 gap-2 min-[360px]:grid-cols-2 min-[360px]:gap-3"
                    >
                        <button
                            type="button"
                            class="min-h-11 rounded-2xl border border-[#e8ddd8] text-sm font-semibold text-[#716268] hover:bg-[#faf6f3]"
                            @click="$emit('cancel')"
                        >
                            Annuler
                        </button>
                        <button
                            type="button"
                            :disabled="processing"
                            class="min-h-11 rounded-2xl bg-[#a65353] text-sm font-semibold text-white hover:bg-[#914545] disabled:opacity-50"
                            @click="$emit('confirm')"
                        >
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
