<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    Crown,
    ImagePlus,
    Plus,
    Save,
    Trash2,
    X,
} from '@lucide/vue';
import { onBeforeUnmount, ref, watch } from 'vue';
import DeleteConfirmDialog from '@/components/admin/DeleteConfirmDialog.vue';
import { store, update } from '@/routes/admin/products';
import {
    destroy as destroyImage,
    main,
    reorder,
} from '@/routes/admin/products/images';

type Option = {
    id: number;
    name: string;
    is_active: boolean;
};
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

const props = defineProps<{
    product?: Product;
    categories: Option[];
    productTypes: Option[];
    productOptions: ProductOption[];
}>();

const imagePreviews = ref<string[]>([]);
const orderedImages = ref<ProductImage[]>([...(props.product?.images ?? [])]);
const imageToDelete = ref<ProductImage | null>(null);
const deletingImage = ref(false);

const form = useForm({
    _method: props.product ? 'put' : 'post',
    category_id: props.product?.category_id ?? '',
    product_type_id: props.product?.product_type_id ?? '',
    name: props.product?.name ?? '',
    sku: props.product?.sku ?? '',
    description: props.product?.description ?? '',
    usage_instructions: props.product?.usage_instructions ?? '',
    ingredients: props.product?.ingredients ?? '',
    precautions: props.product?.precautions ?? '',
    price: props.product?.price ?? '',
    compare_at_price: props.product?.compare_at_price ?? '',
    stock_quantity: props.product?.stock_quantity ?? 0,
    is_active: props.product?.is_active ?? true,
    is_featured: props.product?.is_featured ?? false,
    is_pack: props.product?.is_pack ?? false,
    pack_items: [...(props.product?.pack_items ?? [])],
    main_image_url: props.product?.main_image_url ?? '',
    images: [] as File[],
});

const addPackItem = (): void => {
    form.pack_items.push({ product_id: 0, quantity: 1 });
};

const removePackItem = (index: number): void => {
    form.pack_items.splice(index, 1);
};

watch(
    () => props.product?.images,
    (images) => {
        orderedImages.value = [...(images ?? [])];
    },
);

const selectImages = (event: Event): void => {
    imagePreviews.value.forEach((url) => URL.revokeObjectURL(url));

    const files = Array.from(
        (event.target as HTMLInputElement).files ?? [],
    ).slice(0, 8);

    form.images = files;
    imagePreviews.value = files.map((file) => URL.createObjectURL(file));
};

const clearSelectedImages = (): void => {
    imagePreviews.value.forEach((url) => URL.revokeObjectURL(url));
    imagePreviews.value = [];
    form.images = [];
};

const submit = (): void => {
    const url = props.product ? update(props.product.id).url : store().url;

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: clearSelectedImages,
    });
};

const setMainImage = (image: ProductImage): void => {
    if (!props.product) {
        return;
    }

    router.patch(
        main({ product: props.product.id, productImage: image.id }).url,
        {},
        { preserveScroll: true },
    );
};

const moveImage = (index: number, direction: -1 | 1): void => {
    if (!props.product) {
        return;
    }

    const target = index + direction;

    if (target < 0 || target >= orderedImages.value.length) {
        return;
    }

    const images = [...orderedImages.value];
    [images[index], images[target]] = [images[target], images[index]];
    orderedImages.value = images;

    router.patch(
        reorder(props.product.id).url,
        { image_ids: images.map((image) => image.id) },
        { preserveScroll: true },
    );
};

const confirmImageDeletion = (): void => {
    if (!props.product || !imageToDelete.value) {
        return;
    }

    deletingImage.value = true;
    router.delete(
        destroyImage({
            product: props.product.id,
            productImage: imageToDelete.value.id,
        }).url,
        {
            preserveScroll: true,
            onFinish: () => {
                deletingImage.value = false;
                imageToDelete.value = null;
            },
        },
    );
};

onBeforeUnmount(clearSelectedImages);
</script>

