<script setup lang="ts">
import { ShoppingBag } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    cartCount: number;
    whatsappNumber: string;
}>();

defineEmits<{ cart: [] }>();

const whatsappHref = computed(
    () => `https://wa.me/${props.whatsappNumber.replace(/\D/g, '')}`,
);
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 bottom-0 z-[60]" dir="ltr">
        <a
            :href="whatsappHref"
            target="_blank"
            rel="noopener noreferrer"
            class="floating-action floating-whatsapp pointer-events-auto fixed right-4 bottom-[calc(1rem+env(safe-area-inset-bottom))] sm:right-6 sm:bottom-[calc(1.5rem+env(safe-area-inset-bottom))]"
            aria-label="التواصل عبر واتساب"
            title="التواصل عبر واتساب"
        >
            <svg
                class="size-7"
                viewBox="0 0 24 24"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    d="M12.04 2a9.84 9.84 0 0 0-8.52 14.76L2 22l5.38-1.41A9.96 9.96 0 1 0 12.04 2Zm0 17.98a8.08 8.08 0 0 1-4.12-1.13l-.3-.18-3.19.84.85-3.11-.2-.32a8.14 8.14 0 1 1 6.96 3.9Zm4.46-6.1c-.24-.12-1.45-.72-1.68-.8-.22-.09-.38-.12-.55.12-.16.25-.63.8-.78.97-.14.16-.28.18-.52.06-.25-.12-1.03-.38-1.96-1.21a7.3 7.3 0 0 1-1.36-1.69c-.14-.24-.02-.37.1-.5.12-.1.25-.28.37-.42.12-.14.16-.24.24-.4.08-.17.04-.31-.02-.43-.06-.12-.55-1.32-.75-1.81-.2-.48-.4-.41-.55-.42h-.47a.9.9 0 0 0-.65.3c-.22.25-.85.83-.85 2.03s.87 2.36 1 2.52c.12.16 1.71 2.61 4.15 3.66.58.25 1.03.4 1.38.52.58.18 1.11.16 1.53.1.47-.07 1.45-.6 1.66-1.17.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.47-.28Z"
                />
            </svg>
            <span class="floating-label floating-label-right">واتساب</span>
        </a>

        <button
            type="button"
            class="floating-action floating-cart pointer-events-auto fixed bottom-[calc(1rem+env(safe-area-inset-bottom))] left-4 sm:bottom-[calc(1.5rem+env(safe-area-inset-bottom))] sm:left-6"
            aria-label="فتح سلة التسوق"
            title="سلة التسوق"
            @click="$emit('cart')"
        >
            <ShoppingBag class="size-6" />
            <span
                v-if="cartCount > 0"
                class="absolute -top-1 -right-1 grid min-h-5 min-w-5 place-items-center rounded-full border-2 border-[#fffbf8] bg-[#b86f87] px-1 text-[10px] leading-none font-black text-white"
            >
                {{ cartCount > 99 ? '99+' : cartCount }}
            </span>
            <span class="floating-label floating-label-left">السلة</span>
        </button>
    </div>
</template>

<style scoped>
.floating-action {
    display: grid;
    width: 3.5rem;
    height: 3.5rem;
    place-items: center;
    border: 1px solid rgb(255 255 255 / 72%);
    border-radius: 9999px;
    color: #fff;
    box-shadow:
        0 15px 35px rgb(57 38 49 / 20%),
        inset 0 1px 0 rgb(255 255 255 / 25%);
    transition:
        transform 220ms ease,
        box-shadow 220ms ease,
        background-color 220ms ease;
}
.floating-action:hover,
.floating-action:focus-visible {
    transform: translateY(-4px) scale(1.04);
    outline: 3px solid rgb(255 251 248 / 92%);
    outline-offset: 3px;
    box-shadow: 0 20px 42px rgb(57 38 49 / 27%);
}
.floating-action:active {
    transform: translateY(-1px) scale(0.96);
}
.floating-whatsapp {
    background: #218c5a;
}
.floating-whatsapp:hover,
.floating-whatsapp:focus-visible {
    background: #176f46;
}
.floating-cart {
    background: #392631;
}
.floating-cart:hover,
.floating-cart:focus-visible {
    background: #593b4b;
}
.floating-label {
    position: absolute;
    top: 50%;
    width: max-content;
    padding: 0.4rem 0.65rem;
    border: 1px solid #eadde1;
    border-radius: 9999px;
    background: rgb(255 251 248 / 96%);
    color: #392631;
    font-size: 0.7rem;
    font-weight: 700;
    opacity: 0;
    pointer-events: none;
    box-shadow: 0 8px 24px rgb(57 38 49 / 12%);
    transform: translateY(-50%) scale(0.94);
    transition: 180ms ease;
}
.floating-label-right {
    right: calc(100% + 0.65rem);
}
.floating-label-left {
    left: calc(100% + 0.65rem);
}
.floating-action:hover .floating-label,
.floating-action:focus-visible .floating-label {
    opacity: 1;
    transform: translateY(-50%) scale(1);
}
@media (prefers-reduced-motion: reduce) {
    .floating-action,
    .floating-label {
        transition: none;
    }
}
</style>
