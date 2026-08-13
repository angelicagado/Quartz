<script setup lang="ts">
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineProps<{
    title?: string;
    description?: string;
}>();

const currentYear = computed(() => new Date().getFullYear());
</script>

<template>
    <div class="flex min-h-screen bg-linear-to-bl from-purple-200 via-violet-200 to-white font-body-md">
        <!-- Left Side - Image/Branding (Hidden on mobile) -->
        <div
            class="relative hidden flex-col items-center justify-center overflow-hidden p-12 lg:flex lg:w-1/2"
        >
            <div
                class="absolute inset-0"
            ></div>

            <!-- Form area (vertically centered) -->
            <div class="flex flex-1 flex-col justify-center">
                <div class="mx-auto w-full max-w-[400px]">
                    <div v-if="title || description" class="mb-12">
                        <h1
                            v-if="title"
                            class="text-4xl font-bold tracking-tight text-slate-900"
                        >
                            {{ title }}
                        </h1>
                        <p
                            v-if="description"
                            class="mt-2 text-sm text-slate-500"
                        >
                            {{ description }}
                        </p>
                    </div>

            <div
                class="relative z-10 flex max-w-lg flex-col items-center space-y-8 text-center"
            >
                <img
                    src="/images/quartz-logo.png"
                    alt="QUARTZ"
                    class="w-[85%] max-w-[400px] object-contain drop-shadow-2xl"
                />
                <p
                    class="mt-8 text-lg leading-relaxed font-display-lg italic tracking-wide text-black"
                >
                    The premiere destination for professional event planning,
                    attendance tracking, and certificate management.
                </p>
            </div>
                </div>
            </div>
        </div>

        <!-- Right column — branding panel -->
        <div class="hidden p-3 lg:block">
            <div
                class="relative flex h-full flex-col overflow-hidden rounded-3xl bg-slate-950 p-12 text-white"
            >
                <!-- Background blurs (mirrors the landing hero) -->
                <div class="pointer-events-none absolute inset-0">
                    <div
                        class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-primary/30 blur-[120px]"
                    />
                    <div
                        class="absolute right-0 bottom-1/3 h-96 w-96 rounded-full bg-secondary/25 blur-[120px]"
                    />
                    <div
                        class="absolute -bottom-24 left-1/4 h-72 w-72 rounded-full bg-accent/15 blur-[100px]"
                    />
                </div>

                <!-- Content -->
                <div class="relative z-10 flex h-full flex-col">
                    <div class="h-12 w-12">
                        <AppLogoIcon />
                    </div>

                    <div class="mt-12">
                        <h2 class="font-display-lg text-3xl font-bold tracking-tight">
                            Welcome to
                            <span
                                class="bg-gradient-to-r from-violet-400 to-blue-400 bg-clip-text text-transparent"
                            >
                                Quartz
                            </span>
                        </h2>
                        <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-500">
                            QUARTZ is a web-based event management system that
                            automates Event registration, Attendance tracking,
                            Evaluations, and Rule-based certificate generation
                            and dissemination.
                        </p>
                    </div>

                    <!-- Reserved placeholder -->
                    <div
                        class="mt-auto h-64 w-full rounded-2xl bg-white/5 ring-1 ring-white/10"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