<template>
    <div>
        <form
            class="space-y-5"
            enctype="multipart/form-data"
            @submit.prevent="submit"
        >
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
                <div class="space-y-5">
                    <section
                        class="rounded-3xl border border-[#eadfda] bg-white p-4 min-[375px]:p-5 sm:rounded-[2rem] sm:p-7"
                    >
                        <h2
                            class="font-serif text-xl font-semibold text-[#44373b]"
                        >
                            Informations générales
                        </h2>
                        <div class="mt-5 grid gap-5 sm:grid-cols-2">
                            <label class="block sm:col-span-2">
                                <span
                                    class="mb-2 block text-sm font-medium text-[#594b50]"
                                    >Nom du produit</span
                                >
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    autofocus
                                    class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                                    placeholder="Ex. Sérum hydratant"
                                />
                                <span
                                    v-if="form.errors.name"
                                    class="mt-1.5 block text-xs text-red-600"
                                    >{{ form.errors.name }}</span
                                >
                            </label>

                            <label class="block">
                                <span
                                    class="mb-2 block text-sm font-medium text-[#594b50]"
                                    >Catégorie</span
                                >
                                <select
                                    v-model="form.category_id"
                                    required
                                    class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                                >
                                    <option value="" disabled>
                                        Sélectionner une catégorie
                                    </option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.id"
                                    >
                                        {{ category.name
                                        }}{{
                                            category.is_active
                                                ? ''
                                                : ' (inactive)'
                                        }}
                                    </option>
                                </select>
                                <span
                                    v-if="form.errors.category_id"
                                    class="mt-1.5 block text-xs text-red-600"
                                    >{{ form.errors.category_id }}</span
                                >
                            </label>

                            <label class="block">
                                <span
                                    class="mb-2 block text-sm font-medium text-[#594b50]"
                                    >Type de produit</span
                                >
                                <select
                                    v-model="form.product_type_id"
                                    required
                                    class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                                >
                                    <option value="" disabled>
                                        Sélectionner un type
                                    </option>
                                    <option
                                        v-for="productType in productTypes"
                                        :key="productType.id"
                                        :value="productType.id"
                                    >
                                        {{ productType.name
                                        }}{{
                                            productType.is_active
                                                ? ''
                                                : ' (inactif)'
                                        }}
                                    </option>
                                </select>
                            </label>

                            <label class="block sm:col-span-2">
                                <span
                                    class="mb-2 block text-sm font-medium text-[#594b50]"
                                    >Description</span
                                >
                                <textarea
                                    v-model="form.description"
                                    rows="6"
                                    class="w-full resize-y rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 py-3 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                                    placeholder="Décrivez les bénéfices et l’utilisation du produit"
                                ></textarea>
                            </label>
                            <label
                                v-for="field in [
                                    [
                                        'usage_instructions',
                                        'Conseils d’utilisation',
                                    ],
                                    ['ingredients', 'Composition'],
                                    ['precautions', 'Précautions'],
                                ] as const"
                                :key="field[0]"
                                class="block sm:col-span-2"
                            >
                                <span
                                    class="mb-2 block text-sm font-medium text-[#594b50]"
                                    >{{ field[1] }}</span
                                >
                                <textarea
                                    v-model="form[field[0]]"
                                    rows="3"
                                    class="w-full resize-y rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 py-3 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                                    placeholder="Optionnel — saisir uniquement des informations vérifiées"
                                />
                            </label>
                        </div>
                    </section>

                    <section
                        class="rounded-3xl border border-[#eadfda] bg-white p-4 min-[375px]:p-5 sm:rounded-[2rem] sm:p-7"
                    >
                        <h2
                            class="font-serif text-xl font-semibold text-[#44373b]"
                        >
                            Prix et stock
                        </h2>
                        <div class="mt-5 grid gap-5 sm:grid-cols-2">
                            <label class="block">
                                <span
                                    class="mb-2 block text-sm font-medium text-[#594b50]"
                                    >Prix (MAD)</span
                                >
                                <input
                                    v-model="form.price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    required
                                    class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                                />
                                <span
                                    v-if="form.errors.price"
                                    class="mt-1.5 block text-xs text-red-600"
                                    >{{ form.errors.price }}</span
                                >
                            </label>

                            <label class="block">
                                <span
                                    class="mb-2 block text-sm font-medium text-[#594b50]"
                                    >Ancien prix (MAD)</span
                                >
                                <input
                                    v-model="form.compare_at_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                                    placeholder="Optionnel"
                                />
                                <span
                                    v-if="form.errors.compare_at_price"
                                    class="mt-1.5 block text-xs text-red-600"
                                    >{{ form.errors.compare_at_price }}</span
                                >
                            </label>

                            <label class="block">
                                <span
                                    class="mb-2 block text-sm font-medium text-[#594b50]"
                                    >Quantité en stock</span
                                >
                                <input
                                    v-model.number="form.stock_quantity"
                                    type="number"
                                    min="0"
                                    required
                                    class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                                />
                            </label>

                            <label class="block">
                                <span
                                    class="mb-2 block text-sm font-medium text-[#594b50]"
                                    >SKU</span
                                >
                                <input
                                    v-model="form.sku"
                                    type="text"
                                    class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm uppercase outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                                    placeholder="Optionnel"
                                />
                                <span
                                    v-if="form.errors.sku"
                                    class="mt-1.5 block text-xs text-red-600"
                                    >{{ form.errors.sku }}</span
                                >
                            </label>
                        </div>
                    </section>

                    <section
                        class="rounded-3xl border border-[#eadfda] bg-white p-4 min-[375px]:p-5 sm:rounded-[2rem] sm:p-7"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-3"
                        >
                            <div>
                                <h2
                                    class="font-serif text-xl font-semibold text-[#44373b]"
                                >
                                    Composition du pack
                                </h2>
                                <p
                                    class="mt-1 text-xs leading-5 text-[#948187]"
                                >
                                    Le stock des produits inclus sera contrôlé
                                    lors de la commande.
                                </p>
                            </div>
                            <label
                                class="inline-flex min-h-10 cursor-pointer items-center gap-3 rounded-xl bg-[#faf6f3] px-3 text-xs font-semibold text-[#594b50]"
                            >
                                Produit vendu en pack
                                <input
                                    v-model="form.is_pack"
                                    type="checkbox"
                                    class="size-5 accent-[#ad6878]"
                                />
                            </label>
                        </div>
                        <div v-if="form.is_pack" class="mt-5 space-y-3">
                            <div
                                v-for="(item, index) in form.pack_items"
                                :key="index"
                                class="grid grid-cols-[minmax(0,1fr)_80px_44px] gap-2"
                            >
                                <select
                                    v-model.number="item.product_id"
                                    required
                                    class="min-h-11 min-w-0 rounded-xl border border-[#dfd3cf] bg-[#fffdfb] px-3 text-sm outline-none focus:border-[#b77483]"
                                >
                                    <option :value="0" disabled>
                                        Sélectionner un produit
                                    </option>
                                    <option
                                        v-for="option in productOptions"
                                        :key="option.id"
                                        :value="option.id"
                                    >
                                        {{ option.name }} ({{
                                            option.stock_quantity
                                        }})
                                    </option>
                                </select>
                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    min="1"
                                    required
                                    aria-label="Quantité dans le pack"
                                    class="min-h-11 rounded-xl border border-[#dfd3cf] bg-[#fffdfb] px-3 text-sm outline-none focus:border-[#b77483]"
                                />
                                <button
                                    type="button"
                                    class="grid size-11 place-items-center rounded-xl bg-[#faeaea] text-[#a65353]"
                                    aria-label="Retirer du pack"
                                    @click="removePackItem(index)"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                            <button
                                type="button"
                                class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-[#f7e5e9] px-4 text-xs font-semibold text-[#935866]"
                                @click="addPackItem"
                            >
                                <Plus class="size-4" /> Ajouter un produit
                            </button>
                            <span
                                v-if="form.errors.pack_items"
                                class="block text-xs text-red-600"
                                >{{ form.errors.pack_items }}</span
                            >
                        </div>
                    </section>

                    <section
                        class="rounded-3xl border border-[#eadfda] bg-white p-4 min-[375px]:p-5 sm:rounded-[2rem] sm:p-7"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-3"
                        >
                            <div>
                                <h2
                                    class="font-serif text-xl font-semibold text-[#44373b]"
                                >
                                    Images
                                </h2>
                                <p
                                    class="mt-1 text-xs leading-5 text-[#948187]"
                                >
                                    Jusqu’à 8 images · JPG, PNG ou WebP · 4 Mo
                                    par image.
                                </p>
                            </div>
                            <label
                                class="inline-flex min-h-10 cursor-pointer items-center gap-2 rounded-xl bg-[#f7e5e9] px-3 text-xs font-semibold text-[#935866] hover:bg-[#efd8dd]"
                            >
                                <ImagePlus class="size-4" /> Ajouter
                                <input
                                    type="file"
                                    multiple
                                    accept="image/jpeg,image/png,image/webp"
                                    class="sr-only"
                                    @change="selectImages"
                                />
                            </label>
                        </div>

                        <label class="mt-5 block">
                            <span
                                class="mb-2 block text-sm font-medium text-[#594b50]"
                                >URL de l'image</span
                            >
                            <input
                                v-model="form.main_image_url"
                                type="url"
                                inputmode="url"
                                class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm outline-none focus:border-[#b77483] focus:ring-4 focus:ring-[#efdde1]"
                                placeholder="https://exemple.com/image.jpg"
                            />
                            <span class="mt-1.5 block text-xs text-[#948187]">
                                URL HTTPS uniquement. Elle sera utilisée comme
                                image principale.
                            </span>
                            <span
                                v-if="form.errors.main_image_url"
                                class="mt-1.5 block text-xs text-red-600"
                                >{{ form.errors.main_image_url }}</span
                            >
                        </label>

                        <img
                            v-if="
                                form.main_image_url ||
                                product?.main_image_preview_url
                            "
                            :src="
                                form.main_image_url ||
                                product?.main_image_preview_url ||
                                ''
                            "
                            alt="Aperçu de l’image principale"
                            class="mt-4 aspect-video w-full max-w-sm rounded-2xl bg-[#f6eeeb] object-cover"
                        />

                        <div
                            v-if="orderedImages.length"
                            class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
                        >
                            <article
                                v-for="(image, imageIndex) in orderedImages"
                                :key="image.id"
                                class="overflow-hidden rounded-2xl border border-[#eadfda] bg-[#fcf8f6]"
                            >
                                <div class="relative aspect-square">
                                    <img
                                        :src="image.url"
                                        alt="Image du produit"
                                        class="h-full w-full object-cover"
                                    />
                                    <span
                                        v-if="image.is_main"
                                        class="absolute top-2 left-2 inline-flex items-center gap-1 rounded-full bg-white/90 px-2 py-1 text-[10px] font-semibold text-[#935866] shadow"
                                    >
                                        <Crown class="size-3" /> Principale
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between gap-1 p-2"
                                >
                                    <div class="flex gap-1">
                                        <button
                                            type="button"
                                            :disabled="imageIndex === 0"
                                            class="grid size-8 place-items-center rounded-lg bg-white text-[#78696d] disabled:opacity-30"
                                            aria-label="Déplacer avant"
                                            @click="moveImage(imageIndex, -1)"
                                        >
                                            <ArrowUp class="size-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            :disabled="
                                                imageIndex ===
                                                orderedImages.length - 1
                                            "
                                            class="grid size-8 place-items-center rounded-lg bg-white text-[#78696d] disabled:opacity-30"
                                            aria-label="Déplacer après"
                                            @click="moveImage(imageIndex, 1)"
                                        >
                                            <ArrowDown class="size-3.5" />
                                        </button>
                                    </div>
                                    <button
                                        v-if="!image.is_main"
                                        type="button"
                                        class="grid size-8 place-items-center rounded-lg bg-white text-[#a36a77]"
                                        aria-label="Définir comme image principale"
                                        @click="setMainImage(image)"
                                    >
                                        <Crown class="size-3.5" />
                                    </button>
                                    <button
                                        type="button"
                                        class="grid size-8 place-items-center rounded-lg bg-[#faeaea] text-[#a65353]"
                                        aria-label="Supprimer l’image"
                                        @click="imageToDelete = image"
                                    >
                                        <Trash2 class="size-3.5" />
                                    </button>
                                </div>
                            </article>
                        </div>

                        <div
                            v-if="imagePreviews.length"
                            class="mt-5 rounded-2xl bg-[#faf5f2] p-3"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-xs font-semibold text-[#756368]">
                                    Nouvelles images
                                </p>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 text-xs text-[#a15d6c]"
                                    @click="clearSelectedImages"
                                >
                                    <X class="size-3.5" /> Retirer
                                </button>
                            </div>
                            <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                                <img
                                    v-for="preview in imagePreviews"
                                    :key="preview"
                                    :src="preview"
                                    alt="Prévisualisation"
                                    class="aspect-square w-full rounded-xl object-cover"
                                />
                            </div>
                        </div>
                        <span
                            v-if="form.errors.images"
                            class="mt-2 block text-xs text-red-600"
                            >{{ form.errors.images }}</span
                        >
                    </section>
                </div>

                <aside class="space-y-5">
                    <section
                        class="rounded-3xl border border-[#eadfda] bg-white p-4 min-[375px]:p-5 sm:rounded-[2rem]"
                    >
                        <h2
                            class="font-serif text-xl font-semibold text-[#44373b]"
                        >
                            Publication
                        </h2>
                        <div class="mt-4 space-y-3">
                            <label
                                class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl bg-[#faf6f3] p-4"
                            >
                                <span>
                                    <span
                                        class="block text-sm font-semibold text-[#514348]"
                                        >Produit actif</span
                                    >
                                    <span
                                        class="mt-1 block text-xs text-[#948187]"
                                        >Visible dans la boutique.</span
                                    >
                                </span>
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="size-5 accent-[#ad6878]"
                                />
                            </label>
                            <label
                                class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl bg-[#faf6f3] p-4"
                            >
                                <span>
                                    <span
                                        class="block text-sm font-semibold text-[#514348]"
                                        >Mis en avant</span
                                    >
                                    <span
                                        class="mt-1 block text-xs text-[#948187]"
                                        >Affiché parmi les favoris.</span
                                    >
                                </span>
                                <input
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="size-5 accent-[#ad6878]"
                                />
                            </label>
                        </div>
                    </section>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-[#a96171] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#955464] disabled:opacity-50"
                    >
                        <Save class="size-4.5" />
                        {{
                            form.processing ? 'Enregistrement…' : 'Enregistrer'
                        }}
                    </button>
                </aside>
            </div>
        </form>

        <DeleteConfirmDialog
            :open="imageToDelete !== null"
            title="Supprimer cette image ?"
            description="Le fichier sera supprimé définitivement du stockage de ce produit."
            :processing="deletingImage"
            @cancel="imageToDelete = null"
            @confirm="confirmImageDeletion"
        />
    </div>
</template>
