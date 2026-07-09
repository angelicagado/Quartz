<template>
    <div class="flex min-h-screen bg-slate-950 font-sans dark:bg-slate-950">
        <div
            v-if="isSidebarOpen"
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
            @click="isSidebarOpen = false"
        ></div>

        <aside
            class="fixed top-0 left-0 z-50 flex h-screen w-72 flex-col border-r border-slate-800 bg-slate-900 transition-transform duration-300 ease-in-out lg:sticky"
            :class="
                isSidebarOpen
                    ? 'translate-x-0'
                    : '-translate-x-full lg:translate-x-0'
            "
        >
            <div
                class="relative flex h-20 items-center justify-between overflow-hidden border-b border-slate-800 px-6"
            >
                <div class="absolute inset-0 bg-[#d4af37]/5"></div>
                <img
                    src="/quartzlogo.png"
                    alt="QUARTZ"
                    class="relative z-10 h-10 w-auto object-contain brightness-110 drop-shadow-md"
                />
                <button
                    @click="isSidebarOpen = false"
                    class="p-2 text-slate-400 hover:text-white lg:hidden"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
                <div
                    class="mb-4 px-3 text-xs font-bold tracking-wider text-slate-500"
                >
                    ORGANIZER PANEL
                </div>
                <Link
                    v-for="(item, index) in navItems"
                    :key="index"
                    :href="item.href"
                    class="group relative flex items-center gap-3 overflow-hidden rounded-xl px-3 py-3 transition-all duration-200"
                    :class="[
                        isActive(item.href)
                            ? 'bg-[#1E293B] text-white shadow-[inset_2px_0_0_0_#d4af37]'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-white',
                    ]"
                >
                    <div
                        :class="
                            isActive(item.href)
                                ? 'text-[#d4af37]'
                                : 'text-slate-500 group-hover:text-slate-300'
                        "
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                    </div>
                    <span class="font-medium">{{ item.name }}</span>
                </Link>
            </div>

            <div class="border-t border-slate-800 p-4">
                <div
                    class="rounded-2xl border border-slate-700/50 bg-slate-800/50 p-4"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-600 bg-slate-700 text-[#d4af37]"
                        >
                            <span class="font-bold">{{
                                user?.name?.charAt(0) || 'O'
                            }}</span>
                        </div>
                        <div class="overflow-hidden">
                            <p
                                class="truncate text-sm font-semibold text-white"
                            >
                                {{ user?.name || 'Organizer Name' }}
                            </p>
                            <p class="truncate text-xs text-slate-400">
                                {{ user?.email || 'Staff User' }}
                            </p>
                        </div>
                    </div>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-2.5 text-sm font-medium text-rose-400 transition-all duration-300 hover:border-transparent hover:bg-rose-500 hover:text-white"
                    >
                        <LogOut class="h-4 w-4" />
                        Sign Out
                    </Link>
                </div>
            </div>
        </aside>

        <main class="flex h-screen min-w-0 flex-1 flex-col overflow-hidden">
            <header
                class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 shadow-sm lg:hidden dark:border-slate-800 dark:bg-slate-900"
            >
                <img
                    src="/quartzlogo.png"
                    alt="QUARTZ"
                    class="h-8 max-w-[150px] object-contain drop-shadow"
                    style="filter: invert(1)"
                />
                <button
                    @click="isSidebarOpen = true"
                    class="rounded-lg p-2 text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                >
                    <Menu class="h-6 w-6" />
                </button>
            </header>

            <div class="flex-1 overflow-auto p-4 md:p-6 lg:p-8">
                <slot></slot>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    CalendarDays,
    ScanLine,
    Users,
    Menu,
    X,
    LogOut,
    Activity,
} from '@lucide/vue';

const page = usePage();
const user = computed(() => page.props.auth.user as any);

const isSidebarOpen = ref(false);

const navItems = [
    {
        name: 'Organizer Dashboard',
        href: '/organizer/dashboard',
        icon: LayoutDashboard,
    },

    { name: 'Scan QR Check-in', href: '/attendance', icon: ScanLine },
    { name: 'Live Attendance', href: '/live-attendance', icon: Activity },
    { name: 'Attendees list', href: '/organizer/attendees', icon: Users },
];

const isActive = (href: string) => page.url.startsWith(href);
</script>
