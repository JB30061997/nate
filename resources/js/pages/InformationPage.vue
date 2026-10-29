<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
defineProps<{ title: string; content: string | null }>();
</script>
<template>
    <div
        lang="ar"
        dir="rtl"
        class="min-h-screen bg-[#fffbf8] px-4 py-10 text-[#392631] sm:px-8 sm:py-16"
    >
        <Head :title="title" />
        <main class="mx-auto max-w-3xl">
            <Link href="/" class="text-sm font-semibold text-[#9a5870]"
                >العودة إلى المتجر</Link
            >
            <h1 class="store-display mt-8 text-4xl sm:text-6xl">{{ title }}</h1>
            <div
                v-if="content"
                class="mt-8 text-sm leading-8 whitespace-pre-line text-[#5f4b54] sm:text-base"
            >
                {{ content }}
            </div>
            <p
                v-else
                class="mt-8 rounded-2xl border border-[#eadfe1] p-5 text-sm text-[#7c6870]"
            >
                هذه المعلومات غير متوفرة حالياً.
            </p>
        </main>
    </div>
</template>
