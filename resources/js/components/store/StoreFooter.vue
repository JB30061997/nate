<script setup lang="ts">
const props = defineProps<{
    categories: { id: number; name: string; slug: string }[];
    storeInfo: Record<string, string | null>;
}>();
defineEmits<{ select: [slug: string] }>();
const whatsappHref = (): string =>
    `https://wa.me/${(props.storeInfo.whatsapp ?? '').replace(/\D/g, '')}`;
const instagramHref = (): string => {
    const value = props.storeInfo.instagram ?? '';

    return value.startsWith('https://')
        ? value
        : `https://instagram.com/${value.replace(/^@/, '')}`;
};
</script>
<template>
    <footer class="bg-[#392631] text-[#fffaf7]">
        <div
            class="mx-auto grid max-w-[1440px] gap-10 px-5 py-14 sm:px-8 md:grid-cols-3 lg:px-12 lg:py-20"
        >
            <div>
                <p class="text-3xl font-semibold tracking-[0.2em]">
                    NATÉ<span class="text-[#d9c1a2]">.</span>
                </p>
                <p class="mt-5 max-w-sm text-sm leading-7 text-white/60">
                    متجر جمال صُمم ليمنحكِ تجربة اكتشاف بسيطة، أنيقة وممتعة.
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold tracking-[0.18em] uppercase">
                    الأقسام
                </p>
                <div class="mt-4 space-y-2">
                    <button
                        v-for="category in categories.slice(0, 5)"
                        :key="category.id"
                        class="block text-sm text-white/65 transition hover:text-white"
                        @click="$emit('select', category.slug)"
                    >
                        {{ category.name }}
                    </button>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold tracking-[0.18em] uppercase">
                    معلومات
                </p>
                <a
                    href="#collection"
                    class="mt-4 block text-sm text-white/65 hover:text-white"
                    >جميع المنتجات</a
                >
                <a
                    href="/informations/a-propos"
                    class="mt-2 block text-sm text-white/65 hover:text-white"
                    >عن ناتي</a
                >
                <a
                    href="/informations/livraison"
                    class="mt-2 block text-sm text-white/65 hover:text-white"
                    >التوصيل والدفع</a
                >
                <a
                    href="/informations/faq"
                    class="mt-2 block text-sm text-white/65 hover:text-white"
                    >الأسئلة المتكررة</a
                >
                <a
                    href="/informations/conditions-generales"
                    class="mt-2 block text-sm text-white/65 hover:text-white"
                    >الشروط العامة</a
                >
                <a
                    href="/informations/confidentialite"
                    class="mt-2 block text-sm text-white/65 hover:text-white"
                    >سياسة الخصوصية</a
                >
                <a
                    href="/informations/retours"
                    class="mt-2 block text-sm text-white/65 hover:text-white"
                    >الاستبدال والاسترجاع</a
                >
                <a
                    href="/login"
                    class="mt-2 block text-sm text-white/65 hover:text-white"
                    >فضاء الإدارة</a
                >
                <div
                    v-if="
                        storeInfo.whatsapp ||
                        storeInfo.instagram ||
                        storeInfo.phone ||
                        storeInfo.email
                    "
                    class="mt-5 space-y-2 border-t border-white/10 pt-4"
                >
                    <a
                        v-if="storeInfo.whatsapp"
                        :href="whatsappHref()"
                        class="block text-sm text-white/65 hover:text-white"
                        >WhatsApp</a
                    >
                    <a
                        v-if="storeInfo.instagram"
                        :href="instagramHref()"
                        class="block text-sm text-white/65 hover:text-white"
                        >Instagram</a
                    >
                    <a
                        v-if="storeInfo.phone"
                        :href="`tel:${storeInfo.phone}`"
                        dir="ltr"
                        class="block text-right text-sm text-white/65 hover:text-white"
                        >{{ storeInfo.phone }}</a
                    >
                    <a
                        v-if="storeInfo.email"
                        :href="`mailto:${storeInfo.email}`"
                        dir="ltr"
                        class="block truncate text-right text-sm text-white/65 hover:text-white"
                        >{{ storeInfo.email }}</a
                    >
                </div>
            </div>
        </div>
        <div
            class="border-t border-white/10 px-5 py-5 text-center text-[11px] text-white/45"
        >
            © {{ new Date().getFullYear() }} NATÉ BOUTIQUE · جميع الحقوق محفوظة
        </div>
    </footer>
</template>
