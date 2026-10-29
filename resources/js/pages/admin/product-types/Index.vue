<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Search, Tags, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import DeleteConfirmDialog from '@/components/admin/DeleteConfirmDialog.vue';
import {
    create,
    destroy,
    edit,
    index,
    toggle,
} from '@/routes/admin/product-types';

type ProductType = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    products_count: number;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    productTypes: {
        data: ProductType[];
        links: PaginationLink[];
    };
    filters: { search: string };
}>();

const search = ref(props.filters.search);
const selectedType = ref<ProductType | null>(null);
const deleting = ref(false);

const applySearch = (): void => {
    router.get(
        index().url,
        { search: search.value || undefined },
        { preserveState: true, replace: true },
    );
};

const confirmDelete = (): void => {
    if (!selectedType.value) {
        return;
    }

    deleting.value = true;
    router.delete(destroy(selectedType.value.id).url, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            selectedType.value = null;
        },
    });
};
</script>

<template>
    <Head title="Types de produits" />

    <AdminPageHeader
        title="Types de produits"
        description="Classez vos produits par texture, usage ou format."
        action-label="Ajouter un type"
        :action-href="create()"
    />

    <section
        class="rounded-3xl border border-[#eadfda] bg-white p-3 min-[375px]:p-4 sm:rounded-[2rem] sm:p-6"
    >
        <form
            class="flex flex-col gap-2 sm:flex-row"
            @submit.prevent="applySearch"
        >
            <label class="relative min-w-0 flex-1">
                <Search
                    class="pointer-events-none absolute top-1/2 left-4 size-4.5 -translate-y-1/2 text-[#9b878d]"
                />
                <input
                    v-model="search"
                    type="search"
                    class="min-h-12 w-full rounded-2xl border border-[#e1d6d1] bg-[#fdfaf8] pr-4 pl-11 text-sm outline-none focus:border-[#ba7a89] focus:ring-4 focus:ring-[#f0dfe3]"
                    placeholder="Rechercher un type…"
                />
            </label>
            <button
                type="submit"
                class="min-h-12 w-full rounded-2xl bg-[#4b3c41] px-5 text-sm font-semibold text-white hover:bg-[#3d3034] sm:w-auto"
            >
                Rechercher
            </button>
        </form>
    </section>

    <section
        v-if="productTypes.data.length === 0"
        class="mt-5 rounded-[2rem] border border-dashed border-[#ddced1] bg-white px-6 py-16 text-center"
    >
        <Tags class="mx-auto size-10 text-[#c18b97]" />
        <h2 class="mt-4 font-serif text-2xl font-semibold text-[#493b40]">
            Aucun type de produit
        </h2>
        <p class="mt-2 text-sm text-[#8d7a80]">
            Ajoutez un type pour compléter votre catalogue.
        </p>
    </section>

    <template v-else>
        <div class="mt-5 grid gap-3 md:hidden">
            <article
                v-for="productType in productTypes.data"
                :key="productType.id"
                class="min-w-0 overflow-hidden rounded-3xl border border-[#eadfda] bg-white p-4"
            >
                <div class="flex items-start gap-3">
                    <span
                        class="grid size-11 shrink-0 place-items-center rounded-2xl bg-[#f6e8eb] text-[#a96675]"
                    >
                        <Tags class="size-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <div
                            class="flex flex-col items-start gap-2 min-[380px]:flex-row min-[380px]:justify-between"
                        >
                            <div class="min-w-0">
                                <h2 class="font-semibold text-[#46393d]">
                                    {{ productType.name }}
                                </h2>
                                <p class="mt-1 text-xs text-[#97848a]">
                                    {{ productType.products_count }} produit(s)
                                </p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 rounded-full px-2.5 py-1.5 text-[11px] font-semibold"
                                :class="
                                    productType.is_active
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-[#f2ece9] text-[#87777c]'
                                "
                                @click="
                                    router.patch(
                                        toggle(productType.id).url,
                                        {},
                                        { preserveScroll: true },
                                    )
                                "
                            >
                                {{
                                    productType.is_active ? 'Actif' : 'Inactif'
                                }}
                            </button>
                        </div>
                        <p
                            v-if="productType.description"
                            class="mt-3 line-clamp-2 text-sm leading-5 text-[#7d6d72]"
                        >
                            {{ productType.description }}
                        </p>
                        <div class="mt-4 flex gap-2">
                            <Link
                                :href="edit(productType.id)"
                                class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-[#f8e9ec] text-sm font-semibold text-[#955a68]"
                            >
                                <Pencil class="size-4" /> Modifier
                            </Link>
                            <button
                                type="button"
                                class="grid size-10 place-items-center rounded-xl border border-[#eddcda] text-[#a85c5c]"
                                aria-label="Supprimer"
                                @click="selectedType = productType"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <div
            class="mt-5 hidden overflow-hidden rounded-[2rem] border border-[#eadfda] bg-white md:block"
        >
            <table class="w-full text-left">
                <thead
                    class="bg-[#faf5f2] text-xs tracking-wide text-[#8d7a80] uppercase"
                >
                    <tr>
                        <th class="px-5 py-4 font-semibold">Type</th>
                        <th class="px-5 py-4 font-semibold">Produits</th>
                        <th class="px-5 py-4 font-semibold">Statut</th>
                        <th class="px-5 py-4 text-right font-semibold">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0e7e3]">
                    <tr
                        v-for="productType in productTypes.data"
                        :key="productType.id"
                        class="hover:bg-[#fffaf9]"
                    >
                        <td class="px-5 py-4">
                            <p class="font-semibold text-[#493b40]">
                                {{ productType.name }}
                            </p>
                            <p class="mt-0.5 text-xs text-[#9a878c]">
                                {{ productType.slug }}
                            </p>
                        </td>
                        <td class="px-5 py-4 text-sm text-[#6e6065]">
                            {{ productType.products_count }}
                        </td>
                        <td class="px-5 py-4">
                            <button
                                type="button"
                                class="rounded-full px-3 py-1.5 text-xs font-semibold"
                                :class="
                                    productType.is_active
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-[#f2ece9] text-[#87777c]'
                                "
                                @click="
                                    router.patch(
                                        toggle(productType.id).url,
                                        {},
                                        { preserveScroll: true },
                                    )
                                "
                            >
                                {{
                                    productType.is_active ? 'Actif' : 'Inactif'
                                }}
                            </button>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <Link
                                    :href="edit(productType.id)"
                                    class="grid size-9 place-items-center rounded-xl bg-[#f8e9ec] text-[#955a68]"
                                    aria-label="Modifier"
                                >
                                    <Pencil class="size-4" />
                                </Link>
                                <button
                                    type="button"
                                    class="grid size-9 place-items-center rounded-xl bg-[#faeeee] text-[#a85c5c]"
                                    aria-label="Supprimer"
                                    @click="selectedType = productType"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <AdminPagination :links="productTypes.links" />
    </template>

    <DeleteConfirmDialog
        :open="selectedType !== null"
        title="Supprimer ce type ?"
        :description="`Le type « ${selectedType?.name ?? ''} » sera supprimé s’il n’est associé à aucun produit.`"
        :processing="deleting"
        @cancel="selectedType = null"
        @confirm="confirmDelete"
    />
</template>
