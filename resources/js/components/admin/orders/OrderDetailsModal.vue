<script setup lang="ts">
import {
    Banknote,
    CalendarDays,
    CreditCard,
    Image as ImageIcon,
    Mail,
    MapPin,
    PackageOpen,
    Phone,
    Truck,
    UserRound,
    X,
} from '@lucide/vue';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import type { AdminOrder } from '@/types/orders';
import {
    formatOrderDate,
    formatOrderMoney,
    orderStatusClass,
    orderStatusLabel,
} from '@/types/orders';

const props = withDefaults(
    defineProps<{
        order: AdminOrder | null;
        loading?: boolean;
        error?: string | null;
    }>(),
    { loading: false, error: null },
);
const emit = defineEmits<{ close: [] }>();
const panel = ref<HTMLElement | null>(null);
const previouslyFocused = ref<HTMLElement | null>(null);
const discount = computed(() => {
    if (!props.order) return 0;
    return Math.max(
        0,
        Number(props.order.subtotal) +
            Number(props.order.delivery_fee) -
            Number(props.order.total),
    );
});

const close = (): void => emit('close');
const handleKeydown = (event: KeyboardEvent): void => {
    if (!props.order) return;
    if (event.key === 'Escape') close();
    if (event.key !== 'Tab' || !panel.value) return;

    const focusable = [
        ...panel.value.querySelectorAll<HTMLElement>(
            'button:not([disabled]), a[href], input:not([disabled]), select:not([disabled])',
        ),
    ];
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
};

watch(
    () => props.order,
    async (order) => {
        if (order) {
            previouslyFocused.value = document.activeElement as HTMLElement;
            document.body.style.overflow = 'hidden';
            await nextTick();
            panel.value?.querySelector<HTMLElement>('button')?.focus();
        } else {
            document.body.style.overflow = '';
            previouslyFocused.value?.focus();
        }
    },
);

