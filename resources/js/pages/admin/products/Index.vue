<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import {
    Image as ImageIcon,
    Package,
    Pencil,
    Search,
    Star,
    Trash2,
} from '@lucide/vue';
import { reactive, ref, watch } from 'vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import DeleteConfirmDialog from '@/components/admin/DeleteConfirmDialog.vue';
import { updateSliderVisibility } from '@/actions/App/Http/Controllers/Admin/ProductController';
import { create, destroy, edit, index } from '@/routes/admin/products';

type Option = { id: number; name: string; is_active: boolean };
type Product = {
    id: number;
    name: string;
    sku: string | null;
    price: string;
    compare_at_price: string | null;
    stock_quantity: number;
    is_active: boolean;
    is_featured: boolean;
    show_in_slider: boolean;
    category: { id: number; name: string };
    product_type: { id: number; name: string };
    main_image_url: string | null;
    images_count: number;
};
type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    products: {
        data: Product[];
        links: PaginationLink[];
    };
    filters: {
        search: string;
        category: string;
        product_type: string;
        status: string;
    };
    categories: Option[];
    productTypes: Option[];
    sliderSelectionEnabled: boolean;
}>();

const filters = reactive({ ...props.filters });
const productToDelete = ref<Product | null>(null);
const deleting = ref(false);
const sliderState = reactive<Record<number, boolean>>(
    Object.fromEntries(
        props.products.data.map((product) => [
            product.id,
            product.show_in_slider,
        ]),
    ),
);
const sliderUpdates = ref<Set<number>>(new Set());

watch(
    () => props.products.data,
    (products) => {
        products.forEach((product) => {
            if (!sliderUpdates.value.has(product.id)) {
                sliderState[product.id] = product.show_in_slider;
            }
        });
    },
);

const applyFilters = (): void => {
    router.get(
        index().url,
        {
            search: filters.search || undefined,
            category: filters.category || undefined,
            product_type: filters.product_type || undefined,
            status: filters.status || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const resetFilters = (): void => {
    filters.search = '';
    filters.category = '';
    filters.product_type = '';
    filters.status = '';
    applyFilters();
};

const confirmDelete = (): void => {
    if (!productToDelete.value) {
        return;
    }

    deleting.value = true;
    router.delete(destroy(productToDelete.value.id).url, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            productToDelete.value = null;
        },
    });
};

const formatPrice = (value: string): string =>
    new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD',
    }).format(Number(value));

const updateSlider = (product: Product, event: Event): void => {
    const checkbox = event.target as HTMLInputElement;
    const previousValue = sliderState[product.id];
    const nextValue = checkbox.checked;

    sliderState[product.id] = nextValue;
    sliderUpdates.value = new Set(sliderUpdates.value).add(product.id);

    router.patch(
        updateSliderVisibility(product.id).url,
        { show_in_slider: nextValue },
        {
            preserveScroll: true,
            preserveState: true,
            onError: () => {
                sliderState[product.id] = previousValue;
                toast.error(
                    'La sélection du slider n’a pas pu être mise à jour.',
                );
            },
            onFinish: () => {
                const pendingUpdates = new Set(sliderUpdates.value);
                pendingUpdates.delete(product.id);
                sliderUpdates.value = pendingUpdates;
            },
        },
    );
};
</script>

