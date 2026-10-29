export type OrderLine = {
    id: number;
    product_name: string;
    unit_price: string;
    quantity: number;
    line_total: string;
    image_url: string | null;
};

export type AdminOrder = {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    customer_city: string | null;
    customer_address: string | null;
    customer_notes: string | null;
    subtotal: string;
    delivery_fee: string;
    total: string;
    status: string;
    created_at: string;
    items: OrderLine[];
};

export const formatOrderMoney = (amount: string | number | null): string =>
    `${Number(amount ?? 0).toLocaleString('fr-MA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} MAD`;

export const formatOrderDate = (value: string): string =>
    new Date(value).toLocaleString('fr-MA', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });

const labels: Record<string, string> = {
    pending: 'En attente',
    confirmed: 'Confirmée',
    preparing: 'En préparation',
    processing: 'En préparation',
    shipped: 'Expédiée',
    delivered: 'Livrée',
    cancelled: 'Annulée',
    en_attente: 'En attente',
    confirme: 'Confirmée',
    livre: 'Livrée',
    annule: 'Annulée',
};

export const orderStatusLabel = (value: string): string =>
    labels[value] ?? value;

export const orderStatusClass = (value: string): string => {
    if (['delivered', 'livre'].includes(value))
        return 'border-emerald-200 bg-emerald-50 text-emerald-800';
    if (['cancelled', 'annule'].includes(value))
        return 'border-rose-200 bg-rose-50 text-rose-800';
    if (['pending', 'en_attente'].includes(value))
        return 'border-amber-200 bg-amber-50 text-amber-800';
    if (value === 'shipped') return 'border-sky-200 bg-sky-50 text-sky-800';
    return 'border-[#e4cbd2] bg-[#faedf1] text-[#804c60]';
};
