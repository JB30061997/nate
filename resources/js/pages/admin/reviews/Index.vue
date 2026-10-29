<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Check, Clock3, Star, Trash2, X } from '@lucide/vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';

type Review = {
    id: number;
    customer_name: string;
    rating: number;
    comment: string;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
    product: { id: number; name: string };
};
type Pagination = {
    data: Review[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    from: number | null;
    to: number | null;
    total: number;
};
defineProps<{ reviews: Pagination }>();
const update = (review: Review, status: Review['status']) =>
    router.patch(
        `/admin/reviews/${review.id}`,
        { status },
        { preserveScroll: true },
    );
const remove = (review: Review) => {
    if (window.confirm('Supprimer définitivement cet avis ?'))
        router.delete(`/admin/reviews/${review.id}`, { preserveScroll: true });
};
const statusLabel = {
    pending: 'En attente',
    approved: 'Approuvé',
    rejected: 'Refusé',
};
</script>

<template>
    <Head title="Avis clients" />
    <div class="min-w-0 space-y-5 sm:space-y-6">
        <AdminPageHeader
            title="Avis clients"
            description="Modérez les avis avant leur publication dans la boutique."
        />
        <div v-if="reviews.data.length" class="grid gap-4 xl:grid-cols-2">
            <article
                v-for="review in reviews.data"
                :key="review.id"
                class="min-w-0 overflow-hidden rounded-3xl border border-[#eadfe1] bg-white p-4 shadow-sm sm:p-5"
            >
                <div
                    class="flex min-w-0 items-start justify-between gap-3 sm:gap-4"
                >
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-[#493b3f]">
                            {{ review.customer_name }}
                        </p>
                        <p
                            class="mt-1 line-clamp-1 text-xs text-[#958187]"
                            dir="auto"
                        >
                            {{ review.product.name }}
                        </p>
                    </div>
                    <span
                        class="rounded-full px-3 py-1 text-[10px] font-semibold"
                        :class="
                            review.status === 'approved'
                                ? 'bg-emerald-50 text-emerald-700'
                                : review.status === 'rejected'
                                  ? 'bg-red-50 text-red-700'
                                  : 'bg-amber-50 text-amber-700'
                        "
                        >{{ statusLabel[review.status] }}</span
                    >
                </div>
                <div class="mt-3 flex">
                    <Star
                        v-for="star in 5"
                        :key="star"
                        class="size-4"
                        :class="
                            star <= review.rating
                                ? 'fill-[#b76e7e] text-[#b76e7e]'
                                : 'text-[#ddd0d4]'
                        "
                    />
                </div>
                <p class="mt-3 text-sm leading-6 text-[#6d6064]">
                    {{ review.comment }}
                </p>
                <div
                    class="mt-5 grid grid-cols-2 gap-2 border-t border-[#f0e6e8] pt-4 sm:flex sm:flex-wrap"
                >
                    <button
                        class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl bg-emerald-50 px-2 text-xs font-semibold text-emerald-700 sm:px-3"
                        @click="update(review, 'approved')"
                    >
                        <Check class="size-4" /> Approuver</button
                    ><button
                        class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl bg-amber-50 px-2 text-xs font-semibold text-amber-700 sm:px-3"
                        @click="update(review, 'pending')"
                    >
                        <Clock3 class="size-4" /> Attendre</button
                    ><button
                        class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl bg-red-50 px-2 text-xs font-semibold text-red-700 sm:px-3"
                        @click="update(review, 'rejected')"
                    >
                        <X class="size-4" /> Refuser</button
                    ><button
                        class="grid min-h-11 place-items-center rounded-xl border border-[#eddee2] text-[#8f5365] hover:bg-[#faecef] sm:ml-auto sm:size-11"
                        aria-label="Supprimer"
                        @click="remove(review)"
                    >
                        <Trash2 class="size-4" />
                    </button>
                </div>
            </article>
        </div>
        <div
            v-else
            class="rounded-3xl border border-dashed border-[#ded0d4] bg-white p-12 text-center"
        >
            <p class="text-lg font-semibold text-[#493b3f]">
                Aucun avis à modérer
            </p>
            <p class="mt-2 text-sm text-[#958187]">
                Les nouveaux avis clients apparaîtront ici.
            </p>
        </div>
        <AdminPagination
            v-if="reviews.links.length > 3"
            :links="reviews.links"
        />
    </div>
</template>
