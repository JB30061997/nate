<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Save } from '@lucide/vue';
import { store, update } from '@/routes/admin/product-types';

type ProductType = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
};

const props = defineProps<{ productType?: ProductType }>();

const form = useForm({
    name: props.productType?.name ?? '',
    description: props.productType?.description ?? '',
    is_active: props.productType?.is_active ?? true,
});

const submit = (): void => {
    if (props.productType) {
        form.put(update(props.productType.id).url, { preserveScroll: true });
        return;
    }

    form.post(store().url, { preserveScroll: true });
};
</script>

<template>
    <form class="mx-auto max-w-3xl space-y-5" @submit.prevent="submit">
        <section
            class="rounded-3xl border border-[#eadfda] bg-white p-4 min-[375px]:p-5 sm:rounded-[2rem] sm:p-7"
        >
            <h2 class="font-serif text-xl font-semibold text-[#44373b]">
                Informations du type
            </h2>
            <div class="mt-5 space-y-5">
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-[#594b50]"
                        >Nom</span
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                        placeholder="Ex. Sérum"
                    />
                    <span
                        v-if="form.errors.name"
                        class="mt-1.5 block text-xs text-red-600"
                    >
                        {{ form.errors.name }}
                    </span>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-[#594b50]"
                        >Description</span
                    >
                    <textarea
                        v-model="form.description"
                        rows="5"
                        class="w-full resize-y rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 py-3 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                        placeholder="Décrivez ce type de produit"
                    ></textarea>
                </label>

                <label
                    class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl bg-[#faf6f3] p-4"
                >
                    <span>
                        <span class="block text-sm font-semibold text-[#514348]"
                            >Type actif</span
                        >
                        <span class="mt-1 block text-xs text-[#948187]">
                            Disponible lors de la création des produits.
                        </span>
                    </span>
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="size-5 accent-[#ad6878]"
                    />
                </label>
            </div>
        </section>

        <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-[#a96171] px-5 text-sm font-semibold text-white hover:bg-[#955464] disabled:opacity-50 sm:w-auto sm:min-w-44"
        >
            <Save class="size-4.5" />
            {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
        </button>
    </form>
</template>
