<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

import { ChevronDown, Search, X } from '@lucide/vue';

import { computed, nextTick, ref } from 'vue';

import CartDrawer from '@/components/store/CartDrawer.vue';

import CategoryShowcase from '@/components/store/CategoryShowcase.vue';

import HeroSection from '@/components/store/HeroSection.vue';

import ProductCard from '@/components/store/ProductCard.vue';

import ProductQuickView from '@/components/store/ProductQuickView.vue';

import StoreFooter from '@/components/store/StoreFooter.vue';

import StoreFloatingActions from '@/components/store/StoreFloatingActions.vue';

import StoreHeader from '@/components/store/StoreHeader.vue';

import type { StoreCartLine, StoreCategory, StoreProduct } from '@/types/store';

const props = defineProps<{
    products: StoreProduct[];
    sliderProducts: StoreProduct[];

    categories: StoreCategory[];

    cart: StoreCartLine[];

    cartCount: number;
    storeInfo: Record<string, string | null>;
}>();

const search = ref('');

const category = ref('all');

const sort = ref('featured');

const cartOpen = ref(false);

const menuOpen = ref(false);

const searchOpen = ref(false);

const busy = ref<number | null>(null);

const selectedProduct = ref<StoreProduct | null>(null);

const searchInput = ref<HTMLInputElement | null>(null);

const filteredProducts = computed(() => {
    const term = search.value.trim().toLocaleLowerCase();

    const products = props.products.filter(
        (product) =>
            (category.value === 'all' ||
                product.category?.slug === category.value) &&
            (!term ||
                `${product.name} ${product.category?.name || ''}`

                    .toLocaleLowerCase()

                    .includes(term)),
    );

    return [...products].sort((a, b) => {
        if (sort.value === 'price_asc')
            return Number(a.price) - Number(b.price);

        if (sort.value === 'price_desc')
            return Number(b.price) - Number(a.price);

        if (sort.value === 'name') return a.name.localeCompare(b.name);

        return Number(b.is_featured) - Number(a.is_featured);
    });
});

const featuredProducts = computed(() =>
    props.products.filter((product) => product.is_featured).slice(0, 8),
);

const scrollToCollection = async () => {
    await nextTick();

    document

        .getElementById('collection')

        ?.scrollIntoView({ behavior: 'smooth' });
};

const selectCategory = (slug: string) => {
    category.value = slug;

    menuOpen.value = false;

    void scrollToCollection();
};

const openSearch = async () => {
    searchOpen.value = true;

    await nextTick();

    searchInput.value?.focus();
};

const add = (product: StoreProduct, quantity = 1) => {
    if (busy.value !== null) return;

    busy.value = product.id;

    router.post(
        '/panier',

        { product_id: product.id, quantity },

        {
            preserveScroll: true,

            onSuccess: () => {
                selectedProduct.value = null;

                cartOpen.value = true;
            },

            onFinish: () => {
                busy.value = null;
            },
        },
    );
};

const change = (line: StoreCartLine, quantity: number) => {
    busy.value = line.product_id;

    router.patch(
        `/panier/${line.product_id}`,

        { quantity },

        {
            preserveScroll: true,

            onFinish: () => {
                busy.value = null;
            },
        },
    );
};

const remove = (line: StoreCartLine) => {
    busy.value = line.product_id;

    router.delete(`/panier/${line.product_id}`, {
        preserveScroll: true,

        onFinish: () => {
            busy.value = null;
        },
    });
};
</script>

