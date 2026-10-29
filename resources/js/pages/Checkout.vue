<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Banknote,
    Check,
    LockKeyhole,
    MapPin,
    PackageCheck,
    Phone,
    ShoppingBag,
    UserRound,
} from '@lucide/vue';
import { computed } from 'vue';

type CartLine = {
    product_id: number;
    name: string;
    price: number;
    quantity: number;
    image: string | null;
    stock: number;
    line_total: number;
};

const props = defineProps<{
    cart: CartLine[];
    subtotal: number;
    deliveryFee: number;
    checkoutToken: string;
}>();

const form = useForm({
    customer_name: '',
    customer_phone: '',
    customer_city: '',
    customer_address: '',
    customer_notes: '',
    checkout_token: props.checkoutToken,
});

const checkoutError = computed(
    () =>
        (form.errors as Record<string, string>).cart ??
        form.errors.checkout_token,
);

const money = (amount: number): string =>
    Number(amount).toLocaleString('fr-MA', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }) + ' MAD';

const submit = (): void => {
    if (!form.processing) {
        form.post('/checkout', { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="إتمام الطلب" />

    <div lang="ar" dir="rtl" class="min-h-screen bg-[#fffcf9] text-[#382b32]">
        <header class="border-b border-[#f1e5e7] bg-white">
            <div
                class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8"
            >
                <Link
                    href="/"
                    class="inline-flex min-h-11 items-center gap-2 rounded-full px-3 text-sm font-semibold text-[#715761] hover:bg-[#faf0f3]"
                >
                    <ArrowLeft class="size-4 rotate-180" />
                    <span class="hidden sm:inline">العودة إلى المتجر</span>
                </Link>
                <Link
                    href="/"
                    class="text-xl font-black tracking-[0.1em] text-[#49343e] sm:text-2xl"
                >
                    NATÉ<span class="text-[#c88398]">.</span>
                </Link>
                <span
                    class="inline-flex min-h-11 items-center gap-2 text-xs font-semibold text-[#917b84]"
                >
                    <LockKeyhole class="size-4" />
                    <span class="hidden sm:inline">طلب آمن</span>
                </span>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-7 sm:px-6 sm:py-10 lg:px-8">
            <div class="mb-7">
                <p
                    class="text-xs font-bold tracking-[0.18em] text-[#ad7187] uppercase"
                >
                    الخطوة الأخيرة
                </p>
                <h1 class="mt-2 font-serif text-3xl font-semibold sm:text-4xl">
                    إتمام طلبي
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#8f7b84]">
                    راجعي سلتكِ وأدخلي معلوماتكِ الخاصة بالتوصيل.
                </p>
            </div>

            <form
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_380px]"
                @submit.prevent="submit"
            >
                <div class="space-y-5">
                    <div
                        v-if="checkoutError"
                        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    >
                        {{ checkoutError }}
                    </div>

                    <section
                        class="rounded-[2rem] border border-[#eee1e3] bg-white p-5 shadow-[0_20px_60px_-45px_rgba(80,49,62,0.45)] sm:p-7"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="grid size-10 place-items-center rounded-2xl bg-[#f8e8ed] text-[#a7677e]"
                            >
                                <UserRound class="size-5" />
                            </span>
                            <div>
                                <h2 class="font-serif text-xl font-semibold">
                                    معلوماتكِ
                                </h2>
                                <p class="text-xs text-[#9a858d]">
                                    لتأكيد طلبكِ وتوصيله إليكِ.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            <label class="block sm:col-span-2">
                                <span
                                    class="mb-2 block text-sm font-semibold text-[#594851]"
                                    >الاسم الكامل</span
                                >
                                <input
                                    v-model="form.customer_name"
                                    type="text"
                                    autocomplete="name"
                                    required
                                    autofocus
                                    class="min-h-12 w-full rounded-2xl border border-[#e2d4d8] bg-[#fffdfb] px-4 text-sm transition outline-none focus:border-[#bb788e] focus:ring-4 focus:ring-[#f4e4e9]"
                                    placeholder="الاسم والنسب"
                                />
                                <span
                                    v-if="form.errors.customer_name"
                                    class="mt-1.5 block text-xs text-red-600"
                                    >{{ form.errors.customer_name }}</span
                                >
                            </label>

                            <label class="block">
                                <span
                                    class="mb-2 flex items-center gap-2 text-sm font-semibold text-[#594851]"
                                >
                                    <Phone class="size-4" /> رقم الهاتف المغربي
                                </span>
                                <input
                                    v-model="form.customer_phone"
                                    type="tel"
                                    inputmode="tel"
                                    autocomplete="tel"
                                    dir="ltr"
                                    required
                                    class="min-h-12 w-full rounded-2xl border border-[#e2d4d8] bg-[#fffdfb] px-4 text-sm transition outline-none focus:border-[#bb788e] focus:ring-4 focus:ring-[#f4e4e9]"
                                    placeholder="0612345678"
                                />
                                <span
                                    v-if="form.errors.customer_phone"
                                    class="mt-1.5 block text-xs text-red-600"
                                    >{{ form.errors.customer_phone }}</span
                                >
                            </label>

                            <label class="block">
                                <span
                                    class="mb-2 flex items-center gap-2 text-sm font-semibold text-[#594851]"
                                >
                                    <MapPin class="size-4" /> المدينة
                                </span>
                                <input
                                    v-model="form.customer_city"
                                    type="text"
                                    autocomplete="address-level2"
                                    required
                                    class="min-h-12 w-full rounded-2xl border border-[#e2d4d8] bg-[#fffdfb] px-4 text-sm transition outline-none focus:border-[#bb788e] focus:ring-4 focus:ring-[#f4e4e9]"
                                    placeholder="الدار البيضاء"
                                />
                                <span
                                    v-if="form.errors.customer_city"
                                    class="mt-1.5 block text-xs text-red-600"
                                    >{{ form.errors.customer_city }}</span
                                >
                            </label>

                            <label class="block sm:col-span-2">
                                <span
                                    class="mb-2 block text-sm font-semibold text-[#594851]"
                                    >عنوان التوصيل</span
                                >
                                <textarea
                                    v-model="form.customer_address"
                                    rows="3"
                                    autocomplete="street-address"
                                    required
                                    class="w-full resize-y rounded-2xl border border-[#e2d4d8] bg-[#fffdfb] px-4 py-3 text-sm transition outline-none focus:border-[#bb788e] focus:ring-4 focus:ring-[#f4e4e9]"
                                    placeholder="الحي، الشارع، العمارة، الشقة…"
                                ></textarea>
                                <span
                                    v-if="form.errors.customer_address"
                                    class="mt-1.5 block text-xs text-red-600"
                                    >{{ form.errors.customer_address }}</span
                                >
                            </label>

                            <label class="block sm:col-span-2">
                                <span
                                    class="mb-2 block text-sm font-semibold text-[#594851]"
                                    >ملاحظات
                                    <span class="font-normal text-[#a08d94]"
                                        >(اختياري)</span
                                    ></span
                                >
                                <textarea
                                    v-model="form.customer_notes"
                                    rows="3"
                                    class="w-full resize-y rounded-2xl border border-[#e2d4d8] bg-[#fffdfb] px-4 py-3 text-sm transition outline-none focus:border-[#bb788e] focus:ring-4 focus:ring-[#f4e4e9]"
                                    placeholder="تعليمات إضافية للتوصيل"
                                ></textarea>
                            </label>
                        </div>
                    </section>

                    <section
                        class="rounded-[2rem] border border-[#eee1e3] bg-white p-5 sm:p-7"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="grid size-10 place-items-center rounded-2xl bg-[#f3ece4] text-[#8f704f]"
                            >
                                <Banknote class="size-5" />
                            </span>
                            <div>
                                <h2 class="font-serif text-xl font-semibold">
                                    الدفع
                                </h2>
                                <p class="text-xs text-[#9a858d]">
                                    ادفعي مباشرة عند استلام الطلب.
                                </p>
                            </div>
                        </div>
                        <div
                            class="mt-5 flex items-center gap-3 rounded-2xl border border-[#eaded8] bg-[#fcf8f4] p-4"
                        >
                            <span
                                class="grid size-8 place-items-center rounded-full bg-[#e4efe1] text-[#557150]"
                            >
                                <Check class="size-4" />
                            </span>
                            <div>
                                <p class="text-sm font-bold">
                                    الدفع عند الاستلام
                                </p>
                                <p class="mt-0.5 text-xs text-[#928087]">
                                    لا نطلب منكِ أي دفع عبر الإنترنت.
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <aside
                    class="rounded-[2rem] border border-[#eee1e3] bg-white p-5 shadow-[0_20px_60px_-45px_rgba(80,49,62,0.45)] lg:sticky lg:top-5"
                >
                    <div class="flex items-center gap-3">
                        <ShoppingBag class="size-5 text-[#a7677e]" />
                        <h2 class="font-serif text-xl font-semibold">طلبكِ</h2>
                    </div>

                    <div
                        class="mt-5 max-h-[360px] space-y-4 overflow-y-auto pl-1"
                    >
                        <article
                            v-for="line in cart"
                            :key="line.product_id"
                            class="flex gap-3"
                        >
                            <div
                                class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-[#faf4f2]"
                            >
                                <img
                                    v-if="line.image"
                                    :src="line.image"
                                    :alt="line.name"
                                    class="h-full w-full object-cover"
                                />
                                <PackageCheck
                                    v-else
                                    class="size-5 text-[#c08a9b]"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="line-clamp-2 text-sm leading-5 font-semibold"
                                >
                                    {{ line.name }}
                                </p>
                                <p class="mt-1 text-xs text-[#9b878f]">
                                    {{ line.quantity }} ×
                                    {{ money(line.price) }}
                                </p>
                            </div>
                            <p
                                class="shrink-0 text-sm font-bold text-[#9c6076]"
                            >
                                {{ money(line.line_total) }}
                            </p>
                        </article>
                    </div>

                    <div class="mt-5 space-y-3 border-t border-[#f0e3e6] pt-5">
                        <div
                            class="flex justify-between text-sm text-[#79666e]"
                        >
                            <span>المجموع الفرعي</span>
                            <span>{{ money(subtotal) }}</span>
                        </div>
                        <div
                            class="flex justify-between text-sm text-[#79666e]"
                        >
                            <span>التوصيل</span>
                            <span>{{
                                deliveryFee > 0 ? money(deliveryFee) : 'مجاني'
                            }}</span>
                        </div>
                        <div
                            class="flex items-end justify-between border-t border-[#f0e3e6] pt-4"
                        >
                            <span class="font-bold">المجموع الحالي</span>
                            <strong class="text-xl text-[#a15f78]">{{
                                money(subtotal + deliveryFee)
                            }}</strong>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-6 inline-flex min-h-13 w-full items-center justify-center gap-2 rounded-full bg-[#a66b82] px-5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[#8f5870] disabled:cursor-wait disabled:opacity-60"
                    >
                        <LockKeyhole class="size-4" />
                        {{
                            form.processing
                                ? 'جارٍ تسجيل الطلب…'
                                : 'تأكيد الطلب'
                        }}
                    </button>
                    <p
                        class="mt-3 text-center text-[11px] leading-5 text-[#a08b93]"
                    >
                        سيتم التحقق من المخزون والأسعار عند تأكيد الطلب.
                    </p>
                </aside>
            </form>
        </main>
    </div>
</template>
