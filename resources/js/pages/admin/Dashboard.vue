<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Clock3,
    FolderTree,
    LayoutDashboard,
    Package,
    ShoppingBag,
} from '@lucide/vue';
import { computed } from 'vue';

type Statistics = {
    products: number;
    categories: number;
    orders: number;
    pendingOrders: number;
};

const props = defineProps<{
    statistics: Statistics;
}>();

const statisticCards = computed(() => [
    {
        label: 'Total produits',
        value: props.statistics.products,
        icon: Package,
        iconClass: 'bg-[#f7e3e7] text-[#9f5967]',
    },
    {
        label: 'Total catégories',
        value: props.statistics.categories,
        icon: FolderTree,
        iconClass: 'bg-[#f2eadf] text-[#8b6a45]',
    },
    {
        label: 'Total commandes',
        value: props.statistics.orders,
        icon: ShoppingBag,
        iconClass: 'bg-[#ece6ef] text-[#765a80]',
    },
    {
        label: 'Commandes en attente',
        value: props.statistics.pendingOrders,
        icon: Clock3,
        iconClass: 'bg-[#fae8df] text-[#a15f45]',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <section class="animate-in duration-500 fade-in slide-in-from-bottom-2">
        <div
            class="relative overflow-hidden rounded-3xl bg-[#402f34] px-5 py-7 text-white shadow-[0_20px_60px_-30px_rgba(64,47,52,0.65)] sm:rounded-[2rem] sm:px-8 sm:py-9"
        >
            <div
                class="absolute -top-16 -right-12 size-52 rounded-full bg-[#dca8b4]/20 blur-2xl"
            ></div>
            <div
                class="absolute -bottom-20 left-1/3 size-48 rounded-full bg-[#d9c2a5]/15 blur-3xl"
            ></div>
            <div class="relative max-w-2xl">
                <div
                    class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-medium text-[#f4dce1] ring-1 ring-white/10"
                >
                    <LayoutDashboard class="size-3.5" />
                    Vue d’ensemble
                </div>
                <h1
                    class="font-serif text-2xl leading-tight font-semibold min-[375px]:text-3xl sm:text-4xl"
                >
                    Bonjour, Administratrice
                </h1>
                <p
                    class="mt-3 max-w-xl text-sm leading-6 text-[#eadde0] sm:text-base"
                >
                    Retrouvez ici les chiffres essentiels de votre boutique et
                    gardez un œil sur les commandes à traiter.
                </p>
            </div>
        </div>
    </section>

    <section class="mt-7" aria-labelledby="statistics-title">
        <div class="mb-4">
            <p
                class="text-xs font-semibold tracking-[0.18em] text-[#a16d78] uppercase"
            >
                Aujourd’hui
            </p>
            <h2
                id="statistics-title"
                class="mt-1 font-serif text-2xl font-semibold"
            >
                Aperçu de la boutique
            </h2>
        </div>

        <div
            class="grid grid-cols-1 gap-3 min-[360px]:grid-cols-2 sm:gap-4 xl:grid-cols-4"
        >
            <article
                v-for="(card, index) in statisticCards"
                :key="card.label"
                class="min-w-0 animate-in rounded-3xl border border-[#ede3de] bg-[#fffdfb] p-4 shadow-[0_12px_40px_-28px_rgba(70,46,52,0.45)] transition duration-300 fade-in slide-in-from-bottom-3 hover:-translate-y-1 hover:shadow-[0_20px_45px_-28px_rgba(70,46,52,0.5)] sm:p-5"
                :style="{ animationDelay: `${index * 70}ms` }"
            >
                <div class="flex items-start justify-between gap-2 sm:gap-4">
                    <div class="min-w-0">
                        <p class="text-sm leading-5 text-[#85757a]">
                            {{ card.label }}
                        </p>
                        <p
                            class="mt-3 truncate text-2xl font-semibold tracking-tight sm:text-3xl"
                        >
                            {{ card.value.toLocaleString('fr-FR') }}
                        </p>
                    </div>
                    <span
                        class="grid size-9 shrink-0 place-items-center rounded-xl sm:size-11 sm:rounded-2xl"
                        :class="card.iconClass"
                    >
                        <component :is="card.icon" class="size-5" />
                    </span>
                </div>
            </article>
        </div>
    </section>
</template>
