<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Save, Trash2 } from '@lucide/vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';

type Settings = Record<string, string | null>;
type Faq = {
    id: number;
    question: string;
    answer: string;
    is_active: boolean;
    sort_order: number;
};
type ContentForm = {
    whatsapp: string;
    instagram: string;
    phone: string;
    email: string;
    delivery_fee: string;
    delivery_zones: string;
    delivery_terms: string;
    about_content: string;
    terms_content: string;
    privacy_content: string;
    returns_content: string;
};
type ContentField = {
    key: keyof ContentForm;
    label: string;
    multiline?: boolean;
    type?: string;
};
const props = defineProps<{
    settings: Settings;
    faqs: Faq[];
    databaseReady: boolean;
}>();
const form = useForm<ContentForm>({
    whatsapp: props.settings.whatsapp ?? '',
    instagram: props.settings.instagram ?? '',
    phone: props.settings.phone ?? '',
    email: props.settings.email ?? '',
    delivery_fee: props.settings.delivery_fee ?? '0.00',
    delivery_zones: props.settings.delivery_zones ?? '',
    delivery_terms: props.settings.delivery_terms ?? '',
    about_content: props.settings.about_content ?? '',
    terms_content: props.settings.terms_content ?? '',
    privacy_content: props.settings.privacy_content ?? '',
    returns_content: props.settings.returns_content ?? '',
});
const contentSections: { title: string; fields: ContentField[] }[] = [
    {
        title: 'Contact',
        fields: [
            { key: 'whatsapp', label: 'WhatsApp' },
            { key: 'instagram', label: 'Instagram' },
            { key: 'phone', label: 'Téléphone' },
            { key: 'email', label: 'Email', type: 'email' },
        ],
    },
    {
        title: 'Livraison',
        fields: [
            {
                key: 'delivery_fee',
                label: 'Frais de livraison (MAD)',
                type: 'number',
            },
            {
                key: 'delivery_zones',
                label: 'Zones desservies',
                multiline: true,
            },
            {
                key: 'delivery_terms',
                label: 'Modalités de livraison et paiement',
                multiline: true,
            },
        ],
    },
    {
        title: 'Pages informatives',
        fields: [
            {
                key: 'about_content',
                label: 'À propos de NATÉ',
                multiline: true,
            },
            {
                key: 'terms_content',
                label: 'Conditions générales',
                multiline: true,
            },
            {
                key: 'privacy_content',
                label: 'Politique de confidentialité',
                multiline: true,
            },
            {
                key: 'returns_content',
                label: 'Retours et échanges',
                multiline: true,
            },
        ],
    },
];
const faqForm = useForm({
    question: '',
    answer: '',
    is_active: true,
    sort_order: 0,
});
const save = (): void => form.patch('/admin/content', { preserveScroll: true });
const addFaq = (): void =>
    faqForm.post('/admin/faqs', {
        preserveScroll: true,
        onSuccess: () => faqForm.reset(),
    });
const updateFaq = (faq: Faq): void =>
    router.patch(`/admin/faqs/${faq.id}`, faq, { preserveScroll: true });
const removeFaq = (faq: Faq): void => {
    if (window.confirm('Supprimer cette question ?'))
        router.delete(`/admin/faqs/${faq.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Contenu boutique" />
    <div class="min-w-0 space-y-6">
        <AdminPageHeader
            title="Contenu boutique"
            description="Gérez uniquement les informations vérifiées affichées aux clientes."
        />
        <p
            v-if="!databaseReady"
            class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"
        >
            Exécutez les nouvelles migrations avant d’enregistrer ce contenu.
        </p>
        <form class="space-y-6" @submit.prevent="save">
            <section
                v-for="section in contentSections"
                :key="section.title"
                class="rounded-3xl border border-[#eadfda] bg-white p-4 sm:p-7"
            >
                <h2 class="font-serif text-xl font-semibold text-[#44373b]">
                    {{ section.title }}
                </h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <label
                        v-for="field in section.fields"
                        :key="field.key"
                        class="block"
                        :class="field.multiline ? 'sm:col-span-2' : ''"
                    >
                        <span
                            class="mb-2 block text-sm font-medium text-[#594b50]"
                            >{{ field.label }}</span
                        >
                        <textarea
                            v-if="field.multiline"
                            v-model="form[field.key]"
                            rows="5"
                            class="w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 py-3 text-sm outline-none focus:border-[#b77483]"
                        />
                        <input
                            v-else
                            v-model="form[field.key]"
                            :type="field.type ?? 'text'"
                            :step="field.type === 'number' ? '0.01' : undefined"
                            class="min-h-12 w-full rounded-2xl border border-[#dfd3cf] bg-[#fffdfb] px-4 text-sm outline-none focus:border-[#b77483]"
                        />
                    </label>
                </div>
            </section>
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex min-h-12 items-center gap-2 rounded-2xl bg-[#a96171] px-6 text-sm font-semibold text-white"
            >
                <Save class="size-4" /> Enregistrer
            </button>
        </form>

        <section
            class="rounded-3xl border border-[#eadfda] bg-white p-4 sm:p-7"
        >
            <h2 class="font-serif text-xl font-semibold text-[#44373b]">FAQ</h2>
            <form
                class="mt-5 grid gap-3 sm:grid-cols-[1fr_1.4fr_auto]"
                @submit.prevent="addFaq"
            >
                <input
                    v-model="faqForm.question"
                    required
                    placeholder="Question"
                    class="min-h-11 rounded-xl border border-[#dfd3cf] px-3 text-sm"
                />
                <input
                    v-model="faqForm.answer"
                    required
                    placeholder="Réponse vérifiée"
                    class="min-h-11 rounded-xl border border-[#dfd3cf] px-3 text-sm"
                />
                <button
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#f7e5e9] px-4 text-sm text-[#935866]"
                >
                    <Plus class="size-4" /> Ajouter
                </button>
            </form>
            <div class="mt-5 space-y-3">
                <article
                    v-for="faq in faqs"
                    :key="faq.id"
                    class="grid gap-3 rounded-2xl bg-[#faf6f3] p-3 sm:grid-cols-[1fr_1.4fr_90px_auto]"
                >
                    <input
                        v-model="faq.question"
                        class="min-h-10 rounded-xl border border-[#dfd3cf] bg-white px-3 text-sm"
                    />
                    <textarea
                        v-model="faq.answer"
                        rows="2"
                        class="rounded-xl border border-[#dfd3cf] bg-white px-3 py-2 text-sm"
                    />
                    <div class="flex flex-col gap-2">
                        <input
                            v-model.number="faq.sort_order"
                            type="number"
                            min="0"
                            aria-label="Ordre"
                            class="min-h-9 w-full rounded-xl border border-[#dfd3cf] bg-white px-2 text-sm"
                        />
                        <label
                            class="flex items-center gap-2 text-xs text-[#594b50]"
                            ><input
                                v-model="faq.is_active"
                                type="checkbox"
                                class="accent-[#ad6878]"
                            />
                            Publiée</label
                        >
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="grid size-10 place-items-center rounded-xl bg-white text-[#935866]"
                            @click="updateFaq(faq)"
                        >
                            <Save class="size-4" /></button
                        ><button
                            type="button"
                            class="grid size-10 place-items-center rounded-xl bg-red-50 text-red-600"
                            @click="removeFaq(faq)"
                        >
                            <Trash2 class="size-4" />
                        </button>
                    </div>
                </article>
            </div>
        </section>
    </div>
</template>