onMounted(() => document.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition name="order-modal">
            <div
                v-if="order"
                class="fixed inset-0 z-[80] flex items-stretch justify-center sm:items-center sm:p-5"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="`order-title-${order.id}`"
            >
                <button
                    type="button"
                    class="absolute inset-0 bg-[#2a1e24]/65 backdrop-blur-[2px]"
                    aria-label="Fermer les détails"
                    @click="close"
                />
                <section
                    ref="panel"
                    class="relative flex h-dvh w-full max-w-6xl min-w-0 flex-col overflow-hidden bg-[#fffdfb] shadow-[0_30px_90px_rgba(45,28,37,0.3)] sm:h-auto sm:max-h-[92dvh] sm:rounded-[2rem] sm:border sm:border-white/60"
                >
                    <header
                        class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-[#eadfda] bg-[#fffdfb]/95 px-4 py-4 backdrop-blur sm:gap-4 sm:px-7 sm:py-5"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <p
                                    class="text-[10px] font-bold tracking-[0.18em] text-[#9d5c73] uppercase"
                                >
                                    Détails de la commande
                                </p>
                                <span
                                    class="rounded-full border px-2.5 py-1 text-[10px] font-bold"
                                    :class="orderStatusClass(order.status)"
                                    >{{ orderStatusLabel(order.status) }}</span
                                >
                            </div>
                            <h2
                                :id="`order-title-${order.id}`"
                                class="mt-2 max-w-full font-serif text-xl leading-tight font-semibold break-all text-[#392f33] min-[375px]:text-2xl sm:text-3xl"
                                :title="order.order_number"
                            >
                                {{ order.order_number }}
                            </h2>
                            <p
                                class="mt-1 flex items-center gap-1.5 text-xs font-medium text-[#75656b]"
                            >
                                <CalendarDays class="size-3.5" />
                                {{ formatOrderDate(order.created_at) }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="grid size-11 shrink-0 place-items-center rounded-full bg-[#392f33] text-white transition hover:bg-[#755064] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a45f79] active:scale-95"
                            aria-label="Fermer"
                            @click="close"
                        >
                            <X class="size-5" />
                        </button>
                    </header>

                    <div
                        class="min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-contain px-3 py-4 min-[375px]:px-4 sm:px-7 sm:py-6"
                    >
                        <div
                            v-if="loading"
                            class="grid animate-pulse gap-5 lg:grid-cols-[1fr_0.72fr]"
                        >
                            <div class="space-y-4">
                                <div class="h-36 rounded-3xl bg-[#f2e7e4]" />
                                <div class="h-52 rounded-3xl bg-[#f2e7e4]" />
                            </div>
                            <div class="h-80 rounded-3xl bg-[#f2e7e4]" />
                        </div>
                        <div
                            v-else-if="error"
                            class="rounded-3xl border border-rose-200 bg-rose-50 p-8 text-center text-sm font-medium text-rose-800"
                        >
                            {{ error }}
                        </div>
                        <div
                            v-else
                            class="grid min-w-0 gap-5 sm:gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(300px,0.72fr)]"
                        >
                            <div class="min-w-0 space-y-5 sm:space-y-6">
                                <section
                                    class="min-w-0 rounded-2xl border border-[#eadfda] bg-white p-4 sm:rounded-3xl sm:p-5"
                                >
                                    <h3
                                        class="font-serif text-xl font-semibold text-[#43363b]"
                                    >
                                        Informations cliente
                                    </h3>
                                    <div
                                        class="mt-4 grid gap-4 text-sm sm:grid-cols-2"
                                    >
                                        <div class="order-info">
                                            <UserRound
                                                class="order-info-icon"
                                            />
                                            <div>
                                                <span class="order-info-label"
                                                    >Nom complet</span
                                                ><strong>{{
                                                    order.customer_name
                                                }}</strong>
                                            </div>
                                        </div>
                                        <div class="order-info">
                                            <Phone class="order-info-icon" />
                                            <div>
                                                <span class="order-info-label"
                                                    >Téléphone</span
                                                ><a
                                                    :href="`tel:${order.customer_phone}`"
                                                    class="font-semibold text-[#74475a] hover:underline"
                                                    >{{
                                                        order.customer_phone
                                                    }}</a
                                                >
                                            </div>
                                        </div>
                                        <div class="order-info">
                                            <Mail class="order-info-icon" />
                                            <div>
                                                <span class="order-info-label"
                                                    >Email</span
                                                ><span>Non enregistré</span>
                                            </div>
                                        </div>
                                        <div class="order-info">
                                            <MapPin class="order-info-icon" />
                                            <div>
                                                <span class="order-info-label"
                                                    >Ville</span
                                                ><span>{{
                                                    order.customer_city ||
                                                    'Non renseignée'
                                                }}</span>
                                            </div>
                                        </div>
                                        <div class="order-info sm:col-span-2">
                                            <Truck class="order-info-icon" />
                                            <div>
                                                <span class="order-info-label"
                                                    >Adresse de livraison</span
                                                ><span class="leading-6">{{
                                                    order.customer_address ||
                                                    'Non renseignée'
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <h3
                                            class="font-serif text-xl font-semibold text-[#43363b]"
                                        >
                                            Produits commandés
                                        </h3>
                                        <span
                                            class="text-xs font-semibold text-[#75656b]"
                                            >{{ order.items.length }} ligne{{
                                                order.items.length > 1
                                                    ? 's'
                                                    : ''
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        class="mt-3 overflow-hidden rounded-3xl border border-[#eadfda] bg-white"
                                    >
                                        <article
                                            v-for="line in order.items"
                                            :key="line.id"
                                            class="grid min-w-0 grid-cols-[64px_minmax(0,1fr)] gap-3 border-b border-[#f0e7e3] p-3 last:border-b-0 min-[375px]:grid-cols-[72px_minmax(0,1fr)] sm:grid-cols-[84px_minmax(0,1fr)_auto] sm:items-center sm:gap-4 sm:p-4"
                                        >
                                            <div
                                                class="grid aspect-square place-items-center overflow-hidden rounded-2xl bg-[#f7efed] text-[#a56a7d]"
                                            >
                                                <img
                                                    v-if="line.image_url"
                                                    :src="line.image_url"
                                                    :alt="line.product_name"
                                                    class="h-full w-full object-contain p-1.5"
                                                    loading="lazy"
                                                /><ImageIcon
                                                    v-else
                                                    class="size-6"
                                                />
                                            </div>
                                            <div class="min-w-0">
                                                <p
                                                    class="line-clamp-2 text-sm leading-5 font-semibold text-[#43363b]"
                                                    dir="auto"
                                                >
                                                    {{ line.product_name }}
                                                </p>
                                                <p
                                                    class="mt-1 text-xs text-[#75656b]"
                                                >
                                                    {{ line.quantity }} ×
                                                    {{
                                                        formatOrderMoney(
                                                            line.unit_price,
                                                        )
                                                    }}
                                                </p>
                                                <p
                                                    class="mt-1 text-[11px] text-[#8b7a80]"
                                                >
                                                    Variante non enregistrée
                                                </p>
                                            </div>
                                            <strong
                                                class="col-start-2 text-sm text-[#8f5369] sm:col-auto sm:text-right"
                                                >{{
                                                    formatOrderMoney(
                                                        line.line_total,
                                                    )
                                                }}</strong
                                            >
                                        </article>
                                        <div
                                            v-if="!order.items.length"
                                            class="p-8 text-center text-sm text-[#75656b]"
                                        >
                                            <PackageOpen
                                                class="mx-auto mb-2 size-7"
                                            />Aucune ligne disponible.
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <aside class="min-w-0 space-y-5">
                                <section
                                    class="rounded-2xl bg-[#392f33] p-4 text-white shadow-lg shadow-[#392f33]/10 sm:rounded-3xl sm:p-6"
                                >
                                    <p
                                        class="text-[10px] font-bold tracking-[0.18em] text-[#e5cbd3] uppercase"
                                    >
                                        Récapitulatif
                                    </p>
                                    <div
                                        class="mt-5 space-y-3 text-sm text-white/80"
                                    >
                                        <div class="flex justify-between gap-4">
                                            <span>Sous-total produits</span
                                            ><span>{{
                                                formatOrderMoney(order.subtotal)
                                            }}</span>
                                        </div>
                                        <div class="flex justify-between gap-4">
                                            <span>Frais de livraison</span
                                            ><span>{{
                                                formatOrderMoney(
                                                    order.delivery_fee,
                                                )
                                            }}</span>
                                        </div>
                                        <div
                                            v-if="discount > 0"
                                            class="flex justify-between gap-4 text-[#f0cbd6]"
                                        >
                                            <span>Réduction</span
                                            ><span
                                                >−
                                                {{
                                                    formatOrderMoney(discount)
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                    <div
                                        class="mt-5 border-t border-white/20 pt-5"
                                    >
                                        <span class="text-xs text-white/65"
                                            >Montant total à payer</span
                                        ><strong
                                            class="mt-1 block font-serif text-3xl"
                                            >{{
                                                formatOrderMoney(order.total)
                                            }}</strong
                                        >
                                    </div>
                                </section>
                                <section
                                    class="rounded-3xl border border-[#eadfda] bg-[#faf5f2] p-5"
                                >
                                    <h3
                                        class="font-serif text-xl font-semibold text-[#43363b]"
                                    >
                                        Livraison et paiement
                                    </h3>
                                    <div class="mt-4 space-y-4 text-sm">
                                        <div class="order-info">
                                            <Truck class="order-info-icon" />
                                            <div>
                                                <span class="order-info-label"
                                                    >Mode de livraison</span
                                                ><span>Non enregistré</span>
                                            </div>
                                        </div>
                                        <div class="order-info">
                                            <Banknote class="order-info-icon" />
                                            <div>
                                                <span class="order-info-label"
                                                    >Paiement</span
                                                ><strong
                                                    >Paiement à la
                                                    livraison</strong
                                                >
                                            </div>
                                        </div>
                                        <div class="order-info">
                                            <CreditCard
                                                class="order-info-icon"
                                            />
                                            <div>
                                                <span class="order-info-label"
                                                    >Statut du paiement</span
                                                ><span>Non enregistré</span>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <section
                                    v-if="order.customer_notes"
                                    class="rounded-3xl border border-[#eadfda] bg-white p-5"
                                >
                                    <h3
                                        class="text-sm font-semibold text-[#43363b]"
                                    >
                                        Notes de la cliente
                                    </h3>
                                    <p
                                        class="mt-2 text-sm leading-6 whitespace-pre-line text-[#69585f]"
                                    >
                                        {{ order.customer_notes }}
                                    </p>
                                </section>
                            </aside>
                        </div>
                    </div>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.order-info {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    color: #54464b;
}
.order-info-icon {
    margin-top: 0.125rem;
    width: 1rem;
    height: 1rem;
    flex: none;
    color: #9b6074;
}
.order-info-label {
    display: block;
    margin-bottom: 0.2rem;
    color: #7f6d74;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
}
.order-modal-enter-active,
.order-modal-leave-active {
    transition: opacity 220ms ease;
}
.order-modal-enter-active section,
.order-modal-leave-active section {
    transition: transform 280ms cubic-bezier(0.22, 1, 0.36, 1);
}
.order-modal-enter-from,
.order-modal-leave-to {
    opacity: 0;
}
.order-modal-enter-from section,
.order-modal-leave-to section {
    transform: translateY(1.5rem) scale(0.985);
}
@media (prefers-reduced-motion: reduce) {
    .order-modal-enter-active,
    .order-modal-leave-active,
    .order-modal-enter-active section,
    .order-modal-leave-active section {
        transition: none;
    }
}
</style>
