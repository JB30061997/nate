<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, Search, ShoppingBag, UserRound, X } from '@lucide/vue';

defineProps<{ cartCount: number; menuOpen: boolean }>();
const emit = defineEmits<{
    cart: [];
    search: [];
    'update:menuOpen': [value: boolean];
}>();
</script>

<template>
    <div>
        <div
            class="bg-[#392631] px-4 py-2.5 text-center text-[10px] font-semibold tracking-[0.2em] text-[#fffaf7] uppercase sm:text-[11px]"
        >
            الدفع عند الاستلام · التوفر حسب المخزون
        </div>
        <header
            class="sticky top-0 z-40 border-b border-[#eadfe1] bg-[#fffbf8]/95 backdrop-blur-xl"
        >
            <div
                class="mx-auto flex h-[74px] max-w-[1440px] items-center justify-between px-4 sm:px-6 lg:px-10"
            >
                <button
                    class="grid size-11 place-items-center rounded-full border border-[#e8dcde] lg:hidden"
                    type="button"
                    aria-label="فتح القائمة"
                    @click="emit('update:menuOpen', !menuOpen)"
                >
                    <X v-if="menuOpen" class="size-5" /><Menu
                        v-else
                        class="size-5"
                    />
                </button>
                <a
                    href="/"
                    class="text-center text-[25px] font-semibold tracking-[0.22em] text-[#392631] sm:text-[29px]"
                >
                    NATÉ<span class="text-[#b86f87]">.</span>
                    <span
                        class="block text-[8px] font-medium tracking-[0.42em] text-[#927a84]"
                        >متجر الجمال</span
                    >
                </a>
                <nav
                    class="hidden items-center gap-8 text-[13px] font-medium tracking-wide text-[#57444d] lg:flex"
                    aria-label="التنقل الرئيسي"
                >
                    <a href="#nouveautes" class="store-link">مختاراتنا</a
                    ><a href="#categories" class="store-link">الأقسام</a
                    ><a href="#collection" class="store-link">المنتجات</a>
                </nav>
                <div class="flex items-center gap-1 sm:gap-2">
                    <button
                        class="store-icon-button hidden sm:grid"
                        type="button"
                        aria-label="البحث"
                        @click="emit('search')"
                    >
                        <Search class="size-[19px]" />
                    </button>
                    <Link
                        href="/login"
                        class="store-icon-button hidden sm:grid"
                        aria-label="الحساب"
                        ><UserRound class="size-[19px]"
                    /></Link>
                    <button
                        class="store-icon-button relative"
                        type="button"
                        aria-label="فتح السلة"
                        @click="emit('cart')"
                    >
                        <ShoppingBag class="size-[20px]" /><span
                            v-if="cartCount"
                            class="absolute -top-0.5 -right-0.5 grid size-5 place-items-center rounded-full bg-[#b86f87] text-[10px] font-bold text-white"
                            >{{ cartCount }}</span
                        >
                    </button>
                </div>
            </div>
            <Transition name="menu">
                <nav
                    v-if="menuOpen"
                    class="border-t border-[#eee3e4] bg-[#fffbf8] px-5 py-5 lg:hidden"
                    aria-label="التنقل على الهاتف"
                >
                    <a
                        v-for="item in [
                            ['مختاراتنا', '#nouveautes'],
                            ['الأقسام', '#categories'],
                            ['جميع المنتجات', '#collection'],
                        ]"
                        :key="item[0]"
                        :href="item[1]"
                        class="flex min-h-12 items-center border-b border-[#eee3e4] text-sm font-medium"
                        @click="emit('update:menuOpen', false)"
                        >{{ item[0] }}</a
                    >
                    <Link
                        href="/login"
                        class="mt-4 flex min-h-12 items-center gap-2 text-sm"
                        ><UserRound class="size-4" /> حساب الإدارة</Link
                    >
                </nav>
            </Transition>
        </header>
    </div>
</template>
