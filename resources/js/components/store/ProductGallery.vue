<script setup lang="ts">
import { computed, ref, watch } from 'vue';

const props = defineProps<{ images: string[]; name: string }>();
const active = ref(0);
const zoomed = ref(false);
const current = computed(() => props.images[active.value]);
const isEditorial = computed(() =>
    /\.jpe?g(?:$|\?)/i.test(current.value || ''),
);
watch(
    () => props.images,
    () => {
        active.value = 0;
        zoomed.value = false;
    },
);
</script>

<template>
    <div class="min-w-0 bg-white">
        <button
            type="button"
            class="relative flex aspect-[5/4] w-full items-center justify-center overflow-hidden border-b border-[#eadfe1] bg-white sm:aspect-square"
            :aria-label="zoomed ? 'تصغير الصورة' : 'تكبير الصورة'"
            @click="zoomed = !zoomed"
        >
            <img
                v-if="current"
                :src="current"
                :alt="name"
                class="transition duration-500"
                :class="[
                    isEditorial
                        ? 'h-full w-full object-contain p-4 sm:p-7'
                        : 'h-full w-full object-contain p-5 drop-shadow-[0_16px_22px_rgba(57,38,49,0.1)] sm:p-9',
                    zoomed ? 'scale-125 cursor-zoom-out' : 'cursor-zoom-in',
                ]"
            />
            <span v-else class="store-display text-7xl text-[#c5a7b0]">N.</span>
        </button>
        <div
            v-if="images.length > 1"
            class="flex gap-2 overflow-x-auto border-b border-[#eadfe1] bg-white px-4 py-3 sm:px-6 lg:border-b-0 lg:px-0"
        >
            <button
                v-for="(src, index) in images"
                :key="src"
                type="button"
                class="size-16 shrink-0 overflow-hidden border bg-white transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#8f4c65]"
                :class="
                    active === index ? 'border-[#b86f87]' : 'border-[#e8dcde]'
                "
                @click="active = index"
            >
                <img
                    :src="src"
                    :alt="`${name}، صورة ${index + 1}`"
                    class="h-full w-full object-contain p-1.5"
                />
            </button>
        </div>
    </div>
</template>
