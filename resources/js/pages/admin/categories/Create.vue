<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import CategoryForm from '@/pages/admin/categories/CategoryForm.vue';
import { index } from '@/routes/admin/categories';
</script>

<template>
    <Head title="Nouvelle catégorie" />
    <AdminPageHeader
        title="Nouvelle catégorie"
        description="Organisez votre catalogue avec une catégorie claire et soignée."
        :back-href="index()"
    />
    <CategoryForm />
</template>
