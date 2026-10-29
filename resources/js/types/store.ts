export type StoreCategory = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    products_count: number;
};

export type ProductReview = {
    id: number;
    customer_name: string;
    rating: number;
    comment: string;
    created_at: string;
};

export type StoreProduct = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    usage_instructions: string | null;
    ingredients: string | null;
    precautions: string | null;
    price: number | string;
    compare_at_price: number | string | null;
    stock_quantity: number;
    main_image: string | null;
    main_image_mode: 'packshot' | 'editorial';
    images: string[];
    is_featured: boolean;
    is_pack: boolean;
    pack_items: { id: number; name: string; quantity: number }[];
    show_in_slider: boolean;
    category_id: number;
    category: Pick<StoreCategory, 'id' | 'name' | 'slug'> | null;
    reviews_enabled: boolean;
    rating_average: number | null;
    reviews_count: number;
    reviews: ProductReview[];
};

export type StoreCartLine = {
    product_id: number;
    name: string;
    price: number;
    quantity: number;
    image: string | null;
    slug: string;
    stock: number;
};

export const formatMoney = (value: number | string): string =>
    `${Number(value).toLocaleString('fr-MA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} MAD`;

export const productIsAvailable = (product: StoreProduct): boolean =>
    Number(product.price) > 0 && product.stock_quantity > 0;

export type BilingualName = {
    french: string | null;
    arabic: string | null;
};

const containsArabic = (value: string): boolean =>
    /[\u0600-\u06ff]/u.test(value);
const trimLanguageSeparator = (value: string): string =>
    value.replace(/^[\s\-–—|/]+|[\s\-–—|/]+$/gu, '').trim();

export const splitBilingualName = (name: string): BilingualName => {
    const normalizedName = name.trim();
    const segments = normalizedName
        .split(/\s+(?:[-–—|/])\s+/u)
        .map(trimLanguageSeparator)
        .filter(Boolean);
    const frenchSegments = segments.filter(
        (segment) => !containsArabic(segment),
    );
    const arabicSegments = segments.filter(containsArabic);

    if (frenchSegments.length > 0 && arabicSegments.length > 0) {
        return {
            french: frenchSegments.join(' — '),
            arabic: arabicSegments.join(' — '),
        };
    }

    const arabicStart = normalizedName.search(/[\u0600-\u06ff]/u);

    if (arabicStart > 0) {
        return {
            french: trimLanguageSeparator(normalizedName.slice(0, arabicStart)),
            arabic: trimLanguageSeparator(normalizedName.slice(arabicStart)),
        };
    }

    return containsArabic(normalizedName)
        ? { french: null, arabic: normalizedName }
        : { french: normalizedName, arabic: null };
};
