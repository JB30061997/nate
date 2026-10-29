<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingBag, Trash2, X } from '@lucide/vue';
import { computed, onBeforeUnmount, watch } from 'vue';
import type { StoreCartLine } from '@/types/store';
import { formatMoney } from '@/types/store';

const props = defineProps<{
    open: boolean;
    cart: StoreCartLine[];
    busy: number | null;
}>();
const emit = defineEmits<{
    close: [];
    change: [line: StoreCartLine, quantity: number];
    remove: [line: StoreCartLine];
    shop: [];
}>();
const total = computed(() =>
    props.cart.reduce(
        (sum, line) => sum + Number(line.price) * line.quantity,
        0,
    ),
);
const count = computed(() =>
    props.cart.reduce((sum, line) => sum + line.quantity, 0),
);
watch(
    () => props.open,
    (open) => {
        document.body.style.overflow = open ? 'hidden' : '';
    },
);
onBeforeUnmount(() => {
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body"
        ><Transition name="drawer"
            ><div v-if="open" lang="ar" dir="rtl" class="fixed inset-0 z-[80]">
                <button
                    type="button"
                    class="absolute inset-0 bg-[#241920]/55 backdrop-blur-[2px]"
                    aria-label="إغلاق السلة"
                    @click="$emit('close')"
                />
                <aside
                    class="absolute top-0 right-0 flex h-full w-full max-w-[460px] flex-col bg-[#fffbf8] text-[#392631] shadow-2xl"
                    role="dialog"
                    aria-modal="true"
                    aria-label="سلة التسوق"
                >
                    <header
                        class="flex items-center justify-between border-b border-[#eadfe1] px-5 py-5 sm:px-7"
                    >
                        <div>
                            <p class="store-eyebrow">اختياراتكِ</p>
                            <h2 class="store-display mt-1 text-3xl">
                                سلة التسوق
                                <span class="text-base text-[#9a7884]"
                                    >({{ count }})</span
                                >
                            </h2>
                        </div>
                        <button
                            type="button"
                            class="store-icon-button"
                            aria-label="إغلاق"
                            @click="$emit('close')"
                        >
                            <X class="size-5" />
                        </button>
                    </header>
                    <div class="flex-1 overflow-y-auto px-4 py-5 sm:px-6">
                        <div
                            v-if="!cart.length"
                            class="flex h-full flex-col items-center justify-center text-center"
                        >
                            <span
                                class="grid size-20 place-items-center rounded-full bg-[#f2e3e7]"
                                ><ShoppingBag class="size-8 text-[#a26077]"
                            /></span>
                            <h3 class="store-display mt-5 text-2xl">
                                سلتكِ في انتظاركِ
                            </h3>
                            <p
                                class="mt-2 max-w-xs text-sm leading-6 text-[#7c6870]"
                            >
                                اكتشفي المجموعة وأضيفي المنتجات التي تعجبكِ.
                            </p>
                            <button
                                type="button"
                                class="store-button-primary mt-6"
                                @click="$emit('shop')"
                            >
                                اكتشاف المنتجات
                            </button>
                        </div>
                        <div v-else class="space-y-4">
                            <article
                                v-for="line in cart"
                                :key="line.product_id"
                                class="grid grid-cols-[88px_1fr] gap-4 border-b border-[#eadfe1] pb-4"
                            >
                                <div
                                    class="aspect-[4/5] overflow-hidden rounded-2xl bg-[#f7efed]"
                                >
                                    <img
                                        v-if="line.image"
                                        :src="line.image"
                                        :alt="line.name"
                                        class="h-full w-full object-contain p-2"
                                    />
                                </div>
                                <div class="min-w-0">
                                    <h3
                                        class="line-clamp-2 text-sm leading-5 font-semibold text-[#392631]"
                                    >
                                        {{ line.name }}
                                    </h3>
                                    <p
                                        class="mt-2 text-sm font-semibold text-[#8e5067]"
                                    >
                                        {{ formatMoney(line.price) }}
                                    </p>
                                    <div
                                        class="mt-3 flex items-center justify-between"
                                    >
                                        <div
                                            class="flex h-10 items-center rounded-full border border-[#b9909f] bg-[#fffaf8] text-[#392631] shadow-[0_5px_15px_rgba(57,38,49,0.08)]"
                                        >
                                            <button
                                                type="button"
                                                class="grid size-9 place-items-center rounded-full text-[#392631] transition hover:bg-[#f2e3e7] focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-[#a45f79] active:bg-[#e5ced5] disabled:opacity-30"
                                                :disabled="
                                                    busy !== null ||
                                                    line.quantity <= 1
                                                "
                                                aria-label="تقليل الكمية"
                                                @click="
                                                    $emit(
                                                        'change',
                                                        line,
                                                        line.quantity - 1,
                                                    )
                                                "
                                            >
                                                <Minus
                                                    class="size-4 stroke-[2.7]"
                                                /></button
                                            ><span
                                                class="w-7 text-center text-sm font-black text-[#392631]"
                                                >{{ line.quantity }}</span
                                            ><button
                                                type="button"
                                                class="grid size-9 place-items-center rounded-full text-[#392631] transition hover:bg-[#f2e3e7] focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-[#a45f79] active:bg-[#e5ced5] disabled:opacity-30"
                                                :disabled="
                                                    busy !== null ||
                                                    line.quantity >= line.stock
                                                "
                                                aria-label="زيادة الكمية"
                                                @click="
                                                    $emit(
                                                        'change',
                                                        line,
                                                        line.quantity + 1,
                                                    )
                                                "
                                            >
                                                <Plus
                                                    class="size-4 stroke-[2.7]"
                                                />
                                            </button>
                                        </div>
                                        <button
                                            type="button"
                                            class="grid size-9 place-items-center text-[#9a6577]"
                                            :disabled="busy !== null"
                                            aria-label="حذف المنتج"
                                            @click="$emit('remove', line)"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                    <footer
                        v-if="cart.length"
                        class="border-t border-[#eadfe1] bg-white px-5 py-5 sm:px-7"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#715e66]"
                                >المجموع الفرعي</span
                            ><strong
                                class="store-display text-2xl text-[#392631]"
                                >{{ formatMoney(total) }}</strong
                            >
                        </div>
                        <p class="mt-1 text-[11px] text-[#97838b]">
                            يتم تأكيد مصاريف التوصيل عند تأكيد الطلب.
                        </p>
                        <Link
                            href="/checkout"
                            class="store-button-primary mt-5 w-full justify-center"
                            >إتمام الطلب</Link
                        ><button
                            type="button"
                            class="mt-3 w-full py-2 text-xs font-semibold text-[#715e66]"
                            @click="$emit('close')"
                        >
                            متابعة التسوق
                        </button>
                    </footer>
                </aside>
            </div></Transition
        ></Teleport
    >
</template>
