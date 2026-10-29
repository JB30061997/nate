<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check, PackageCheck, Phone, ShoppingBag } from '@lucide/vue';

type OrderLine = {
    product_name: string;
    unit_price: string;
    quantity: number;
    line_total: string;
};

type Order = {
    order_number: string;
    customer_name: string;
    customer_phone: string;
    customer_city: string | null;
    customer_address: string | null;
    subtotal: string;
    delivery_fee: string;
    total: string;
    status: string;
    items: OrderLine[];
};

defineProps<{ order: Order }>();

const money = (amount: string): string =>
    Number(amount).toLocaleString('fr-MA', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }) + ' MAD';
</script>

<template>
    <Head title="تم تأكيد الطلب" />

    <div
        lang="ar"
        dir="rtl"
        class="min-h-screen bg-[#fffcf9] px-4 py-8 text-[#382b32] sm:px-6 sm:py-12"
    >
        <main class="mx-auto max-w-2xl">
            <div class="text-center">
                <span
                    class="mx-auto grid size-16 place-items-center rounded-full bg-[#e5f0e2] text-[#557650]"
                >
                    <Check class="size-8" />
                </span>
                <p
                    class="mt-6 text-xs font-bold tracking-[0.18em] text-[#ad7187] uppercase"
                >
                    تم تسجيل الطلب
                </p>
                <h1 class="mt-2 font-serif text-3xl font-semibold sm:text-4xl">
                    شكراً لكِ {{ order.customer_name }}!
                </h1>
                <p
                    class="mx-auto mt-3 max-w-lg text-sm leading-6 text-[#8e7a83]"
                >
                    تم تسجيل طلبكِ بنجاح. سنتواصل معكِ لتأكيد التوصيل والمبلغ
                    النهائي.
                </p>
            </div>

            <section
                class="mt-8 overflow-hidden rounded-[2rem] border border-[#eee1e3] bg-white shadow-[0_24px_70px_-50px_rgba(80,49,62,0.5)]"
            >
                <div class="bg-[#49343e] px-5 py-5 text-white sm:px-7">
                    <p class="text-xs text-white/65">رقم الطلب</p>
                    <p
                        class="mt-1 font-mono text-sm font-bold tracking-wide break-all"
                        dir="ltr"
                    >
                        {{ order.order_number }}
                    </p>
                </div>

                <div class="p-5 sm:p-7">
                    <div class="space-y-4">
                        <article
                            v-for="line in order.items"
                            :key="line.product_name + line.unit_price"
                            class="flex items-center gap-3"
                        >
                            <span
                                class="grid size-11 shrink-0 place-items-center rounded-2xl bg-[#f8e9ed] text-[#a4677d]"
                            >
                                <PackageCheck class="size-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">
                                    {{ line.product_name }}
                                </p>
                                <p class="mt-1 text-xs text-[#96828a]">
                                    {{ line.quantity }} ×
                                    {{ money(line.unit_price) }}
                                </p>
                            </div>
                            <strong class="text-sm text-[#9c6076]">
                                {{ money(line.line_total) }}
                            </strong>
                        </article>
                    </div>

                    <div class="mt-6 space-y-3 border-t border-[#f0e3e6] pt-5">
                        <div
                            class="flex justify-between text-sm text-[#79666e]"
                        >
                            <span>المجموع الفرعي</span>
                            <span>{{ money(order.subtotal) }}</span>
                        </div>
                        <div
                            class="flex justify-between text-sm text-[#79666e]"
                        >
                            <span>التوصيل</span>
                            <span>{{ money(order.delivery_fee) }}</span>
                        </div>
                        <div
                            class="flex justify-between border-t border-[#f0e3e6] pt-4 text-lg font-bold"
                        >
                            <span>المجموع</span>
                            <span class="text-[#a15f78]">{{
                                money(order.total)
                            }}</span>
                        </div>
                    </div>

                    <div
                        class="mt-6 rounded-2xl bg-[#fcf7f3] p-4 text-sm text-[#745f67]"
                    >
                        <p class="flex items-center gap-2 font-semibold">
                            <Phone class="size-4" />
                            الهاتف: <bdi>{{ order.customer_phone }}</bdi>
                        </p>
                        <p class="mt-2 leading-6">
                            التوصيل إلى {{ order.customer_city }}،
                            {{ order.customer_address }}
                        </p>
                        <p class="mt-2 font-semibold">الدفع عند الاستلام</p>
                    </div>
                </div>
            </section>

            <Link
                href="/"
                class="mt-6 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-full bg-[#a66b82] px-5 text-sm font-bold text-white transition hover:bg-[#8f5870]"
            >
                <ShoppingBag class="size-4" />
                العودة إلى المتجر
            </Link>
        </main>
    </div>
</template>
