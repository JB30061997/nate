<script setup lang="ts">
import { ArrowUpRight } from '@lucide/vue';
import type { StoreCategory, StoreProduct } from '@/types/store';
import { splitBilingualName } from '@/types/store';

const props = defineProps<{
    categories: StoreCategory[];
    products: StoreProduct[];
}>();
defineEmits<{ select: [slug: string] }>();
const cover = (category: StoreCategory) =>
    category.image ||
    props.products.find((product) => product.category?.id === category.id)
        ?.main_image;
const displayName = (category: StoreCategory) =>
    splitBilingualName(category.name);
</script>

<template>
    <section id="categories" class="store-section bg-[#fffbf8]">
        <div class="store-section-heading">
            <div>
                <p class="store-eyebrow">اكتشفي</p>
                <h2 class="store-title">عالمكِ الخاص بالجمال</h2>
            </div>
            <p class="max-w-md text-sm leading-6 text-[#7c6870]">
                تصفحي المنتجات حسب الأقسام المتوفرة فعلياً في المتجر.
            </p>
        </div>
        <div
            class="grid auto-cols-[78%] grid-flow-col gap-4 overflow-x-auto pb-3 sm:auto-cols-[42%] lg:grid-flow-row lg:grid-cols-4 lg:overflow-visible"
        >
            <button
                v-for="(category, index) in categories"
                :key="category.id"
                type="button"
                class="group relative h-[330px] overflow-hidden rounded-[1.75rem] bg-[#f2e3e7] text-right sm:h-[380px]"
                @click="$emit('select', category.slug)"
            >
                <img
                    v-if="cover(category)"
                    :src="cover(category)!"
                    :alt="category.name"
                    class="h-full w-full object-contain p-8 transition duration-700 group-hover:scale-105"
                    loading="lazy"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-t from-[#392631]/80 via-transparent to-transparent"
                />
                <div class="absolute inset-x-0 bottom-0 p-5 text-white sm:p-6">
                    <div class="min-w-0 text-center">
                        <span class="text-[10px] tracking-[0.12em] uppercase"
                            >{{ category.products_count }} منتج</span
                        >
                        <h3 class="mt-2 min-w-0">
                            <span
                                v-if="displayName(category).french"
                                dir="ltr"
                                lang="fr"
                                class="mx-auto block max-w-full text-center text-[clamp(1rem,2vw,1.45rem)] leading-tight font-semibold text-balance break-words"
                                >{{ displayName(category).french }}</span
                            >
                            <span
                                v-if="displayName(category).arabic"
                                dir="rtl"
                                lang="ar"
                                class="mx-auto mt-1 block max-w-full text-center text-[clamp(0.9rem,1.7vw,1.2rem)] leading-snug font-medium text-balance break-words text-white/88"
                                >{{ displayName(category).arabic }}</span
                            >
                        </h3>
                    </div>
                    <span
                        class="absolute bottom-5 left-5 grid size-10 place-items-center rounded-full border border-white/40 bg-white/10 backdrop-blur sm:bottom-6 sm:left-6"
                        ><ArrowUpRight class="size-4"
                    /></span>
                </div>
                <span
                    class="absolute top-5 left-5 text-[11px] font-semibold tracking-[0.12em] text-[#72505e]"
                    >0{{ index + 1 }}</span
                >
            </button>
        </div>
    </section>
</template>
