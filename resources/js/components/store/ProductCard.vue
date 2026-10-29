<script setup lang="ts">
import { Eye, ShoppingBag, Star } from '@lucide/vue';
import type { StoreProduct } from '@/types/store';
import {
    formatMoney,
    productIsAvailable,
    splitBilingualName,
} from '@/types/store';

defineProps<{ product: StoreProduct; busy: boolean }>();
defineEmits<{ add: [product: StoreProduct]; open: [product: StoreProduct] }>();
</script>

<template>
    <article
        class="group flex min-w-0 flex-col border border-[#eadde1] bg-transparent transition duration-300 hover:-translate-y-1"
    >
        <button
            type="button"
            class="relative aspect-square overflow-hidden border-b border-[#eadde1] bg-[#f8f1ef] sm:aspect-[4/5]"
            :aria-label="`عرض ${product.name}`"
            @click="$emit('open', product)"
        >
            <img
                v-if="product.main_image"
                :src="product.main_image"
                :alt="product.name"
                class="transition duration-700 group-hover:scale-[1.025]"
                :class="
                    product.main_image_mode === 'editorial'
                        ? 'h-full w-full object-cover'
                        : 'h-full w-full object-contain p-2 drop-shadow-[0_14px_18px_rgba(57,38,49,0.1)] min-[375px]:p-3 sm:p-7'
                "
                loading="lazy"
            />
            <span
                v-else
                class="store-display grid h-full place-items-center text-6xl text-[#c4a4ae]"
                >N.</span
            >
            <span
                v-if="product.is_featured"
                class="absolute top-2.5 left-2.5 rounded-full bg-[#fffbf8]/95 px-2.5 py-1.5 text-[8px] font-bold tracking-[0.12em] text-[#9a5870] uppercase shadow-sm sm:top-4 sm:left-4 sm:text-[9px]"
                >من مختاراتنا</span
            >
            <span
                v-if="!productIsAvailable(product)"
                class="absolute inset-x-2.5 bottom-2.5 rounded-full bg-[#392631]/90 px-3 py-2 text-center text-[9px] font-semibold tracking-wide text-white uppercase"
                >غير متوفر</span
            >
            <span
                class="absolute bottom-4 left-1/2 hidden -translate-x-1/2 items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-semibold text-[#392631] opacity-0 shadow-lg transition group-hover:opacity-100 lg:flex"
                ><Eye class="size-4" /> نظرة سريعة</span
            >
        </button>
        <div
            class="flex flex-1 flex-col px-2 pt-3 pb-2 sm:px-3 sm:pt-5 sm:pb-3"
        >
            <div
                class="flex min-h-8 items-start justify-between gap-1 sm:gap-2"
            >
                <p class="min-w-0 flex-1 text-center text-[#9e7885]">
                    <span
                        v-if="
                            splitBilingualName(product.category?.name || 'NATÉ')
                                .french
                        "
                        dir="ltr"
                        lang="fr"
                        class="block text-[9px] leading-tight font-semibold tracking-[0.08em] break-words uppercase sm:text-[10px]"
                        >{{
                            splitBilingualName(product.category?.name || 'NATÉ')
                                .french
                        }}</span
                    >
                    <span
                        v-if="
                            splitBilingualName(product.category?.name || 'NATÉ')
                                .arabic
                        "
                        dir="rtl"
                        lang="ar"
                        class="mt-1 block text-[9px] leading-tight font-medium tracking-normal break-words sm:text-[10px]"
                        >{{
                            splitBilingualName(product.category?.name || 'NATÉ')
                                .arabic
                        }}</span
                    >
                </p>
                <span
                    v-if="product.reviews_count"
                    class="hidden shrink-0 items-center gap-1 text-[10px] text-[#735c65] sm:flex"
                    ><Star class="size-3 fill-[#b86f87] text-[#b86f87]" />
                    {{ product.rating_average }} ({{
                        product.reviews_count
                    }})</span
                >
            </div>
            <h3
                class="mt-1 line-clamp-2 min-h-10 text-[12px] leading-[1.15rem] font-semibold text-[#392631] min-[375px]:text-[13px] sm:mt-2 sm:min-h-12 sm:text-[15px] sm:leading-[1.35rem] sm:font-medium"
            >
                {{ product.name }}
            </h3>
            <div
                class="mt-auto flex min-h-12 items-center justify-between gap-1.5 pt-1.5 sm:mt-2 sm:block sm:min-h-6 sm:pt-0"
            >
                <div
                    class="flex min-w-0 flex-col items-start gap-0.5 sm:flex-row sm:flex-wrap sm:items-baseline sm:gap-x-2"
                >
                    <strong
                        class="text-[13px] font-bold text-[#8e5067] min-[375px]:text-sm sm:text-base sm:font-semibold"
                        >{{
                            Number(product.price) > 0
                                ? formatMoney(product.price)
                                : 'السعر قريباً'
                        }}</strong
                    >
                    <del
                        v-if="
                            Number(product.compare_at_price) >
                                Number(product.price) &&
                            Number(product.price) > 0
                        "
                        class="text-[9px] font-medium text-[#745e67] decoration-[#a45f79] sm:text-xs"
                        >{{ formatMoney(product.compare_at_price!) }}</del
                    >
                </div>
                <button
                    type="button"
                    class="grid size-10 shrink-0 place-items-center rounded-md border border-[#cbaab5] bg-transparent text-[#392631] transition duration-200 hover:-translate-y-0.5 hover:border-[#a45f79] hover:bg-[#f2e3e7] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a45f79] active:scale-95 sm:hidden"
                    :aria-label="`عرض تفاصيل ${product.name}`"
                    @click="$emit('open', product)"
                >
                    <Eye class="size-[19px]" />
                </button>
            </div>
            <div class="mt-4 hidden grid-cols-[1fr_44px] gap-2 sm:grid">
                <button
                    type="button"
                    :disabled="!productIsAvailable(product) || busy"
                    class="store-card-action inline-flex items-center justify-center gap-1.5"
                    @click="$emit('add', product)"
                >
                    <span v-if="busy">جارٍ…</span>
                    <template v-else-if="productIsAvailable(product)">
                        <span>أضيفي إلى السلة</span>
                    </template>
                    <span v-else>غير متوفر</span>
                </button>
                <button
                    type="button"
                    class="hidden min-h-11 place-items-center rounded-full border border-[#9c7a87] text-[#392631] transition hover:border-[#a45f79] hover:bg-[#f2e3e7] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a45f79] active:bg-[#e5ced5] sm:grid"
                    :aria-label="`عرض ${product.name}`"
                    @click="$emit('open', product)"
                >
                    <ShoppingBag class="size-4" />
                </button>
            </div>
        </div>
    </article>
</template>
