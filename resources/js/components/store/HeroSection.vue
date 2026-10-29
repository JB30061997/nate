<script setup lang="ts">
import { ArrowDownRight, Sparkles } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { StoreProduct } from '@/types/store';
import { formatMoney } from '@/types/store';
const props = defineProps<{
    products: StoreProduct[];
    modalOpen: boolean;
}>();
const emit = defineEmits<{ open: [product: StoreProduct] }>();
const INTERVAL = 3000;
const currentIndex = ref(0);
const pointerInside = ref(false);
const focusInside = ref(false);
const pageHidden = ref(false);
const reducedMotion = ref(false);
let timer: ReturnType<typeof setTimeout> | null = null;
let motionQuery: MediaQueryList | null = null;
const slides = computed(() =>
    props.products.filter((product) => Boolean(product.main_image)),
);
const currentProduct = computed(() => slides.value[currentIndex.value] ?? null);
type HeroMessage = {
    firstLine: string;
    secondLine: string;
    accentLine: string;
    description: string;
};
const heroMessages: Record<string, HeroMessage> = {
    'soins-du-visage': {
        firstLine: 'إشراقتكِ',
        secondLine: 'تبدأ من',
        accentLine: 'عنايتكِ.',
        description: 'عناية يومية تمنح بشرتكِ مظهراً صافياً ومشرقاً.',
    },
    'soins-des-cheveux': {
        firstLine: 'شعرٌ صحي،',
        secondLine: 'جمالٌ',
        accentLine: 'يلفت الأنظار.',
        description: 'اختيارات تمنح شعركِ العناية التي يستحقها كل يوم.',
    },
    'soins-du-corps': {
        firstLine: 'دلّلي',
        secondLine: 'جسمكِ',
        accentLine: 'كل يوم.',
        description: 'لحظات عناية ناعمة تمنحكِ راحة وثقة متجددة.',
    },
    'hygiene-soins-intimes': {
        firstLine: 'انتعاشٌ',
        secondLine: 'وراحةٌ',
        accentLine: 'تدومان.',
        description: 'عناية لطيفة ترافقكِ بثقة وراحة طوال اليوم.',
    },
    'beaute-maquillage': {
        firstLine: 'لمستكِ',
        secondLine: 'تصنع',
        accentLine: 'الفرق.',
        description: 'تفاصيل مختارة لتعبّري عن جمالكِ بطريقتكِ.',
    },
    'bien-etre-complements': {
        firstLine: 'جمالكِ',
        secondLine: 'يبدأ من',
        accentLine: 'داخلكِ.',
        description: 'اختيارات للعناية بصحتكِ وتوازنكِ من الداخل.',
    },
    'coffrets-packs': {
        firstLine: 'روتينكِ',
        secondLine: 'الكامل',
        accentLine: 'في باقة.',
        description: 'منتجات متكاملة اختيرت لتجعل عنايتكِ أكثر سهولة.',
    },
};
const defaultHeroMessage = heroMessages['beaute-maquillage']!;
const currentHeroMessage = computed(
    () =>
        heroMessages[currentProduct.value?.category?.slug ?? ''] ??
        defaultHeroMessage,
);
const hasNavigation = computed(() => slides.value.length > 1);
const isPaused = computed(
    () =>
        pointerInside.value ||
        focusInside.value ||
        props.modalOpen ||
        pageHidden.value ||
        reducedMotion.value ||
        !hasNavigation.value,
);
const clearTimer = (): void => {
    if (timer !== null) {
        window.clearTimeout(timer);
        timer = null;
    }
};
const preloadNextImage = (): void => {
    if (!hasNavigation.value) return;
    const nextProduct =
        slides.value[(currentIndex.value + 1) % slides.value.length];
    if (!nextProduct?.main_image) return;
    const image = new Image();
    image.src = nextProduct.main_image;
};
const scheduleNext = (): void => {
    clearTimer();
    if (isPaused.value) return;
    preloadNextImage();
    timer = window.setTimeout(() => {
        currentIndex.value = (currentIndex.value + 1) % slides.value.length;
    }, INTERVAL);
};
const handleVisibility = (): void => {
    pageHidden.value = document.hidden;
};
const handleMotionPreference = (event: MediaQueryListEvent): void => {
    reducedMotion.value = event.matches;
};
const handleFocusOut = (event: FocusEvent): void => {
    const nextTarget = event.relatedTarget;
    focusInside.value =
        nextTarget instanceof Node &&
        event.currentTarget instanceof Node &&
        event.currentTarget.contains(nextTarget);
};
watch(
    [currentIndex, isPaused, () => slides.value.length],
    () => {
        if (currentIndex.value >= slides.value.length) currentIndex.value = 0;
        scheduleNext();
    },
    { flush: 'post' },
);
onMounted(() => {
    pageHidden.value = document.hidden;
    motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    reducedMotion.value = motionQuery.matches;
    motionQuery.addEventListener('change', handleMotionPreference);
    document.addEventListener('visibilitychange', handleVisibility);
    scheduleNext();
});
onBeforeUnmount(() => {
    clearTimer();
    motionQuery?.removeEventListener('change', handleMotionPreference);
    document.removeEventListener('visibilitychange', handleVisibility);
});
</script>
<template>
    <section
        class="hero-shell relative isolate overflow-hidden border-y border-[#eadde1] bg-[#fffaf8] text-[#392631]"
    >
        <div
            class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_15%\_20%,rgba(231,188,202,0.44),transparent_28%),radial-gradient(circle_at_82%\_78%,rgba(217,193,162,0.32),transparent_28%),linear-gradient(135deg,#fffdfb_0%,#fff7f5_48%,#f9e9ed_100%)]"
        />
        <div class="hero-noise absolute inset-0 -z-10 opacity-40" />
        <span
            class="hero-orbit absolute -top-32 -right-32 -z-10 size-72 rounded-full border border-[#c98aa0]/20 sm:size-[30rem]"
        />
        <div
            class="petal-field pointer-events-none absolute inset-0 -z-10"
            aria-hidden="true"
        >
            <span class="petal petal-one" /><span class="petal petal-two" />
            <span class="petal petal-three" /><span class="petal petal-four" />
            <span class="petal petal-five" />
        </div>
        <div
            class="relative min-h-[calc(100svh-7.5rem)] overflow-hidden lg:hidden"
            aria-roledescription="carousel"
            aria-label="مختارات منتجات ناتي"
            @mouseenter="pointerInside = true"
            @mouseleave="pointerInside = false"
            @focusin="focusInside = true"
            @focusout="handleFocusOut"
        >
            <img
                src="/images/hero/mobile-khawla-editorial.webp"
                alt=""
                class="absolute inset-0 h-full w-full object-cover object-[42%_center]"
                loading="eager"
                fetchpriority="high"
                aria-hidden="true"
            />
            <div
                class="absolute inset-0 bg-[linear-gradient(270deg,rgba(57,38,49,0.9)_0%,rgba(74,48,61,0.74)_36%,rgba(57,38,49,0.18)_64%,rgba(57,38,49,0.02)_100%)]"
            />
            <div
                class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-[#392631]/65 to-transparent"
            />
            <p
                class="absolute top-7 right-5 z-30 inline-flex min-h-9 items-center gap-1.5 rounded-full border border-white/35 bg-[#392631]/30 px-3 text-[10px] font-bold text-white shadow-sm backdrop-blur-md min-[390px]:right-6 min-[430px]:top-9"
                dir="rtl"
            >
                <Sparkles class="size-4" /> اختيارات ناتي للجمال
            </p>
            <div
                class="relative z-10 flex min-h-[calc(100svh-7.5rem)] flex-col px-5 pt-8 pb-6 min-[390px]:px-6 min-[430px]:pt-10"
                dir="rtl"
            >
                <div
                    class="flex w-[59%] max-w-[15rem] flex-1 flex-col items-end justify-center self-start pb-36 text-right min-[390px]:w-[57%] min-[390px]:max-w-[16rem]"
                >
                    <Transition
                        :name="reducedMotion ? '' : 'hero-copy'"
                        mode="out-in"
                    >
                        <div :key="currentProduct?.category?.slug ?? 'default'">
                            <h1
                                class="store-display text-[clamp(2.25rem,11vw,3.35rem)] leading-[1.06] font-black text-white drop-shadow-[0_3px_16px_rgba(57,38,49,0.32)]"
                            >
                                {{ currentHeroMessage.firstLine }}<br />{{
                                    currentHeroMessage.secondLine
                                }}<br /><em class="text-[#f0b7ca] not-italic">{{
                                    currentHeroMessage.accentLine
                                }}</em>
                            </h1>
                            <p
                                class="mt-4 text-[11px] leading-6 font-medium text-white/90 min-[390px]:text-xs"
                            >
                                {{ currentHeroMessage.description }}
                            </p>
                        </div>
                    </Transition>
                </div>

                <div class="mt-auto">
                    <Transition :name="reducedMotion ? '' : 'hero-product'">
                        <button
                            v-if="currentProduct"
                            :key="currentProduct.id"
                            type="button"
                            class="group absolute bottom-24 left-0 z-10 h-[39svh] max-h-[360px] min-h-[270px] w-[58%] text-right focus-visible:outline-2 focus-visible:outline-offset-[-4px] focus-visible:outline-white"
                            :aria-label="`عرض ${currentProduct.name}`"
                            @click="emit('open', currentProduct)"
                        >
                            <img
                                :src="currentProduct.main_image!"
                                :alt="currentProduct.name"
                                class="absolute bottom-3 left-[-8%] h-[79%] w-[112%] object-contain drop-shadow-[0_22px_20px_rgba(57,38,49,0.3)] transition duration-700 group-active:scale-[0.98]"
                                :loading="currentIndex === 0 ? 'eager' : 'lazy'"
                                :fetchpriority="
                                    currentIndex === 0 ? 'high' : 'auto'
                                "
                            />
                        </button>
                    </Transition>

                    <a
                        href="#collection"
                        class="hero-primary-action absolute bottom-12 left-1/2 z-30 inline-flex min-h-11 -translate-x-1/2 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-white px-5 text-xs font-black whitespace-nowrap text-[#392631] shadow-[0_14px_30px_rgba(32,18,25,0.28)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                    >
                        اكتشفي المنتجات <ArrowDownRight class="size-4" />
                    </a>

                    <div
                        v-if="hasNavigation"
                        class="absolute bottom-3 left-1/2 z-30 flex -translate-x-1/2 items-center gap-1.5 rounded-full border border-white/30 bg-[#392631]/45 px-3 py-2 shadow-sm backdrop-blur-md"
                        aria-label="تقدم عرض المنتجات"
                    >
                        <span
                            v-for="(_, index) in slides"
                            :key="index"
                            class="h-1.5 rounded-full transition-all duration-500"
                            :class="
                                index === currentIndex
                                    ? 'w-6 bg-[#f3bfd0]'
                                    : 'w-1.5 bg-white/45'
                            "
                        />
                    </div>
                </div>
            </div>
        </div>

        <div
            class="relative mx-auto hidden min-h-[700px] max-w-[1500px] items-center gap-12 px-12 py-8 lg:grid lg:grid-cols-2 xl:min-h-[740px] xl:gap-16 xl:px-16"
        >
            <div
                class="z-10 mx-auto w-full max-w-[620px] px-2 text-right lg:mr-0 lg:pr-4 xl:pr-8"
            >
                <p
                    class="hero-reveal hero-reveal-1 mb-6 inline-flex min-h-10 items-center gap-2 rounded-full border border-[#dfbdc8] bg-white/70 px-4 text-xs font-bold text-[#8f4c65] shadow-sm backdrop-blur"
                >
                    <Sparkles class="size-4" /> اختيارات ناتي للجمال
                </p>
                <h1
                    class="hero-reveal hero-reveal-2 text-[clamp(3.35rem,5.7vw,6.15rem)] leading-[1.02] font-black text-[#392631]"
                >
                    الجمال،<br /><em
                        class="font-black text-[#b85f7c] not-italic"
                        >على طريقتكِ.</em
                    >
                </h1>
                <p
                    class="hero-reveal hero-reveal-3 mt-6 max-w-[34rem] text-[15px] leading-8 text-[#6f5963] sm:text-lg sm:leading-9"
                >
                    اكتشفي مجموعتنا الحقيقية من منتجات العناية والتجميل، واختاري
                    روتينكِ بكل سهولة.
                </p>
                <div
                    class="hero-reveal hero-reveal-4 mt-8 flex flex-col gap-3 min-[390px]:flex-row min-[390px]:flex-wrap"
                >
                    <a
                        href="#collection"
                        class="hero-primary-action relative inline-flex min-h-13 items-center justify-center gap-2 overflow-hidden rounded-full bg-[#392631] px-7 text-sm font-bold text-white shadow-[0_14px_32px_rgba(57,38,49,0.2)] transition duration-300 hover:-translate-y-1 hover:bg-[#533847] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#392631]"
                    >
                        اكتشفي المجموعة <ArrowDownRight class="size-4" />
                    </a>
                    <button
                        v-if="currentProduct"
                        type="button"
                        class="inline-flex min-h-13 items-center justify-center rounded-full border border-[#caa5b1] bg-white/70 px-7 text-sm font-bold text-[#392631] backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-[#a45f79] hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a45f79]"
                        @click="emit('open', currentProduct)"
                    >
                        عرض المنتج
                    </button>
                </div>
            </div>
            <div
                class="hero-stage relative mx-auto flex min-h-[500px] w-full max-w-[700px] items-center justify-center sm:min-h-[610px] lg:min-h-[620px] xl:min-h-[660px]"
                aria-roledescription="carousel"
                aria-label="مختارات منتجات ناتي"
                @mouseenter="pointerInside = true"
                @mouseleave="pointerInside = false"
                @focusin="focusInside = true"
                @focusout="handleFocusOut"
            >
                <!-- Portrait éditorial en arrière-plan, produit mis en avant au premier plan. -->
                <img
                    src="/images/nate-doctor-cutout.png"
                    alt="Ambassadrice NATÉ en blouse blanche"
                    class="hero-portrait pointer-events-none absolute bottom-0 left-[-62%] z-[7] block w-[138%] max-w-none object-contain drop-shadow-[0_20px_24px_rgba(57,38,49,0.11)] min-[375px]:left-[-58%] min-[375px]:w-[132%] sm:bottom-[0.5rem] sm:left-[-28%] sm:w-[88%] sm:max-w-[620px] lg:bottom-[1.5rem] lg:left-[-17%] lg:w-[88%] lg:max-w-[620px] lg:drop-shadow-[0_28px_30px_rgba(57,38,49,0.13)] xl:left-[-19%] xl:w-[92%] xl:max-w-[650px]"
                    loading="eager"
                    decoding="async"
                    aria-hidden="true"
                />
                <span
                    class="stage-glow absolute top-[44%] left-[64%] size-[61%] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#f1ced8]/55 blur-3xl"
                />
                <span
                    class="hero-orbit absolute top-[44%] left-[64%] size-[61%] -translate-x-1/2 -translate-y-1/2 rounded-full border border-[#b86f87]/20"
                />
                <span
                    class="hero-orbit hero-orbit-delay absolute top-[44%] left-[64%] size-[45%] -translate-x-1/2 -translate-y-1/2 rounded-full border border-white/90"
                />
                <span class="hero-spark hero-spark-one" /><span
                    class="hero-spark hero-spark-two"
                />
                <div
                    class="pointer-events-none absolute inset-0 z-[12] hidden lg:block"
                    aria-hidden="true"
                >
                    <Sparkles
                        class="hero-twinkle hero-twinkle-one size-7 text-[#b86f87]"
                    />
                    <Sparkles
                        class="hero-twinkle hero-twinkle-two size-5 text-[#c59b68]"
                    />
                    <Sparkles
                        class="hero-twinkle hero-twinkle-three size-4 text-[#a45f79]"
                    />
                    <span class="hero-light-dot hero-light-dot-one" />
                    <span class="hero-light-dot hero-light-dot-two" />
                </div>
                <Transition :name="reducedMotion ? '' : 'hero-product'">
                    <article
                        v-if="currentProduct"
                        :key="currentProduct.id"
                        class="absolute inset-0 flex items-center justify-center"
                        aria-live="polite"
                        aria-atomic="true"
                    >
                        <button
                            type="button"
                            class="group flex h-full w-full items-center justify-center pt-14 pr-0 pb-28 pl-[34%] sm:pt-16 sm:pr-4 sm:pb-32 sm:pl-[36%] lg:pr-2 lg:pb-36 lg:pl-[46%]"
                            :aria-label="`عرض ${currentProduct.name}`"
                            @click="emit('open', currentProduct)"
                        >
                            <span
                                class="product-caption absolute top-2 right-[2%] z-20 max-w-[78%] rounded-full border border-[#dec4cc] bg-white/75 px-4 py-2.5 text-right text-xs text-[#392631] shadow-[0_12px_35px_rgba(57,38,49,0.08)] backdrop-blur-md sm:top-7 sm:right-[4%] sm:max-w-[58%] lg:max-w-[52%]"
                            >
                                <span
                                    class="block text-[9px] font-bold tracking-[0.18em] text-[#8c4f65] uppercase"
                                    >المنتج المختار</span
                                >
                                <span
                                    class="mt-1 line-clamp-2 block font-medium"
                                    >{{ currentProduct.name }}</span
                                >
                            </span>
                            <img
                                :src="currentProduct.main_image!"
                                :alt="currentProduct.name"
                                class="hero-product-image relative z-10 transition duration-700 group-hover:scale-[1.045]"
                                :class="
                                    currentProduct.main_image_mode ===
                                    'editorial'
                                        ? 'h-[250px] w-full rounded-[1.5rem] object-cover shadow-[0_30px_65px_rgba(57,38,49,0.18)] ring-1 ring-white/80 sm:h-[360px] lg:h-[330px]'
                                        : 'h-[270px] w-full object-contain drop-shadow-[0_30px_24px_rgba(73,42,56,0.22)] sm:h-[390px] lg:h-[345px] xl:h-[380px]'
                                "
                                :loading="currentIndex === 0 ? 'eager' : 'lazy'"
                                :fetchpriority="
                                    currentIndex === 0 ? 'high' : 'auto'
                                "
                            />
                            <span
                                class="product-price absolute right-[3%] bottom-[2.2rem] z-20 inline-flex items-center gap-3 rounded-[1.35rem] border border-[#dcb8c4] bg-[#fffaf8]/95 px-4 py-2.5 shadow-[0_12px_28px_rgba(95,53,70,0.12)] backdrop-blur-md sm:right-[5%] sm:bottom-[4.5rem] lg:right-[7%] lg:bottom-[7.2rem]"
                            >
                                <span
                                    class="size-2 rounded-full bg-[#b86f87] shadow-[0_0_0_5px_rgba(184,111,135,0.12)]"
                                />
                                <span class="flex flex-col items-start">
                                    <span
                                        class="text-[9px] leading-none font-bold tracking-[0.08em] text-[#8d6b78]"
                                        >السعر</span
                                    >
                                    <span
                                        class="mt-1 text-sm font-black text-[#8f4c65]"
                                        dir="ltr"
                                    >
                                        {{
                                            Number(currentProduct.price) > 0
                                                ? formatMoney(
                                                      currentProduct.price,
                                                  )
                                                : 'السعر قريباً'
                                        }}
                                    </span>
                                </span>
                            </span>
                        </button>
                    </article>
                    <div
                        v-else
                        key="empty"
                        class="relative z-10 flex max-w-sm flex-col items-center text-center"
                    >
                        <span class="store-display text-8xl text-[#b86f87]/35"
                            >N.</span
                        >
                        <p class="store-display mt-5 text-2xl text-[#4e3944]">
                            المختارات قادمة قريباً
                        </p>
                        <p class="mt-2 text-sm leading-6 text-[#806a73]">
                            ستظهر المنتجات هنا فور توفر صورها.
                        </p>
                    </div>
                </Transition>
            </div>
        </div>
    </section>
</template>
<style scoped>
.hero-noise {
    background-image: radial-gradient(
        rgb(184 111 135 / 18%) 0.65px,
        transparent 0.65px
    );
    background-size: 21px 21px;
}
.hero-stage {
    perspective: 1100px;
}
.hero-portrait {
    -webkit-mask-image: linear-gradient(to bottom, #000 86%, transparent 100%);
    mask-image: linear-gradient(to bottom, #000 86%, transparent 100%);
    transform-origin: bottom center;
    animation: portrait-breathe 6s ease-in-out infinite;
}
.hero-primary-action::after {
    position: absolute;
    top: -60%;
    left: -35%;
    width: 24%;
    height: 220%;
    content: '';
    background: linear-gradient(
        90deg,
        transparent,
        rgb(255 255 255 / 48%),
        transparent
    );
    transform: rotate(18deg);
    animation: button-shimmer 4.8s ease-in-out infinite;
}
.petal {
    position: absolute;
    width: 1.15rem;
    height: 1.7rem;
    border-radius: 90% 10% 90% 10%;
    background: linear-gradient(
        145deg,
        rgb(216 145 168 / 32%),
        rgb(255 255 255 / 12%)
    );
    filter: blur(0.2px);
    opacity: 0;
    animation: petal-fall 11s linear infinite;
}
.petal-one {
    top: -8%;
    left: 8%;
    animation-delay: -1s;
}
.petal-two {
    top: -12%;
    left: 32%;
    width: 0.8rem;
    height: 1.25rem;
    animation-delay: -6s;
    animation-duration: 13s;
}
.petal-three {
    top: -10%;
    left: 57%;
    animation-delay: -3.5s;
    animation-duration: 14s;
}
.petal-four {
    top: -8%;
    left: 78%;
    width: 0.7rem;
    height: 1.1rem;
    animation-delay: -8s;
    animation-duration: 12s;
}
.petal-five {
    top: -14%;
    left: 92%;
    animation-delay: -4.5s;
    animation-duration: 15s;
}
.hero-spark {
    position: absolute;
    z-index: 1;
    width: 0.65rem;
    height: 0.65rem;
    border-radius: 9999px;
    background: #c47992;
    box-shadow: 0 0 0 7px rgb(196 121 146 / 10%);
    animation: spark-drift 4s ease-in-out infinite;
}
.hero-spark-one {
    top: 22%;
    left: 12%;
}
.hero-spark-two {
    right: 8%;
    bottom: 32%;
    width: 0.4rem;
    height: 0.4rem;
    animation-delay: -2s;
}
.hero-twinkle {
    position: absolute;
    filter: drop-shadow(0 0 10px rgb(184 111 135 / 35%));
    animation: twinkle-float 3.8s ease-in-out infinite;
}
.hero-twinkle-one {
    top: 27%;
    right: 6%;
}
.hero-twinkle-two {
    top: 44%;
    left: 43%;
    animation-delay: -1.4s;
}
.hero-twinkle-three {
    right: 16%;
    bottom: 29%;
    animation-delay: -2.6s;
}
.hero-light-dot {
    position: absolute;
    width: 0.35rem;
    height: 0.35rem;
    border-radius: 9999px;
    background: #fff;
    box-shadow:
        0 0 0 4px rgb(255 255 255 / 28%),
        0 0 18px 7px rgb(184 111 135 / 24%);
    animation: light-pulse 2.8s ease-in-out infinite;
}
.hero-light-dot-one {
    top: 36%;
    right: 25%;
}
.hero-light-dot-two {
    right: 4%;
    bottom: 39%;
    animation-delay: -1.5s;
}
.product-caption,
.product-price {
    animation: detail-float 4.5s ease-in-out infinite;
}
.product-price {
    animation-delay: -2.2s;
}
.hero-reveal {
    animation: hero-reveal 720ms cubic-bezier(0.22, 1, 0.36, 1) both;
}
.hero-reveal-1 {
    animation-delay: 80ms;
}
.hero-reveal-2 {
    animation-delay: 150ms;
}
.hero-reveal-3 {
    animation-delay: 220ms;
}
.hero-reveal-4 {
    animation-delay: 290ms;
}
.hero-product-image {
    animation: product-float 5s ease-in-out infinite;
}
.hero-orbit {
    animation: orbit-breathe 6s ease-in-out infinite;
}
.hero-orbit-delay {
    animation-delay: -3s;
}
.stage-glow {
    animation: glow-breathe 4.5s ease-in-out infinite;
}
@keyframes hero-reveal {
    from {
        opacity: 0;
        transform: translateY(22px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@keyframes product-float {
    0%,
    100% {
        transform: translateY(0) rotate(-0.5deg);
    }
    50% {
        transform: translateY(-12px) rotate(0.5deg);
    }
}
@keyframes portrait-breathe {
    0%,
    100% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-5px) scale(1.008);
    }
}
@keyframes orbit-breathe {
    0%,
    100% {
        opacity: 0.55;
        scale: 1;
    }
    50% {
        opacity: 1;
        scale: 1.035;
    }
}
@keyframes glow-breathe {
    0%,
    100% {
        opacity: 0.55;
        scale: 0.96;
    }
    50% {
        opacity: 0.9;
        scale: 1.08;
    }
}
@keyframes spark-drift {
    0%,
    100% {
        opacity: 0.45;
        transform: translateY(0) scale(0.8);
    }
    50% {
        opacity: 1;
        transform: translateY(-14px) scale(1);
    }
}
@keyframes twinkle-float {
    0%,
    100% {
        opacity: 0.35;
        transform: translateY(5px) rotate(-8deg) scale(0.78);
    }
    45% {
        opacity: 1;
        transform: translateY(-7px) rotate(8deg) scale(1.12);
    }
    70% {
        opacity: 0.65;
        transform: translateY(-3px) rotate(2deg) scale(0.92);
    }
}
@keyframes light-pulse {
    0%,
    100% {
        opacity: 0.25;
        transform: scale(0.7);
    }
    50% {
        opacity: 0.95;
        transform: scale(1.15);
    }
}
@keyframes button-shimmer {
    0%,
    58% {
        left: -35%;
    }
    78%,
    100% {
        left: 125%;
    }
}
@keyframes petal-fall {
    0% {
        opacity: 0;
        transform: translate3d(0, -2rem, 0) rotate(0deg);
    }
    12% {
        opacity: 0.55;
    }
    78% {
        opacity: 0.35;
    }
    100% {
        opacity: 0;
        transform: translate3d(5rem, 52rem, 0) rotate(420deg);
    }
}
@keyframes detail-float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}
.hero-product-enter-active,
.hero-product-leave-active {
    transition:
        opacity 600ms ease,
        transform 600ms cubic-bezier(0.22, 1, 0.36, 1);
}
.hero-product-enter-from {
    opacity: 0;
    transform: translateY(18px);
}
.hero-product-leave-to {
    opacity: 0;
    transform: translateY(-12px);
}
.hero-copy-enter-active,
.hero-copy-leave-active {
    transition:
        opacity 500ms ease,
        transform 600ms cubic-bezier(0.22, 1, 0.36, 1),
        filter 500ms ease;
}
.hero-copy-enter-from {
    opacity: 0;
    filter: blur(5px);
    transform: translateY(14px);
}
.hero-copy-leave-to {
    opacity: 0;
    filter: blur(3px);
    transform: translateY(-10px);
}
@media (prefers-reduced-motion: reduce) {
    .hero-product-enter-active,
    .hero-product-leave-active,
    .hero-copy-enter-active,
    .hero-copy-leave-active,
    .hero-reveal,
    .hero-portrait,
    .hero-product-image,
    .hero-orbit,
    .stage-glow,
    .hero-spark,
    .hero-twinkle,
    .hero-light-dot,
    .petal,
    .hero-primary-action::after,
    .product-caption,
    .product-price {
        animation: none;
        transition: none;
    }
}
</style>
