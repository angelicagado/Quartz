<template>
    <div
        class="min-h-screen bg-slate-950 pb-20 font-sans md:pb-0 dark:bg-slate-950"
    >
        <header
            class="sticky top-0 z-40 border-b border-slate-800 bg-slate-900 shadow-md"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-2">
                    <img
                        src="/quartzlogo.png"
                        alt="QUARTZ"
                        class="h-8 w-auto object-contain brightness-110 drop-shadow-md"
                    />
                </div>

                <nav class="hidden items-center gap-2 md:flex">
                    <Link
                        v-for="(item, index) in navItems"
                        :key="index"
                        :href="item.href"
                        class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all"
                        :class="
                            isActive(item.href)
                                ? 'bg-[#d4af37]/15 text-[#d4af37]'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
                        "
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                        {{ item.name }}
                    </Link>
                </nav>

                <div class="hidden items-center gap-4 md:flex">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-[#d4af37]"
                        >
                            <User class="h-4 w-4" />
                        </div>
                    </div>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-800 hover:text-rose-400"
                    >
                        <LogOut class="h-5 w-5" />
                    </Link>
                </div>

                <button
                    @click="isMobileOpen = true"
                    class="rounded-xl p-2 text-slate-400 transition-colors hover:bg-slate-800 hover:text-white md:hidden"
                >
                    <Menu class="h-6 w-6" />
                </button>
            </div>
        </header>

        <div v-if="isMobileOpen" class="fixed inset-0 z-50 flex md:hidden">
            <div
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="isMobileOpen = false"
            ></div>

            <div
                class="relative ml-auto flex h-full w-4/5 max-w-sm animate-in flex-col border-l border-slate-800 bg-slate-900 p-6 shadow-2xl duration-300 slide-in-from-right"
            >
                <button
                    @click="isMobileOpen = false"
                    class="absolute top-5 right-5 rounded-xl bg-slate-800 p-2 text-slate-400 hover:text-white"
                >
                    <X class="h-5 w-5" />
                </button>

                <div
                    class="mt-8 mb-6 flex items-center gap-3 border-b border-slate-800 pb-6"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-[#d4af37]"
                    >
                        <User class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="font-medium text-white">
                            {{ user?.name || 'Participant Name' }}
                        </p>
                        <p class="text-sm text-slate-400">
                            {{ user?.email || 'user@example.com' }}
                        </p>
                    </div>
                </div>

                <nav class="flex flex-1 flex-col gap-2">
                    <Link
                        v-for="(item, index) in navItems"
                        :key="index"
                        :href="item.href"
                        @click="isMobileOpen = false"
                        class="flex items-center gap-3 rounded-xl px-4 py-3.5 text-sm font-medium transition-all"
                        :class="
                            isActive(item.href)
                                ? 'bg-[#d4af37]/15 text-[#d4af37]'
                                : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'
                        "
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                        {{ item.name }}
                    </Link>
                </nav>

                <div class="border-t border-slate-800 pt-6">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-rose-500/10 px-4 py-3 font-medium text-rose-400 transition-colors hover:bg-rose-500/20"
                    >
                        <LogOut class="h-5 w-5" />
                        Sign Out
                    </Link>
                </div>
            </div>
        </div>

        <main class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
            <slot></slot>
        </main>

        <nav
            class="pb-safe fixed right-0 bottom-0 left-0 z-40 flex items-center justify-around border-t border-slate-200 bg-white px-2 pt-2 shadow-[0_-4px_20px_rgba(0,0,0,0.05)] md:hidden dark:border-slate-800 dark:bg-slate-900"
        >
            <Link
                v-for="(item, index) in navItems"
                :key="index"
                :href="item.href"
                class="flex min-w-[64px] flex-col items-center justify-center rounded-xl p-2 transition-all"
                :class="
                    isActive(item.href)
                        ? 'text-[#d4af37]'
                        : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'
                "
            >
                <div
                    class="mb-1 rounded-full p-1 transition-all"
                    :class="isActive(item.href) ? 'bg-[#d4af37]/10' : ''"
                >
                    <component :is="item.icon" class="h-5 w-5" />
                </div>
                <span class="text-[10px] font-medium tracking-wide">{{
                    item.name
                }}</span>
            </Link>
        </nav>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    QrCode,
    User,
    Menu,
    X,
    LogOut,
    Award,
    FileText,
} from '@lucide/vue';

const page = usePage();
const user = computed(() => page.props.auth.user as any);

const isMobileOpen = ref(false);

const navItems = [
    { name: 'My Events', href: '/participant/events', icon: CalendarDays },
    { name: 'My QR Pass', href: '/participant/qr', icon: QrCode },
    { name: 'Evaluations', href: '/participant/evaluations', icon: FileText },
    { name: 'Certificates', href: '/participant/certificates', icon: Award },
];

const isActive = (href: string) => page.url.startsWith(href);
</script>
