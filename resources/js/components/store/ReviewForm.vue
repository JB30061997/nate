<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Star } from '@lucide/vue';

const props = defineProps<{ productId: number }>();
const form = useForm({
    customer_name: '',
    rating: 5,
    comment: '',
    website: '',
});
const submit = () =>
    form.post(`/produits/${props.productId}/avis`, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
</script>

<template>
    <form
        class="rounded-2xl border border-[#eadfe1] bg-[#fffaf7] p-4"
        @submit.prevent="submit"
    >
        <h4 class="text-sm font-semibold text-[#392631]">شاركي تجربتكِ</h4>
        <div class="mt-3 flex gap-1" role="radiogroup" aria-label="تقييمكِ">
            <button
                v-for="value in 5"
                :key="value"
                type="button"
                class="rounded p-1"
                :aria-label="`${value} نجوم`"
                @click="form.rating = value"
            >
                <Star
                    class="size-5"
                    :class="
                        value <= form.rating
                            ? 'fill-[#b86f87] text-[#b86f87]'
                            : 'text-[#d8c8cd]'
                    "
                />
            </button>
        </div>
        <input
            v-model="form.website"
            tabindex="-1"
            autocomplete="off"
            class="absolute -left-[9999px]"
            aria-hidden="true"
        />
        <input
            v-model="form.customer_name"
            type="text"
            maxlength="100"
            required
            placeholder="اسمكِ"
            class="store-field mt-3"
        />
        <p v-if="form.errors.customer_name" class="mt-1 text-xs text-red-700">
            {{ form.errors.customer_name }}
        </p>
        <textarea
            v-model="form.comment"
            rows="3"
            minlength="10"
            maxlength="1500"
            required
            placeholder="رأيكِ بكل صراحة…"
            class="store-field mt-3 resize-none"
        />
        <p v-if="form.errors.comment" class="mt-1 text-xs text-red-700">
            {{ form.errors.comment }}
        </p>
        <button
            type="submit"
            :disabled="form.processing"
            class="store-button-primary mt-3 w-full justify-center"
        >
            {{ form.processing ? 'جارٍ الإرسال…' : 'إرسال الرأي للمراجعة' }}
        </button>
    </form>
</template>
