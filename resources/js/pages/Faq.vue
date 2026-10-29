<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
defineProps<{ faqs: { id: number; question: string; answer: string }[] }>();
</script>
<template>
    <div
        lang="ar"
        dir="rtl"
        class="min-h-screen bg-[#fffbf8] px-4 py-10 text-[#392631] sm:px-8 sm:py-16"
    >
        <Head title="الأسئلة المتكررة" />
        <main class="mx-auto max-w-3xl">
            <Link href="/" class="text-sm font-semibold text-[#9a5870]"
                >العودة إلى المتجر</Link
            >
            <h1 class="store-display mt-8 text-4xl sm:text-6xl">
                الأسئلة المتكررة
            </h1>
            <div
                v-if="faqs.length"
                class="mt-8 divide-y divide-[#eadfe1] border-y border-[#eadfe1]"
            >
                <details v-for="faq in faqs" :key="faq.id" class="group py-5">
                    <summary
                        class="cursor-pointer list-none text-base font-bold"
                    >
                        {{ faq.question }}
                    </summary>
                    <p
                        class="mt-3 text-sm leading-7 whitespace-pre-line text-[#6d5961]"
                    >
                        {{ faq.answer }}
                    </p>
                </details>
            </div>
            <p v-else class="mt-8 text-sm text-[#7c6870]">
                لا توجد أسئلة منشورة حالياً.
            </p>
        </main>
    </div>
</template>
