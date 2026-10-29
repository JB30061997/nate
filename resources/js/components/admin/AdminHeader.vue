<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Menu } from '@lucide/vue';
import { computed } from 'vue';

defineEmits<{
    openMenu: [];
}>();

defineProps<{
    isMenuOpen: boolean;
}>();

const page = usePage();

const pageTitle = computed(() => {
    const titles: Record<string, string> = {
        '/admin/dashboard': 'Dashboard',
        '/admin/orders': 'Commandes',
        '/admin/products': 'Produits',
        '/admin/categories': 'Catégories',
        '/admin/product-types': 'Types de produits',
        '/admin/reviews': 'Avis clients',
        '/admin/content': 'Contenu boutique',
    };

    const matchingPath = Object.keys(titles).find((path) =>
        page.url.startsWith(path),
    );

    return matchingPath ? titles[matchingPath] : 'Administration';
});
</script>

<template>
    <header
        class="sticky top-0 z-20 border-b border-[#eadfda]/80 bg-[#fbf8f4]/95 backdrop-blur"
    >
        <div
            class="flex min-h-16 items-center gap-3 px-3 min-[375px]:px-4 sm:px-6 lg:px-8"
        >
            <button
                type="button"
                class="grid size-11 shrink-0 place-items-center rounded-2xl border border-[#e7dcd7] bg-white text-[#6d5a60] shadow-sm transition hover:border-[#d7b7bf] hover:bg-[#fff7f8] lg:hidden"
                aria-label="Ouvrir le menu de navigation"
                :aria-expanded="isMenuOpen"
                @click="$emit('openMenu')"
            >
                <Menu class="size-5" />
            </button>

            <div class="min-w-0">
                <p class="truncate text-lg font-semibold text-[#3d3235]">
                    {{ pageTitle }}
                </p>
                <p class="hidden text-xs text-[#938087] sm:block">
                    Gérez votre boutique avec simplicité
                </p>
            </div>
        </div>
    </header>
</template>