<template>
    <Head title="ناتي بوتيك | الجمال والعناية"
        ><meta
            name="description"
            content="اكتشفي منتجات ناتي للعناية بالبشرة والشعر والجسم، مع إمكانية الدفع عند الاستلام."
    /></Head>

    <div
        lang="ar"
        dir="rtl"
        class="min-h-screen overflow-x-clip bg-[#fffbf8] text-[#392631] antialiased"
    >
        <StoreHeader
            :cart-count="cartCount"
            :menu-open="menuOpen"
            @cart="cartOpen = true"
            @search="openSearch"
            @update:menu-open="menuOpen = $event"
        />

        <main>
            <HeroSection
                :products="sliderProducts"
                :modal-open="selectedProduct !== null"
                @open="selectedProduct = $event"
            />

            <!-- <section class="border-b border-[#eadfe1] bg-[#fffdfb]">
                <div
                    class="mx-auto grid max-w-[1180px] grid-cols-3 gap-2 px-3 py-4 text-center sm:gap-4 sm:px-8 sm:py-6"
                >
                    <div
                        class="rounded-2xl border border-[#f0e5e2] bg-white px-2 py-3 shadow-[0_10px_30px_-26px_rgba(57,38,49,0.55)] sm:rounded-3xl sm:py-4"
                    >
                        <strong
                            class="text-xl font-black text-[#392631] sm:text-3xl"
                            >{{ products.length }}</strong
                        ><span
                            class="mt-1 block text-[9px] tracking-[0.13em] text-[#8e7880] uppercase sm:text-[10px]"
                            >المنتجات</span
                        >
                    </div>

                    <div
                        class="rounded-2xl border border-[#f0e5e2] bg-white px-2 py-3 shadow-[0_10px_30px_-26px_rgba(57,38,49,0.55)] sm:rounded-3xl sm:py-4"
                    >
                        <strong
                            class="text-xl font-black text-[#392631] sm:text-3xl"
                            >{{ categories.length }}</strong
                        ><span
                            class="mt-1 block text-[9px] tracking-[0.13em] text-[#8e7880] uppercase sm:text-[10px]"
                            >الأقسام</span
                        >
                    </div>

                    <div
                        class="rounded-2xl border border-[#f0e5e2] bg-white px-2 py-3 shadow-[0_10px_30px_-26px_rgba(57,38,49,0.55)] sm:rounded-3xl sm:py-4"
                    >
                        <strong
                            class="text-xl font-black text-[#392631] sm:text-3xl"
                            >MAD</strong
                        ><span
                            class="mt-1 block text-[9px] tracking-[0.13em] text-[#8e7880] uppercase sm:text-[10px]"
                            >الأثمنة بالدرهم</span
                        >
                    </div>
                </div>
            </section> -->

            <CategoryShowcase
                :categories="categories"
                :products="products"
                @select="selectCategory"
            />

            <section
                v-if="featuredProducts.length"
                id="nouveautes"
                class="store-section bg-[#f6eceb]"
            >
                <div class="store-section-heading">
                    <div>
                        <p class="store-eyebrow">مختارات ناتي</p>

                        <h2 class="store-title">منتجات اخترناها لكِ</h2>
                    </div>

                    <p class="max-w-md text-sm leading-6 text-[#7c6870]">
                        مجموعة مختارة من المنتجات التي نعرضها لكِ في المتجر.
                    </p>
                </div>

                <div
                    class="grid grid-cols-2 gap-x-3 gap-y-7 sm:gap-x-4 sm:gap-y-9 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-6"
                >
                    <ProductCard
                        v-for="product in featuredProducts"
                        :key="product.id"
                        :product="product"
                        :busy="busy === product.id"
                        @add="add"
                        @open="selectedProduct = $event"
                    />
                </div>
            </section>

            <section
                id="collection"
                class="store-section scroll-mt-24 bg-[#fffbf8]"
            >
                <div class="store-section-heading">
                    <div>
                        <p class="store-eyebrow">جميع المنتجات</p>

                        <h2 class="store-title">اختاري ما يناسبكِ</h2>

                        <p class="mt-2 text-sm text-[#8e7880]">
                            {{ filteredProducts.length }} منتج
                        </p>
                    </div>
                </div>

                <div class="mb-9 grid gap-3 sm:max-w-2xl sm:grid-cols-2">
                    <label class="store-filter-field">
                        <span class="store-filter-label">القسم</span>
                        <span class="relative flex items-center">
                            <select
                                v-model="category"
                                class="store-filter-select"
                            >
                                <option value="all">جميع الأقسام</option>
                                <option
                                    v-for="item in categories"
                                    :key="item.id"
                                    :value="item.slug"
                                >
                                    {{ item.name }}
                                </option>
                            </select>
                            <ChevronDown
                                class="pointer-events-none absolute left-4 size-4 text-[#9d7180]"
                            />
                        </span>
                    </label>

                    <label class="store-filter-field">
                        <span class="store-filter-label">الترتيب</span>
                        <span class="relative flex items-center">
                            <select v-model="sort" class="store-filter-select">
                                <option value="featured">
                                    المنتجات المختارة
                                </option>
                                <option value="price_asc">
                                    من الأرخص إلى الأغلى
                                </option>
                                <option value="price_desc">
                                    من الأغلى إلى الأرخص
                                </option>
                                <option value="name">الاسم أبجدياً</option>
                            </select>
                            <ChevronDown
                                class="pointer-events-none absolute left-4 size-4 text-[#9d7180]"
                            />
                        </span>
                    </label>
                </div>

                <div
                    v-if="filteredProducts.length"
                    class="grid grid-cols-2 gap-x-3 gap-y-7 sm:gap-x-4 sm:gap-y-9 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-6 lg:gap-y-12"
                >
                    <ProductCard
                        v-for="product in filteredProducts"
                        :key="product.id"
                        :product="product"
                        :busy="busy === product.id"
                        @add="add"
                        @open="selectedProduct = $event"
                    />
                </div>

                <div
                    v-else
                    class="rounded-[2rem] border border-dashed border-[#d9c9ce] bg-white px-5 py-16 text-center"
                >
                    <p class="store-display text-3xl">لم نجد أي منتج</p>

                    <p class="mt-2 text-sm text-[#7c6870]">
                        جرّبي كلمة أخرى أو اعرضي جميع المنتجات.
                    </p>

                    <button
                        type="button"
                        class="store-button-secondary mt-5"
                        @click="
                            search = '';

                            category = 'all';
                        "
                    >
                        عرض جميع المنتجات
                    </button>
                </div>
            </section>

            <section
                class="bg-[#392631] px-5 py-16 text-center text-white sm:py-24"
            >
                <p class="store-eyebrow !text-[#d9c1a2]">تسوّق سهل وواضح</p>

                <h2
                    class="store-display mx-auto mt-4 max-w-3xl text-4xl leading-tight sm:text-6xl"
                >
                    اختاري منتجاتكِ المفضلة واطلبيها بكل سهولة.
                </h2>

                <a
                    href="#collection"
                    class="mt-8 inline-flex min-h-12 items-center rounded-full bg-[#fffbf8] px-7 text-sm font-semibold text-[#392631]"
                    >شاهدي جميع المنتجات</a
                >
            </section>
        </main>

        <StoreFooter
            :categories="categories"
            :store-info="storeInfo"
            @select="selectCategory"
        />

        <StoreFloatingActions
            :cart-count="cartCount"
            whatsapp-number="+212 688-999539"
            @cart="cartOpen = true"
        />

        <CartDrawer
            :open="cartOpen"
            :cart="cart"
            :busy="busy"
            @close="cartOpen = false"
            @change="change"
            @remove="remove"
            @shop="
                cartOpen = false;

                scrollToCollection();
            "
        />

        <ProductQuickView
            :product="selectedProduct"
            :busy="busy === selectedProduct?.id"
            @close="selectedProduct = null"
            @add="add"
        />

        <Transition name="search"
            ><div
                v-if="searchOpen"
                class="fixed inset-0 z-[90] bg-[#fffbf8]/98 p-5 backdrop-blur"
            >
                <button
                    type="button"
                    class="store-icon-button absolute top-5 left-5"
                    aria-label="إغلاق البحث"
                    @click="searchOpen = false"
                >
                    <X class="size-5" />
                </button>

                <div
                    class="mx-auto flex h-full max-w-3xl flex-col justify-center"
                >
                    <p class="store-eyebrow">ابحثي عن المنتج الذي تريدينه</p>

                    <label
                        class="mt-5 flex items-center border-b border-[#816a73] py-4"
                        ><Search class="ml-4 size-6 text-[#9a6577]" /><input
                            ref="searchInput"
                            v-model="search"
                            type="search"
                            placeholder="اسم المنتج أو القسم…"
                            class="store-display min-w-0 flex-1 bg-transparent text-3xl outline-none sm:text-5xl"
                            @keydown.enter="
                                searchOpen = false;

                                scrollToCollection();
                            "
                    /></label>

                    <p class="mt-4 text-sm text-[#806b73]">
                        {{ filteredProducts.length }} نتيجة
                    </p>

                    <button
                        type="button"
                        class="store-button-primary mt-8 self-start"
                        @click="
                            searchOpen = false;

                            scrollToCollection();
                        "
                    >
                        عرض النتائج
                    </button>
                </div>
            </div></Transition
        >
    </div>
</template>
