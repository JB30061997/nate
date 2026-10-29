<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import ProductTypeForm from '@/pages/admin/product-types/ProductTypeForm.vue';
import { index } from '@/routes/admin/product-types';

type ProductType = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
};

defineProps<{ productType: ProductType }>();
</script>

<template>
    <Head :title="`Modifier ${productType.name}`" />
    <AdminPageHeader
        :title="`Modifier « ${productType.name} »`"
        description="Mettez à jour ce type de produit et sa disponibilité."
        :back-href="index()"
    />
    <ProductTypeForm :product-type="productType" />
</template>
