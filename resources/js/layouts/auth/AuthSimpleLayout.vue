<script setup lang="ts">
import { computed } from 'vue';

defineProps<{
    title?: string;
    description?: string;
}>();

const currentYear = computed(() => new Date().getFullYear());
</script>

<template>
    <div class="flex min-h-screen bg-[#F8F9FC] font-['Outfit']">
        <!-- Left Side - Image/Branding (Hidden on mobile) -->
        <div
            class="relative hidden flex-col items-center justify-center overflow-hidden bg-slate-900 p-12 lg:flex lg:w-1/2"
        >
            <div
                class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 opacity-90"
            ></div>

            <!-- Decorative Elements -->
            <div
                class="absolute top-0 right-0 h-96 w-96 rounded-bl-full bg-[#C5A059] opacity-[0.05] blur-3xl filter"
            ></div>
            <div
                class="absolute bottom-0 left-0 h-96 w-96 rounded-tr-full bg-[#C5A059] opacity-[0.05] blur-3xl filter"
            ></div>

            <div
                class="relative z-10 flex max-w-lg flex-col items-center space-y-8 text-center"
            >
                <img
                    src="/images/quartzlogo.png"
                    alt="QUARTZ"
                    class="w-[85%] max-w-[400px] object-contain drop-shadow-2xl"
                />
                <p
                    class="mt-8 text-lg leading-relaxed font-light tracking-wide text-slate-300"
                >
                    The premiere destination for professional event planning,
                    attendance tracking, and certificate management.
                </p>
            </div>
        </div>

        <!-- Right Side - Auth Form -->
        <div
            class="relative flex w-full flex-col items-center justify-center p-6 sm:p-12 lg:w-1/2"
        >
            <!-- Mobile Logo Only -->
            <div class="mb-8 flex justify-center lg:hidden">
                <img
                    src="/images/quartzlogo.png"
                    alt="QUARTZ"
                    class="w-48 object-contain"
                />
            </div>

            <div
                class="relative z-10 w-full max-w-md rounded-[2rem] border border-slate-100 bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:p-10"
            >
                <div class="mb-8 text-center">
                    <h1
                        class="mb-2 text-3xl font-semibold tracking-tight text-slate-900"
                    >
                        {{ title }}
                    </h1>
                    <p class="text-[15px] text-slate-500">
                        {{ description }}
                    </p>
                </div>

                <slot />
            </div>

            <!-- Footer -->
            <div class="absolute right-0 bottom-6 left-0 text-center">
                <p class="text-sm text-slate-400">
                    © {{ currentYear }} Quartz. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</template>
