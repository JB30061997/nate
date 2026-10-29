<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import AdminHeader from '@/components/admin/AdminHeader.vue';
import AdminSidebar from '@/components/admin/AdminSidebar.vue';

const isMobileMenuOpen = ref(false);

const closeMobileMenu = (): void => {
    isMobileMenuOpen.value = false;
};

const handleEscape = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') {
        closeMobileMenu();
    }
};

watch(isMobileMenuOpen, (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
});

onMounted(() => document.addEventListener('keydown', handleEscape));

onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleEscape);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="min-h-screen overflow-x-clip bg-[#fbf8f4] text-[#302b2d]">
        <aside
            class="fixed inset-y-0 left-0 z-30 hidden w-[260px] overflow-hidden border-r border-[#eadfda] shadow-[8px_0_30px_-24px_rgba(73,50,57,0.35)] lg:block"
        >
            <AdminSidebar />
        </aside>

        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <button
                v-if="isMobileMenuOpen"
                type="button"
                class="fixed inset-0 z-40 bg-[#34292d]/40 backdrop-blur-[1px] lg:hidden"
                aria-label="Fermer le menu de navigation"
                @click="closeMobileMenu"
            ></button>
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="-translate-x-full"
            leave-active-class="transition-transform duration-250 ease-in"
            leave-to-class="-translate-x-full"
        >
            <aside
                v-if="isMobileMenuOpen"
                class="fixed inset-y-0 left-0 z-50 w-[min(calc(100vw-3rem),300px)] max-w-full overflow-hidden border-r border-[#eadfda] shadow-2xl lg:hidden"
                role="dialog"
                aria-modal="true"
                aria-label="Menu de navigation"
            >
                <AdminSidebar @navigate="closeMobileMenu" />
            </aside>
        </Transition>

        <div class="min-h-screen min-w-0 lg:pl-[260px]">
            <AdminHeader
                :is-menu-open="isMobileMenuOpen"
                @open-menu="isMobileMenuOpen = true"
            />
            <main
                class="mx-auto w-full max-w-[1600px] min-w-0 px-3 py-4 min-[375px]:px-4 sm:px-6 sm:py-7 lg:px-8"
            >
                <slot />
            </main>
        </div>
    </div>
</template>
