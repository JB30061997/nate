<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import ProductForm from '@/pages/admin/products/ProductForm.vue';
import { index } from '@/routes/admin/products';

type Option = { id: number; name: string; is_active: boolean };
type ProductOption = { id: number; name: string; stock_quantity: number };
type ProductImage = {
    id: number;
    image_path: string;
    sort_order: number;
    url: string;
    is_main: boolean;
};
type Product = {
    id: number;
    category_id: number;
    product_type_id: number;
    name: string;
    sku: string | null;
    description: string | null;
    usage_instructions: string | null;
    ingredients: string | null;
    precautions: string | null;
    price: string;
    compare_at_price: string | null;
    stock_quantity: number;
    is_active: boolean;
    is_featured: boolean;
    is_pack: boolean;
    pack_items: { product_id: number; quantity: number }[];
    main_image: string | null;
    main_image_url: string | null;
    main_image_preview_url: string | null;
    images: ProductImage[];
};

defineProps<{
    product: Product;
    categories: Option[];
    productTypes: Option[];
    productOptions: ProductOption[];
}>();
</script>

<template>
    <Head :title="`Modifier ${product.name}`" />
    <AdminPageHeader
        :title="`Modifier « ${product.name} »`"
        description="Mettez à jour le produit, son stock et ses images."
        :back-href="index()"
    />
    <ProductForm
        :product="product"
        :categories="categories"
        :product-types="productTypes"
        :product-options="productOptions"
    />
</template>
