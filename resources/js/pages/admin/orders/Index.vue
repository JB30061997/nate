<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    ClipboardList,
    Clock3,
    Eye,
    PackageSearch,
    RotateCcw,
    Search,
    Wallet,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import OrderDetailsModal from '@/components/admin/orders/OrderDetailsModal.vue';
import type { AdminOrder } from '@/types/orders';
import {
    formatOrderDate,
    formatOrderMoney,
    orderStatusClass,
    orderStatusLabel,
} from '@/types/orders';

type PaginationLink = { url: string | null; label: string; active: boolean };
const props = defineProps<{
    orders: {
        data: AdminOrder[];
        links: PaginationLink[];
        total: number;
        from: number | null;
        to: number | null;
    };
    filters: { search: string; status: string };
    statuses: string[];
    stats: {
        total: number;
        pending: number;
        delivered: number;
        revenue: string;
    };
}>();

const search = ref(props.filters.search);
const status = ref(props.filters.status);
const selectedOrder = ref<AdminOrder | null>(null);
const filtering = ref(false);
const hasOrders = computed(() => props.orders.data.length > 0);
const hasFilters = computed(() => search.value !== '' || status.value !== '');

const visitOrders = (): void => {
    router.get(
        '/admin/orders',
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            onStart: () => (filtering.value = true),
            onFinish: () => (filtering.value = false),
        },
    );
};
const resetFilters = (): void => {
    search.value = '';
    status.value = '';
    visitOrders();
};

const statCards = computed(() => [
    {
        label: 'Total commandes',
        value: String(props.stats.total),
        icon: ClipboardList,
        tone: 'bg-[#f7e9ed] text-[#96576d]',
    },
    {
        label: 'En attente',
        value: String(props.stats.pending),
        icon: Clock3,
        tone: 'bg-amber-50 text-amber-700',
    },
    {
        label: 'Livrées',
        value: String(props.stats.delivered),
        icon: CheckCircle2,
        tone: 'bg-emerald-50 text-emerald-700',
    },
    {
        label: 'CA livré',
        value: formatOrderMoney(props.stats.revenue),
        icon: Wallet,
        tone: 'bg-[#eee8df] text-[#775c3f]',
    },
]);
</script>

