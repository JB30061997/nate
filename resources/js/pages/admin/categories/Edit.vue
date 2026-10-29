<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import CategoryForm from '@/pages/admin/categories/CategoryForm.vue';
import { index } from '@/routes/admin/categories';

type Category = {
    id: number;
    name: string;
    description: string | null;
    image_url: string | null;
    is_active: boolean;
    sort_order: number;
};

defineProps<{ category: Category }>();
</script>

<template>
    <Head :title="`Modifier ${category.name}`" />
    <AdminPageHeader
        :title="`Modifier « ${category.name} »`"
        description="Mettez à jour les informations et la visibilité de cette catégorie."
        :back-href="index()"
    />
    <CategoryForm :category="category" />
</template>
