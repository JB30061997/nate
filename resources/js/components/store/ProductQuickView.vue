<script setup lang="ts">
import { Minus, Plus, ShoppingBag, X } from '@lucide/vue';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import ProductGallery from './ProductGallery.vue';
import ProductReviews from './ProductReviews.vue';
import type { StoreProduct } from '@/types/store';
import {
    formatMoney,
    productIsAvailable,
    splitBilingualName,
} from '@/types/store';

const props = defineProps<{ product: StoreProduct | null; busy: boolean }>();
const emit = defineEmits<{
    close: [];
    add: [product: StoreProduct, quantity: number];
}>();
const panel = ref<HTMLElement | null>(null);
const quantity = ref(1);
const available = computed(() =>
    props.product ? productIsAvailable(props.product) : false,
);
const productName = computed(() =>
    splitBilingualName(props.product?.name ?? ''),
);
const totalPrice = computed(() =>
    props.product ? Number(props.product.price) * quantity.value : 0,
);

const close = () => emit('close');
const onKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') close();
    if (event.key !== 'Tab' || !panel.value) return;
    const items = [
        ...panel.value.querySelectorAll<HTMLElement>(
            'button:not([disabled]), input:not([disabled]), textarea:not([disabled]), a[href]',
        ),
    ];
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    }
    if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
};
watch(
    () => props.product,
    async (product) => {
        quantity.value = 1;
        document.body.style.overflow = product ? 'hidden' : '';
        if (product) {
            await nextTick();
            panel.value?.querySelector<HTMLElement>('button')?.focus();
        }
    },
);
onMounted(() => document.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="product"
                lang="ar"
                dir="rtl"
                class="fixed inset-0 z-[70] flex items-end justify-center lg:items-center lg:p-8"
                role="dialog"
                aria-modal="true"
                :aria-label="product.name"
            >
                <button
                    class="absolute inset-0 bg-[#241920]/65 backdrop-blur-sm"
                    type="button"
                    aria-label="إغلاق المعاينة"
                    @click="close"
                />
                <section
                    ref="panel"
                    class="relative flex h-[96dvh] w-full max-w-6xl flex-col overflow-hidden rounded-t-[2rem] border border-white/70 bg-white shadow-[0_30px_100px_rgba(36,25,32,0.3)] sm:h-auto sm:max-h-[94dvh] lg:h-[88vh] lg:rounded-[2.25rem]"
                >
                    <button
                        type="button"
                        class="absolute top-4 right-4 z-30 grid size-12 place-items-center rounded-full border border-[#392631] bg-[#392631] text-white shadow-[0_8px_24px_rgba(57,38,49,0.22)] transition hover:scale-105 hover:bg-[#704456] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a45f79] active:scale-95"
                        aria-label="إغلاق"
                        @click="close"
                    >
                        <X class="size-5" />
                    </button>
                    <div
                        class="min-h-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-contain scroll-smooth lg:grid lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:items-start lg:gap-10 lg:p-8"
                    >
                        <ProductGallery
                            :images="product.images"
                            :name="product.name"
                        />
                        <div
                            class="min-w-0 px-4 pt-6 pb-8 sm:px-6 lg:px-0 lg:pt-3 lg:pr-3 lg:pb-0"
                        >
                            <p class="store-eyebrow text-[#9a5870]">
                                {{ product.category?.name || 'متجر ناتي' }}
                            </p>
                            <h2 class="mt-3 max-w-xl">
                                <span
                                    v-if="productName.french"
                                    dir="ltr"
                                    lang="fr"
                                    class="store-display block text-left text-[clamp(1.8rem,4vw,2.7rem)] leading-[1.08] text-balance break-words text-[#392631]"
                                    >{{ productName.french }}</span
                                >
                                <span
                                    v-if="productName.arabic"
                                    dir="rtl"
                                    lang="ar"
                                    class="mt-2 block text-right text-[clamp(1.3rem,3vw,2rem)] leading-tight font-semibold text-balance break-words text-[#9a5870]"
                                    >{{ productName.arabic }}</span
                                >
                            </h2>
                            <div
                                class="mt-6 flex items-end justify-between gap-3 border-y border-[#eadfe1] py-4"
                            >
                                <div
                                    class="flex min-w-0 flex-col items-start gap-1 sm:flex-row sm:flex-wrap sm:items-baseline sm:gap-x-3"
                                >
                                    <strong
                                        class="text-xl font-bold text-[#8e5067] sm:text-2xl"
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
                                        class="text-xs font-medium text-[#745e67] decoration-[#a45f79] decoration-1 sm:text-sm"
                                        >{{
                                            formatMoney(
                                                product.compare_at_price!,
                                            )
                                        }}</del
                                    >
                                </div>
                                <div class="shrink-0">
                                    <div
                                        class="flex h-10 w-[7.25rem] items-center justify-between rounded-full border border-[#b9909f] bg-white text-[#392631] shadow-[0_6px_18px_rgba(57,38,49,0.08)] sm:h-11 sm:w-32"
                                    >
                                        <button
                                            type="button"
                                            class="grid size-9 place-items-center rounded-full text-[#392631] transition hover:bg-[#f2e3e7] focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-[#a45f79] active:bg-[#e5ced5] disabled:cursor-not-allowed disabled:text-[#ad9ca2] sm:size-10"
                                            :disabled="quantity <= 1"
                                            aria-label="تقليل الكمية"
                                            @click="quantity--"
                                        >
                                            <Minus class="size-3.5" />
                                        </button>
                                        <span
                                            class="w-6 text-center text-sm font-black text-[#392631] sm:text-base"
                                            >{{ quantity }}</span
                                        >
                                        <button
                                            type="button"
                                            class="grid size-9 place-items-center rounded-full text-[#392631] transition hover:bg-[#f2e3e7] focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-[#a45f79] active:bg-[#e5ced5] disabled:cursor-not-allowed disabled:text-[#ad9ca2] sm:size-10"
                                            :disabled="
                                                quantity >=
                                                product.stock_quantity
                                            "
                                            aria-label="زيادة الكمية"
                                            @click="quantity++"
                                        >
                                            <Plus class="size-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p
                                class="mt-4 inline-flex min-h-8 items-center rounded-full px-3 text-xs font-bold"
                                :class="
                                    available
                                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
                                        : 'bg-[#f5e9eb] text-[#9b586e] ring-1 ring-[#ead5db]'
                                "
                            >
                                {{
                                    available
                                        ? `${product.stock_quantity} متوفر في المخزون`
                                        : 'غير متوفر حالياً'
                                }}
                            </p>
                            <p
                                v-if="product.description"
                                class="mt-6 text-sm leading-7 whitespace-pre-line text-[#5f4b54]"
                            >
                                {{ product.description }}
                            </p>
                            <p
                                v-else
                                class="mt-5 rounded-2xl bg-[#f5e9eb] px-4 py-3 text-sm leading-6 text-[#624d56]"
                            >
                                جميع المعلومات المتوفرة عن هذا المنتج معروضة
                                أعلاه.
                            </p>
                            <div
                                v-if="
                                    product.is_pack && product.pack_items.length
                                "
                                class="mt-6 rounded-2xl border border-[#eadfe1] px-4 py-3"
                            >
                                <p class="text-xs font-bold text-[#8e5067]">
                                    محتويات الباقة
                                </p>
                                <ul
                                    class="mt-2 space-y-1.5 text-sm text-[#5f4b54]"
                                >
                                    <li
                                        v-for="item in product.pack_items"
                                        :key="item.id"
                                    >
                                        {{ item.quantity }} × {{ item.name }}
                                    </li>
                                </ul>
                            </div>
                            <div
                                v-if="
                                    product.usage_instructions ||
                                    product.ingredients ||
                                    product.precautions
                                "
                                class="mt-6 divide-y divide-[#eadfe1] border-y border-[#eadfe1]"
                            >
                                <details
                                    v-if="product.usage_instructions"
                                    class="py-4"
                                >
                                    <summary
                                        class="cursor-pointer text-sm font-bold text-[#392631]"
                                    >
                                        طريقة الاستخدام
                                    </summary>
                                    <p
                                        class="mt-3 text-sm leading-7 whitespace-pre-line text-[#5f4b54]"
                                    >
                                        {{ product.usage_instructions }}
                                    </p>
                                </details>
                                <details
                                    v-if="product.ingredients"
                                    class="py-4"
                                >
                                    <summary
                                        class="cursor-pointer text-sm font-bold text-[#392631]"
                                    >
                                        المكونات
                                    </summary>
                                    <p
                                        class="mt-3 text-sm leading-7 whitespace-pre-line text-[#5f4b54]"
                                    >
                                        {{ product.ingredients }}
                                    </p>
                                </details>
                                <details
                                    v-if="product.precautions"
                                    class="py-4"
                                >
                                    <summary
                                        class="cursor-pointer text-sm font-bold text-[#392631]"
                                    >
                                        الاحتياطات
                                    </summary>
                                    <p
                                        class="mt-3 text-sm leading-7 whitespace-pre-line text-[#5f4b54]"
                                    >
                                        {{ product.precautions }}
                                    </p>
                                </details>
                            </div>
                            <div v-if="product.reviews_enabled" class="mt-9">
                                <ProductReviews :product="product" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="shrink-0 border-t border-[#eadfe1] bg-[#fffbf8]/96 px-4 py-3 shadow-[0_-16px_40px_-32px_rgba(57,38,49,0.45)] backdrop-blur-xl sm:px-6 sm:py-4"
                    >
                        <div class="mx-auto w-full max-w-xl">
                            <button
                                type="button"
                                :disabled="!available || busy"
                                class="store-button-primary w-full justify-center"
                                @click="emit('add', product, quantity)"
                            >
                                <ShoppingBag class="size-4" />
                                {{
                                    busy
                                        ? 'جارٍ الإضافة…'
                                        : available
                                          ? `أضيفي إلى السلة · ${formatMoney(totalPrice)}`
                                          : 'غير متوفر حالياً'
                                }}
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>
