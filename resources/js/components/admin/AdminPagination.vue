<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

defineProps<{
    links: PaginationLink[];
}>();
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="mt-6 flex max-w-full flex-wrap items-center justify-center gap-1.5 overflow-hidden"
        aria-label="Pagination"
    >
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="grid min-h-11 min-w-11 place-items-center rounded-xl border px-3 text-sm transition"
                :class="
                    link.active
                        ? 'border-[#b66f7f] bg-[#b66f7f] text-white'
                        : 'border-[#e8ddd8] bg-white text-[#75666a] hover:border-[#d4afb8] hover:bg-[#fff7f8]'
                "
                v-html="link.label"
            />
            <span
                v-else
                class="grid min-h-11 min-w-11 place-items-center rounded-xl border border-[#eee5e1] px-3 text-sm text-[#8f8185]"
                v-html="link.label"
            ></span>
        </template>
    </nav>
</template>
