<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Construction } from '@lucide/vue';

defineProps<{
    title: string;
}>();
</script>

<template>
    <Head :title="title" />

    <section
        class="flex min-h-[calc(100vh-8rem)] animate-in items-center justify-center duration-500 fade-in slide-in-from-bottom-2"
    >
        <div
            class="w-full max-w-xl rounded-[2rem] border border-[#eadfda] bg-[#fffdfb] px-6 py-12 text-center shadow-[0_20px_60px_-38px_rgba(64,47,52,0.45)] sm:px-10"
        >
            <span
                class="mx-auto grid size-14 place-items-center rounded-2xl bg-[#f4e3e7] text-[#a15f6e]"
            >
                <Construction class="size-6" />
            </span>
            <p
                class="mt-6 text-xs font-semibold tracking-[0.18em] text-[#a16d78] uppercase"
            >
                NATE Boutique
            </p>
            <h1 class="mt-2 font-serif text-3xl font-semibold text-[#3f3236]">
                {{ title }}
            </h1>
            <p class="mt-3 text-sm leading-6 text-[#88757b]">
                Module en cours de développement
            </p>
        </div>
    </section>
</template>
