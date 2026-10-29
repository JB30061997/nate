<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import ProductTypeForm from '@/pages/admin/product-types/ProductTypeForm.vue';
import { index } from '@/routes/admin/product-types';
</script>

<template>
    <Head title="Nouveau type de produit" />
    <AdminPageHeader
        title="Nouveau type"
        description="Créez une typologie claire pour faciliter la gestion du catalogue."
        :back-href="index()"
    />
    <ProductTypeForm />
</template>
