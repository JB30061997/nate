<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ImagePlus, Save, X } from '@lucide/vue';
import { onBeforeUnmount, ref } from 'vue';
import { store, update } from '@/routes/admin/categories';

type Category = {
    id: number;
    name: string;
    description: string | null;
    image_url: string | null;
    is_active: boolean;
    sort_order: number;
};

const props = defineProps<{
    category?: Category;
}>();

const previewUrl = ref<string | null>(null);
const form = useForm({
    _method: props.category ? 'put' : 'post',
    name: props.category?.name ?? '',
    description: props.category?.description ?? '',
    image: null as File | null,
    remove_image: false,
    is_active: props.category?.is_active ?? true,
    sort_order: props.category?.sort_order ?? 0,
});

const selectImage = (event: Event): void => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    form.image = file;
    previewUrl.value = file ? URL.createObjectURL(file) : null;
    form.remove_image = false;
};

const removeImage = (): void => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = null;
    form.image = null;
    form.remove_image = true;
};

const submit = (): void => {
    const url = props.category ? update(props.category.id).url : store().url;

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
    });
};

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});
</script>

<template>
    <form
        class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]"
        enctype="multipart/form-data"
        @submit.prevent="submit"
    >
        <div class="space-y-5">
            <section
                class="rounded-3xl border border-[#eadfda] bg-white p-4 min-[375px]:p-5 sm:rounded-[2rem] sm:p-7"
            >
                <h2 class="font-serif text-xl font-semibold text-[#44373b]">
                    Informations
                </h2>
                <div class="mt-5 space-y-5">
                    <label class="block">
                        <span
                            class="mb-2 block text-sm font-medium text-[#594b50]"
                            >Nom</span
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm transition outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                            placeholder="Ex. Soins du visage"
                        />
                        <span
                            v-if="form.errors.name"
                            class="mt-1.5 block text-xs text-red-600"
                        >
                            {{ form.errors.name }}
                        </span>
                    </label>

                    <label class="block">
                        <span
                            class="mb-2 block text-sm font-medium text-[#594b50]"
                            >Description</span
                        >
                        <textarea
                            v-model="form.description"
                            rows="5"
                            class="w-full resize-y rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 py-3 text-sm transition outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                            placeholder="Décrivez brièvement cette catégorie"
                        ></textarea>
                        <span
                            v-if="form.errors.description"
                            class="mt-1.5 block text-xs text-red-600"
                        >
                            {{ form.errors.description }}
                        </span>
                    </label>

                    <label class="block max-w-xs">
                        <span
                            class="mb-2 block text-sm font-medium text-[#594b50]"
                            >Ordre d’affichage</span
                        >
                        <input
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            required
                            class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                        />
                    </label>
                </div>
            </section>
        </div>

        <aside class="space-y-5">
            <section
                class="rounded-3xl border border-[#eadfda] bg-white p-4 min-[375px]:p-5 sm:rounded-[2rem]"
            >
                <h2 class="font-serif text-xl font-semibold text-[#44373b]">
                    Image
                </h2>
                <div class="mt-4">
                    <div
                        v-if="
                            previewUrl ||
                            (category?.image_url && !form.remove_image)
                        "
                        class="relative aspect-square overflow-hidden rounded-2xl bg-[#f7f1ee]"
                    >
                        <img
                            :src="previewUrl ?? category?.image_url ?? ''"
                            alt="Aperçu de la catégorie"
                            class="h-full w-full object-cover"
                        />
                        <button
                            type="button"
                            class="absolute top-2 right-2 grid size-9 place-items-center rounded-full bg-white/90 text-[#925365] shadow"
                            aria-label="Retirer l’image"
                            @click="removeImage"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                    <label
                        v-else
                        class="flex aspect-square cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#dcc9ce] bg-[#fcf8f6] text-center transition hover:border-[#bb7c8a] hover:bg-[#fff5f7]"
                    >
                        <ImagePlus class="size-7 text-[#b37281]" />
                        <span class="mt-3 text-sm font-medium text-[#67565c]"
                            >Choisir une image</span
                        >
                        <span class="mt-1 px-4 text-xs text-[#9a878c]"
                            >JPG, PNG ou WebP · 2 Mo max.</span
                        >
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="sr-only"
                            @change="selectImage"
                        />
                    </label>
                    <label
                        v-if="
                            previewUrl ||
                            (category?.image_url && !form.remove_image)
                        "
                        class="mt-3 flex min-h-10 cursor-pointer items-center justify-center rounded-xl border border-[#e5d7d3] text-xs font-semibold text-[#826c72] hover:bg-[#faf4f3]"
                    >
                        Remplacer l’image
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="sr-only"
                            @change="selectImage"
                        />
                    </label>
                    <span
                        v-if="form.errors.image"
                        class="mt-2 block text-xs text-red-600"
                    >
                        {{ form.errors.image }}
                    </span>
                </div>
            </section>

            <section
                class="rounded-3xl border border-[#eadfda] bg-white p-4 min-[375px]:p-5 sm:rounded-[2rem]"
            >
                <label
                    class="flex cursor-pointer items-center justify-between gap-4"
                >
                    <span>
                        <span class="block text-sm font-semibold text-[#514348]"
                            >Catégorie active</span
                        >
                        <span
                            class="mt-1 block text-xs leading-5 text-[#948187]"
                        >
                            Visible et disponible pour les produits.
                        </span>
                    </span>
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="size-5 accent-[#ad6878]"
                    />
                </label>
            </section>

            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-[#a96171] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#955464] disabled:opacity-50"
            >
                <Save class="size-4.5" />
                {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
            </button>
        </aside>
    </form>
</template>