<template>
    <Head title="Produits" />

    <AdminPageHeader
        title="Produits"
        description="Gérez votre catalogue, vos prix, votre stock et vos visuels."
        action-label="Ajouter un produit"
        :action-href="create()"
    />

    <section
        class="rounded-3xl border border-[#eadfda] bg-white p-3 min-[375px]:p-4 sm:rounded-[2rem] sm:p-6"
    >
        <form
            class="grid gap-3 md:grid-cols-2 xl:grid-cols-5"
            @submit.prevent="applyFilters"
        >
            <label class="relative md:col-span-2 xl:col-span-2">
                <Search
                    class="pointer-events-none absolute top-1/2 left-4 size-4.5 -translate-y-1/2 text-[#9b878d]"
                />
                <input
                    v-model="filters.search"
                    type="search"
                    class="min-h-12 w-full rounded-2xl border border-[#e1d6d1] bg-[#fdfaf8] pr-4 pl-11 text-sm outline-none focus:border-[#ba7a89] focus:ring-4 focus:ring-[#f0dfe3]"
                    placeholder="Rechercher un produit…"
                />
            </label>
            <select
                v-model="filters.category"
                class="min-h-12 rounded-2xl border border-[#e1d6d1] bg-[#fdfaf8] px-4 text-sm outline-none focus:border-[#ba7a89]"
            >
                <option value="">Toutes les catégories</option>
                <option
                    v-for="category in categories"
                    :key="category.id"
                    :value="String(category.id)"
                >
                    {{ category.name }}
                </option>
            </select>
            <select
                v-model="filters.product_type"
                class="min-h-12 rounded-2xl border border-[#e1d6d1] bg-[#fdfaf8] px-4 text-sm outline-none focus:border-[#ba7a89]"
            >
                <option value="">Tous les types</option>
                <option
                    v-for="productType in productTypes"
                    :key="productType.id"
                    :value="String(productType.id)"
                >
                    {{ productType.name }}
                </option>
            </select>
            <select
                v-model="filters.status"
                class="min-h-12 rounded-2xl border border-[#e1d6d1] bg-[#fdfaf8] px-4 text-sm outline-none focus:border-[#ba7a89]"
            >
                <option value="">Tous les statuts</option>
                <option value="active">Actifs</option>
                <option value="inactive">Inactifs</option>
            </select>
            <div
                class="flex min-w-0 flex-col gap-2 min-[360px]:flex-row md:col-span-2 xl:col-span-5 xl:justify-end"
            >
                <button
                    type="button"
                    class="min-h-12 w-full rounded-xl border border-[#e5d9d5] px-4 text-sm font-medium text-[#78686d] min-[360px]:w-auto"
                    @click="resetFilters"
                >
                    Réinitialiser
                </button>
                <button
                    type="submit"
                    class="min-h-12 flex-1 rounded-xl bg-[#4b3c41] px-5 text-sm font-semibold text-white xl:flex-none"
                >
                    Filtrer
                </button>
            </div>
        </form>
    </section>

    <section
        v-if="products.data.length === 0"
        class="mt-5 rounded-[2rem] border border-dashed border-[#ddced1] bg-white px-6 py-16 text-center"
    >
        <Package class="mx-auto size-10 text-[#c18b97]" />
        <h2 class="mt-4 font-serif text-2xl font-semibold text-[#493b40]">
            Aucun produit
        </h2>
        <p class="mt-2 text-sm text-[#8d7a80]">
            Ajoutez votre premier produit ou modifiez les filtres.
        </p>
    </section>

    <template v-else>
        <div
            v-if="!sliderSelectionEnabled"
            class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900"
            role="status"
        >
            La sélection du slider sera disponible après l’exécution de la
            migration <code>show_in_slider</code>.
        </div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:hidden">
            <article
                v-for="product in products.data"
                :key="product.id"
                class="min-w-0 overflow-hidden rounded-3xl border border-[#eadfda] bg-white"
            >
                <div
                    class="flex min-w-0 gap-3 p-3 min-[375px]:gap-4 min-[375px]:p-4"
                >
                    <div
                        class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-2xl bg-[#f6eeeb] text-[#b27a86] min-[375px]:size-24"
                    >
                        <img
                            v-if="product.main_image_url"
                            :src="product.main_image_url"
                            :alt="product.name"
                            class="h-full w-full object-contain p-1"
                        />
                        <ImageIcon v-else class="size-6" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start gap-2">
                            <h2
                                class="line-clamp-2 flex-1 font-semibold text-[#46393d]"
                            >
                                {{ product.name }}
                            </h2>
                            <Star
                                v-if="product.is_featured"
                                class="size-4 shrink-0 fill-[#c48a4c] text-[#c48a4c]"
                            />
                        </div>
                        <p class="mt-1 text-xs text-[#97848a]">
                            {{ product.category.name }} ·
                            {{ product.product_type.name }}
                        </p>
                        <p class="mt-3 font-semibold text-[#8f5361]">
                            {{ formatPrice(product.price) }}
                        </p>
                        <p
                            class="mt-1 text-xs"
                            :class="
                                product.stock_quantity > 0
                                    ? 'text-[#6d7c62]'
                                    : 'text-[#a65353]'
                            "
                        >
                            Stock : {{ product.stock_quantity }}
                        </p>
                        <label
                            class="mt-3 inline-flex min-h-10 cursor-pointer items-center gap-2 text-xs font-semibold text-[#66565c]"
                        >
                            <input
                                :checked="sliderState[product.id]"
                                type="checkbox"
                                class="size-5 rounded-md border-[#bda8af] text-[#9f5d70] accent-[#9f5d70] focus:ring-[#d8aeb9] disabled:cursor-wait disabled:opacity-60"
                                :disabled="
                                    !sliderSelectionEnabled ||
                                    sliderUpdates.has(product.id)
                                "
                                @change="updateSlider(product, $event)"
                            />
                            Afficher dans le slider
                        </label>
                    </div>
                </div>
                <div
                    class="flex flex-wrap items-center justify-between gap-2 border-t border-[#f0e7e3] px-3 py-3 min-[375px]:px-4"
                >
                    <span
                        class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                        :class="
                            product.is_active
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'bg-[#f2ece9] text-[#87777c]'
                        "
                    >
                        {{ product.is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    <div class="flex gap-2">
                        <Link
                            :href="edit(product.id)"
                            class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#f8e9ec] px-3 text-sm font-semibold text-[#955a68]"
                        >
                            <Pencil class="size-4" /> Modifier
                        </Link>
                        <button
                            type="button"
                            class="grid size-11 place-items-center rounded-xl border border-[#eddcda] text-[#a85c5c]"
                            aria-label="Supprimer"
                            @click="productToDelete = product"
                        >
                            <Trash2 class="size-4" />
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <div
            class="mt-5 hidden overflow-hidden rounded-[2rem] border border-[#eadfda] bg-white xl:block"
        >
            <table class="w-full text-left">
                <thead
                    class="bg-[#faf5f2] text-xs tracking-wide text-[#8d7a80] uppercase"
                >
                    <tr>
                        <th class="px-5 py-4 font-semibold">Produit</th>
                        <th class="px-5 py-4 font-semibold">Classement</th>
                        <th class="px-5 py-4 font-semibold">Prix</th>
                        <th class="px-5 py-4 font-semibold">Stock</th>
                        <th class="px-5 py-4 font-semibold">Statut</th>
                        <th class="px-5 py-4 text-center font-semibold">
                            Afficher dans le slider
                        </th>
                        <th class="px-5 py-4 text-right font-semibold">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0e7e3]">
                    <tr
                        v-for="product in products.data"
                        :key="product.id"
                        class="hover:bg-[#fffaf9]"
                    >
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-[#f6eeeb] text-[#b27a86]"
                                >
                                    <img
                                        v-if="product.main_image_url"
                                        :src="product.main_image_url"
                                        :alt="product.name"
                                        class="h-full w-full object-contain p-1"
                                    />
                                    <ImageIcon v-else class="size-4.5" />
                                </div>
                                <div class="min-w-0">
                                    <p
                                        class="flex items-center gap-1.5 font-semibold text-[#493b40]"
                                    >
                                        <span class="max-w-64 truncate">{{
                                            product.name
                                        }}</span>
                                        <Star
                                            v-if="product.is_featured"
                                            class="size-3.5 fill-[#c48a4c] text-[#c48a4c]"
                                        />
                                    </p>
                                    <p class="mt-0.5 text-xs text-[#9a878c]">
                                        {{ product.sku || 'Sans SKU' }} ·
                                        {{ product.images_count }} image(s)
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-[#6e6065]">
                            <p>{{ product.category.name }}</p>
                            <p class="mt-1 text-xs text-[#9a878c]">
                                {{ product.product_type.name }}
                            </p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-[#8f5361]">
                                {{ formatPrice(product.price) }}
                            </p>
                            <p
                                v-if="product.compare_at_price"
                                class="mt-1 text-xs text-[#a6959a] line-through"
                            >
                                {{ formatPrice(product.compare_at_price) }}
                            </p>
                        </td>
                        <td class="px-5 py-4 text-sm">
                            <span
                                :class="
                                    product.stock_quantity > 0
                                        ? 'text-[#647258]'
                                        : 'font-semibold text-[#a65353]'
                                "
                            >
                                {{ product.stock_quantity }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span
                                class="rounded-full px-3 py-1.5 text-xs font-semibold"
                                :class="
                                    product.is_active
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-[#f2ece9] text-[#87777c]'
                                "
                            >
                                {{ product.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <label
                                class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-xl px-4 transition hover:bg-[#f8edef]"
                                :aria-label="`Afficher ${product.name} dans le slider`"
                            >
                                <input
                                    :checked="sliderState[product.id]"
                                    type="checkbox"
                                    class="size-5 rounded-md border-[#bda8af] text-[#9f5d70] accent-[#9f5d70] focus:ring-[#d8aeb9] disabled:cursor-wait disabled:opacity-60"
                                    :disabled="
                                        !sliderSelectionEnabled ||
                                        sliderUpdates.has(product.id)
                                    "
                                    @change="updateSlider(product, $event)"
                                />
                                <span class="sr-only"
                                    >Afficher dans le slider</span
                                >
                            </label>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <Link
                                    :href="edit(product.id)"
                                    class="grid size-9 place-items-center rounded-xl bg-[#f8e9ec] text-[#955a68]"
                                    aria-label="Modifier"
                                >
                                    <Pencil class="size-4" />
                                </Link>
                                <button
                                    type="button"
                                    class="grid size-9 place-items-center rounded-xl bg-[#faeeee] text-[#a85c5c]"
                                    aria-label="Supprimer"
                                    @click="productToDelete = product"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <AdminPagination :links="products.links" />
    </template>

    <DeleteConfirmDialog
        :open="productToDelete !== null"
        title="Supprimer ce produit ?"
        :description="`Le produit « ${productToDelete?.name ?? ''} » sera retiré du catalogue. Ses données seront conservées grâce à la suppression douce.`"
        :processing="deleting"
        @cancel="productToDelete = null"
        @confirm="confirmDelete"
    />
</template>