<template>
    <Head title="Commandes" />

    <AdminPageHeader
        title="Commandes"
        description="Suivez les commandes, les clientes et les informations de livraison depuis un espace centralisé."
    />

    <section
        class="grid grid-cols-1 gap-3 min-[360px]:grid-cols-2 xl:grid-cols-4"
    >
        <article
            v-for="card in statCards"
            :key="card.label"
            class="group min-w-0 rounded-3xl border border-[#eadfda] bg-white p-4 shadow-[0_8px_30px_rgba(70,49,57,0.035)] sm:p-5"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p
                        class="text-[11px] font-semibold tracking-wide text-[#796970] uppercase"
                    >
                        {{ card.label }}
                    </p>
                    <p
                        class="mt-2 font-serif text-xl leading-tight font-semibold break-words text-[#3f3337] min-[390px]:text-2xl sm:text-3xl"
                        :title="card.value"
                    >
                        {{ card.value }}
                    </p>
                </div>
                <span
                    class="grid size-9 shrink-0 place-items-center rounded-xl transition group-hover:scale-105 sm:size-10 sm:rounded-2xl"
                    :class="card.tone"
                    ><component :is="card.icon" class="size-[18px]"
                /></span>
            </div>
        </article>
    </section>

    <section
        class="mt-5 rounded-3xl border border-[#eadfda] bg-white p-3 shadow-[0_10px_35px_rgba(70,49,57,0.025)] min-[375px]:p-4 sm:rounded-[2rem] sm:p-5"
    >
        <form
            class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]"
            @submit.prevent="visitOrders"
        >
            <label class="relative min-w-0"
                ><Search
                    class="pointer-events-none absolute top-1/2 left-4 size-[18px] -translate-y-1/2 text-[#806d74]" /><input
                    v-model="search"
                    type="search"
                    placeholder="Commande, cliente, téléphone ou ville…"
                    class="min-h-12 w-full rounded-2xl border border-[#d9cbc7] bg-[#fdfaf8] pr-4 pl-11 text-sm text-[#3f3337] outline-none placeholder:text-[#8e7d83] focus:border-[#ac6a7b] focus:ring-4 focus:ring-[#f0dfe3]"
            /></label>
            <select
                v-model="status"
                class="min-h-12 w-full rounded-2xl border border-[#d9cbc7] bg-[#fdfaf8] px-4 text-sm font-medium text-[#493b40] outline-none focus:border-[#ac6a7b] focus:ring-4 focus:ring-[#f0dfe3]"
            >
                <option value="">Tous les statuts</option>
                <option v-for="item in statuses" :key="item" :value="item">
                    {{ orderStatusLabel(item) }}
                </option>
            </select>
            <div class="flex min-w-0 gap-2">
                <button
                    v-if="hasFilters"
                    type="button"
                    class="grid size-12 shrink-0 place-items-center rounded-2xl border border-[#dbcdd0] text-[#66545b] transition hover:bg-[#f7ecef] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a45f79]"
                    aria-label="Réinitialiser les filtres"
                    @click="resetFilters"
                >
                    <RotateCcw class="size-4" /></button
                ><button
                    type="submit"
                    :disabled="filtering"
                    class="inline-flex min-h-12 flex-1 items-center justify-center gap-2 rounded-2xl bg-[#49343e] px-5 text-sm font-semibold text-white transition hover:bg-[#6c4858] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a45f79] disabled:cursor-wait disabled:opacity-60 md:flex-none"
                >
                    <span
                        v-if="filtering"
                        class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                    />{{ filtering ? 'Chargement…' : 'Rechercher' }}
                </button>
            </div>
        </form>
    </section>

    <div
        v-if="filtering && hasOrders"
        class="mt-5 grid gap-3 md:hidden"
        aria-hidden="true"
    >
        <div
            v-for="index in 3"
            :key="index"
            class="h-44 animate-pulse rounded-3xl bg-[#eee5e1]"
        />
    </div>

    <section
        v-else-if="!hasOrders"
        class="mt-5 rounded-3xl border border-dashed border-[#d8c6cb] bg-white px-4 py-12 text-center sm:rounded-[2rem] sm:px-6 sm:py-16"
    >
        <span
            class="mx-auto grid size-16 place-items-center rounded-full bg-[#f7e9ed]"
            ><PackageSearch class="size-7 text-[#a46176]"
        /></span>
        <h2 class="mt-5 font-serif text-2xl font-semibold text-[#493b40]">
            {{
                hasFilters
                    ? 'Aucune commande trouvée'
                    : 'Aucune commande pour le moment'
            }}
        </h2>
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#75656b]">
            {{
                hasFilters
                    ? 'Essayez un autre numéro, nom, téléphone, ville ou statut.'
                    : 'Les prochaines commandes apparaîtront ici automatiquement.'
            }}
        </p>
        <button
            v-if="hasFilters"
            type="button"
            class="mt-5 rounded-xl bg-[#49343e] px-5 py-3 text-sm font-semibold text-white"
            @click="resetFilters"
        >
            Afficher toutes les commandes
        </button>
    </section>

    <template v-else>
        <div
            class="mt-5 grid gap-3 lg:hidden"
            :class="filtering && 'pointer-events-none opacity-55'"
        >
            <article
                v-for="order in orders.data"
                :key="order.id"
                class="min-w-0 overflow-hidden rounded-3xl border border-[#eadfda] bg-white p-4 shadow-[0_8px_28px_rgba(70,49,57,0.035)] sm:p-5"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="text-sm leading-5 font-bold break-all text-[#43363b]"
                            :title="order.order_number"
                        >
                            {{ order.order_number }}
                        </p>
                        <p class="mt-1 text-xs text-[#75656b]">
                            {{ formatOrderDate(order.created_at) }}
                        </p>
                    </div>
                    <span
                        class="shrink-0 rounded-full border px-2.5 py-1 text-[10px] font-bold"
                        :class="orderStatusClass(order.status)"
                        >{{ orderStatusLabel(order.status) }}</span
                    >
                </div>
                <div
                    class="mt-4 grid grid-cols-1 gap-3 rounded-2xl bg-[#faf6f3] p-3 min-[350px]:grid-cols-2"
                >
                    <div class="min-w-0">
                        <span
                            class="text-[10px] font-bold tracking-wide text-[#86737a] uppercase"
                            >Cliente</span
                        >
                        <p
                            class="mt-1 truncate text-sm font-semibold text-[#493b40]"
                        >
                            {{ order.customer_name }}
                        </p>
                        <p class="truncate text-xs text-[#75656b]">
                            {{ order.customer_phone }}
                        </p>
                    </div>
                    <div>
                        <span
                            class="text-[10px] font-bold tracking-wide text-[#86737a] uppercase"
                            >Total</span
                        >
                        <p class="mt-1 text-sm font-bold text-[#97576e]">
                            {{ formatOrderMoney(order.total) }}
                        </p>
                        <p class="text-xs text-[#75656b]">
                            {{ order.items.length }} produit(s)
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl bg-[#f7e9ed] text-sm font-semibold text-[#7e485c] transition hover:bg-[#eed7de] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a45f79]"
                    @click="selectedOrder = order"
                >
                    <Eye class="size-4" /> Voir les détails
                    <ArrowRight class="size-4" />
                </button>
            </article>
        </div>

        <section
            class="mt-5 hidden overflow-hidden rounded-[2rem] border border-[#eadfda] bg-white shadow-[0_10px_35px_rgba(70,49,57,0.025)] lg:block"
            :class="filtering && 'pointer-events-none opacity-55'"
        >
            <div
                class="flex items-center justify-between border-b border-[#eee4df] px-5 py-4"
            >
                <div>
                    <h2 class="font-serif text-xl font-semibold text-[#43363b]">
                        Commandes récentes
                    </h2>
                    <p class="mt-0.5 text-xs text-[#7d6c73]">
                        {{ orders.total }} commande{{
                            orders.total > 1 ? 's' : ''
                        }}
                        au total
                    </p>
                </div>
                <span
                    v-if="orders.from && orders.to"
                    class="text-xs font-medium text-[#7d6c73]"
                    >{{ orders.from }}–{{ orders.to }} affichées</span
                >
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[920px] table-fixed text-left">
                    <thead
                        class="bg-[#faf6f3] text-[10px] font-bold tracking-[0.1em] text-[#74636a] uppercase"
                    >
                        <tr>
                            <th class="w-[22%] px-5 py-4">Commande</th>
                            <th class="w-[24%] px-5 py-4">Cliente</th>
                            <th class="w-[14%] px-5 py-4">Ville</th>
                            <th class="w-[15%] px-5 py-4">Total</th>
                            <th class="w-[15%] px-5 py-4">Statut</th>
                            <th class="w-[10%] px-5 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e7e3]">
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="transition hover:bg-[#fffaf9]"
                        >
                            <td class="px-5 py-4">
                                <p
                                    class="truncate text-sm font-bold text-[#493b40]"
                                    :title="order.order_number"
                                >
                                    {{ order.order_number }}
                                </p>
                                <p class="mt-1 truncate text-xs text-[#7d6c73]">
                                    {{ formatOrderDate(order.created_at) }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <p
                                    class="truncate text-sm font-semibold text-[#493b40]"
                                >
                                    {{ order.customer_name }}
                                </p>
                                <p class="mt-1 truncate text-xs text-[#7d6c73]">
                                    {{ order.customer_phone }}
                                </p>
                            </td>
                            <td
                                class="truncate px-5 py-4 text-sm text-[#62535a]"
                                :title="order.customer_city || undefined"
                            >
                                {{ order.customer_city || '—' }}
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-bold text-[#925268]">
                                    {{ formatOrderMoney(order.total) }}
                                </p>
                                <p class="mt-1 text-xs text-[#7d6c73]">
                                    {{ order.items.length }} ligne(s)
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex rounded-full border px-3 py-1.5 text-[10px] font-bold"
                                    :class="orderStatusClass(order.status)"
                                    >{{ orderStatusLabel(order.status) }}</span
                                >
                            </td>
                            <td class="px-5 py-4 text-right">
                                <button
                                    type="button"
                                    class="inline-grid size-10 place-items-center rounded-xl border border-[#e4d4d8] bg-[#f9edef] text-[#824b60] transition hover:border-[#c794a2] hover:bg-[#efdce2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a45f79] active:scale-95"
                                    :aria-label="`Voir la commande ${order.order_number}`"
                                    @click="selectedOrder = order"
                                >
                                    <Eye class="size-[18px]" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <AdminPagination :links="orders.links" />
    </template>

    <OrderDetailsModal :order="selectedOrder" @close="selectedOrder = null" />
</template>
