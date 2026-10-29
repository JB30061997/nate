<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import ProductForm from '@/pages/admin/products/ProductForm.vue';
import { index } from '@/routes/admin/products';

type Option = { id: number; name: string; is_active: boolean };
type ProductOption = { id: number; name: string; stock_quantity: number };

defineProps<{
    categories: Option[];
    productTypes: Option[];
    productOptions: ProductOption[];
}>();
</script>

<template>
    <Head title="Nouveau produit" />
    <AdminPageHeader
        title="Nouveau produit"
        description="Ajoutez un produit, son prix, son stock et ses visuels."
        :back-href="index()"
    />
    <ProductForm
        :categories="categories"
        :product-types="productTypes"
        :product-options="productOptions"
    />
</template>
