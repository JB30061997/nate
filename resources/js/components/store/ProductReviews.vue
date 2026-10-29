<script setup lang="ts">
import { Star } from '@lucide/vue';
import ReviewForm from './ReviewForm.vue';
import type { StoreProduct } from '@/types/store';

defineProps<{ product: StoreProduct }>();
</script>

<template>
    <section aria-labelledby="reviews-title">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h3 id="reviews-title" class="store-display text-2xl">
                    آراء الزبونات
                </h3>
                <p
                    v-if="product.reviews_count"
                    class="mt-1 flex items-center gap-1 text-xs text-[#715e66]"
                >
                    <Star class="size-3 fill-[#b86f87] text-[#b86f87]" />
                    {{ product.rating_average }} من 5 ·
                    {{ product.reviews_count }} رأي
                </p>
            </div>
        </div>
        <div v-if="product.reviews.length" class="mt-4 space-y-3">
            <article
                v-for="review in product.reviews"
                :key="review.id"
                class="border-t border-[#eadfe1] pt-3"
            >
                <div class="flex items-center justify-between gap-3">
                    <strong class="text-sm">{{ review.customer_name }}</strong
                    ><span class="flex"
                        ><Star
                            v-for="star in 5"
                            :key="star"
                            class="size-3"
                            :class="
                                star <= review.rating
                                    ? 'fill-[#b86f87] text-[#b86f87]'
                                    : 'text-[#dacdd1]'
                            "
                    /></span>
                </div>
                <p class="mt-2 text-sm leading-6 text-[#715e66]">
                    {{ review.comment }}
                </p>
            </article>
        </div>
        <p
            v-else
            class="mt-4 rounded-xl bg-[#f7efed] p-4 text-sm text-[#715e66]"
        >
            كوني أول من يشارك رأيه حول هذا المنتج.
        </p>
        <div v-if="product.reviews_enabled" class="mt-5">
            <ReviewForm :product-id="product.id" />
        </div>
    </section>
</template>
