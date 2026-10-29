<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Plus } from '@lucide/vue';
import type { RouteDefinition } from '@/wayfinder';

defineProps<{
    title: string;
    description: string;
    actionLabel?: string;
    actionHref?: RouteDefinition<'get'>;
    backHref?: RouteDefinition<'get'>;
}>();
</script>

<template>
    <div
        class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
    >
        <div class="flex min-w-0 items-start gap-3">
            <Link
                v-if="backHref"
                :href="backHref"
                class="mt-0.5 grid size-10 shrink-0 place-items-center rounded-2xl border border-[#e7dcd7] bg-white text-[#79696e] transition hover:border-[#d5b4bc] hover:text-[#a05d6c]"
                aria-label="Retour"
            >
                <ArrowLeft class="size-4.5" />
            </Link>
            <div class="min-w-0">
                <p
                    class="text-xs font-semibold tracking-[0.18em] text-[#a16d78] uppercase"
                >
                    Administration
                </p>
                <h1
                    class="mt-1 font-serif text-2xl font-semibold break-words text-[#3f3236] sm:text-3xl"
                >
                    {{ title }}
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#88777c]">
                    {{ description }}
                </p>
            </div>
        </div>

        <Link
            v-if="actionHref && actionLabel"
            :href="actionHref"
            class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-[#a96171] px-5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[#955464] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#8f5361] sm:min-h-11 sm:w-auto"
        >
            <Plus class="size-4.5" />
            {{ actionLabel }}
        </Link>
    </div>
</template>
