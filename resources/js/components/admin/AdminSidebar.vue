<script setup lang="ts">
import { Form, Link, usePage } from '@inertiajs/vue3';
import {
    FolderTree,
    FileText,
    LayoutDashboard,
    LogOut,
    MessageSquareText,
    Package,
    ShoppingBag,
    Sparkles,
    Tags,
    X,
} from '@lucide/vue';
import { computed } from 'vue';
import { logout } from '@/routes';
import { dashboard, orders } from '@/routes/admin';
import { index as categoriesIndex } from '@/routes/admin/categories';
import { index as productTypesIndex } from '@/routes/admin/product-types';
import { index as productsIndex } from '@/routes/admin/products';
import type { User } from '@/types';

defineEmits<{
    navigate: [];
}>();

const page = usePage();
const user = computed(() => page.props.auth.user as User);

const navigationItems = [
    { label: 'Dashboard', href: dashboard(), icon: LayoutDashboard },
    { label: 'Commandes', href: orders(), icon: ShoppingBag },
    { label: 'Produits', href: productsIndex(), icon: Package },
    { label: 'Catégories', href: categoriesIndex(), icon: FolderTree },
    { label: 'Types de produits', href: productTypesIndex(), icon: Tags },
    {
        label: 'Avis clients',
        href: { url: '/admin/reviews', method: 'get' as const },
        icon: MessageSquareText,
    },
    {
        label: 'Contenu boutique',
        href: { url: '/admin/content', method: 'get' as const },
        icon: FileText,
    },
];

const isActive = (url: string): boolean => page.url.startsWith(url);

const initials = computed(() =>
    user.value.name
        .split(' ')
        .map((part) => part.charAt(0))
        .join('')
        .slice(0, 2)
        .toUpperCase(),
);
</script>

<template>
    <div class="flex h-full flex-col bg-[#fffdfb]">
        <div
            class="flex items-center gap-2 border-b border-[#eee4df] px-4 py-4 sm:px-5 sm:py-5"
        >
            <Link
                :href="dashboard()"
                class="group flex min-w-0 flex-1 items-center gap-3"
                @click="$emit('navigate')"
            >
                <span
                    class="grid size-11 shrink-0 place-items-center rounded-2xl bg-[#b76e7e] text-white shadow-sm transition-transform duration-200 group-hover:-rotate-3"
                >
                    <Sparkles class="size-5" />
                </span>
                <span class="min-w-0">
                    <span
                        class="block truncate font-serif text-lg font-semibold text-[#3f3236]"
                    >
                        NATE Boutique
                    </span>
                    <span class="block text-xs tracking-wide text-[#9a7c84]">
                        Administration
                    </span>
                </span>
            </Link>
            <button
                type="button"
                class="grid size-11 shrink-0 place-items-center rounded-2xl border border-[#eadfda] bg-white text-[#5e4d53] transition hover:bg-[#f8eaed] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a45f79] lg:hidden"
                aria-label="Fermer le menu de navigation"
                @click="$emit('navigate')"
            >
                <X class="size-5" />
            </button>
        </div>

        <nav
            class="flex-1 overflow-y-auto px-3 py-5"
            aria-label="Navigation principale"
        >
            <p
                class="mb-3 px-3 text-[11px] font-semibold tracking-[0.16em] text-[#a69499] uppercase"
            >
                Menu principal
            </p>
            <ul class="space-y-1.5">
                <li v-for="item in navigationItems" :key="item.label">
                    <Link
                        :href="item.href"
                        class="group flex min-h-12 items-center gap-3 rounded-2xl px-3.5 text-sm font-medium transition-all duration-200"
                        :class="
                            isActive(item.href.url)
                                ? 'bg-[#f7e5e9] text-[#8f5361] shadow-[inset_0_0_0_1px_rgba(183,110,126,0.08)]'
                                : 'text-[#6d6064] hover:translate-x-0.5 hover:bg-[#faf3f2] hover:text-[#8f5361]'
                        "
                        :aria-current="
                            isActive(item.href.url) ? 'page' : undefined
                        "
                        @click="$emit('navigate')"
                    >
                        <span
                            class="grid size-8 place-items-center rounded-xl transition-colors"
                            :class="
                                isActive(item.href.url)
                                    ? 'bg-white/80 text-[#b06475]'
                                    : 'text-[#9a858b] group-hover:bg-white group-hover:text-[#b06475]'
                            "
                        >
                            <component :is="item.icon" class="size-[18px]" />
                        </span>
                        <span>{{ item.label }}</span>
                    </Link>
                </li>
            </ul>
        </nav>

        <div class="border-t border-[#eee4df] p-3">
            <div
                class="mb-2 flex items-center gap-3 rounded-2xl bg-[#faf6f3] p-3"
            >
                <span
                    class="grid size-10 shrink-0 place-items-center rounded-full bg-[#ead1d7] text-xs font-semibold text-[#85515d]"
                >
                    {{ initials }}
                </span>
                <span class="min-w-0">
                    <span
                        class="block truncate text-sm font-semibold text-[#493b3f]"
                    >
                        {{ user.name }}
                    </span>
                    <span class="block truncate text-xs text-[#958187]">
                        {{ user.email }}
                    </span>
                </span>
            </div>

            <Form v-bind="logout.form()" v-slot="{ processing }">
                <button
                    type="submit"
                    :disabled="processing"
                    class="flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl px-3 text-sm font-medium text-[#75666a] transition hover:bg-[#f8eaed] hover:text-[#945665] disabled:opacity-50"
                >
                    <LogOut class="size-4" />
                    Déconnexion
                </button>
            </Form>
        </div>
    </div>
</template>
