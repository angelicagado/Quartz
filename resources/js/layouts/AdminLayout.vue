<template>
    <div
        class="flex h-screen overflow-hidden bg-slate-950 font-sans dark:bg-slate-950"
    >
        <div
            v-if="isMobileOpen"
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm transition-all duration-300 lg:hidden"
            @click="isMobileOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-[280px] transform flex-col overflow-hidden border-r border-slate-800 bg-slate-900 shadow-2xl transition-transform duration-300 ease-in-out lg:static lg:flex-shrink-0 lg:translate-x-0 lg:shadow-none"
            :class="isMobileOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div
                class="pointer-events-none absolute inset-0 z-0"
            ></div>
            <div
                class="pointer-events-none absolute top-0 right-0 z-0 h-64 w-64 rounded-bl-full bg-[#6D4AFF] opacity-[0.05] blur-3xl filter"
            ></div>
            <div
                class="pointer-events-none absolute bottom-0 left-0 z-0 h-64 w-64 rounded-tr-full bg-[#2563EB] opacity-[0.05] blur-3xl filter"
            ></div>

            <div
                class="relative z-10 flex w-full flex-col items-center justify-center pt-10 pb-6"
            >
                <img
                    src="/quartz-logo.png"
                    alt="QUARTZ"
                    class="h-auto w-[75%] max-w-[150px] object-contain brightness-110 drop-shadow-2xl"
                />
                <button
                    @click="isMobileOpen = false"
                    class="absolute top-4 right-4 rounded-lg bg-slate-800/50 p-1.5 text-slate-400 transition-colors hover:bg-slate-700 hover:text-white lg:hidden"
                >
                    <X class="h-5 w-5" />
                </button>
                <div
                    class="mt-8 hidden h-[1px] w-12 bg-slate-700/50 lg:block"
                ></div>
            </div>

            <nav
                class="hide-scrollbar relative z-10 mt-2 flex-1 space-y-1.5 overflow-y-auto px-4"
            >
                <Link
                    v-for="(item, index) in navItems"
                    :key="index"
                    :href="item.href"
                    class="group flex items-center gap-3.5 rounded-xl px-4 py-3.5 transition-all duration-300"
                    :class="[
                        isActive(item.href)
                            ? 'bg-[#6D4AFF]/15 font-medium text-[#6D4AFF] shadow-[inset_4px_0_0_0_#6D4AFF]'
                            : 'font-light text-slate-400 hover:bg-slate-800/50 hover:text-slate-200',
                    ]"
                >
                    <div
                        class="transition-colors duration-300"
                        :class="
                            isActive(item.href)
                                ? 'text-[#6D4AFF]'
                                : 'text-slate-500 group-hover:text-slate-300'
                        "
                    >
                        <component
                            :is="item.icon"
                            class="h-5 w-5 flex-shrink-0"
                        />
                    </div>
                    <span class="text-[15px] leading-tight tracking-wide">{{
                        item.name
                    }}</span>
                </Link>
            </nav>

            <div class="relative z-10 mt-auto mb-2 w-full p-4">
                <div
                    class="flex flex-col gap-2 rounded-2xl border border-slate-700/50 bg-slate-800/40 p-3 shadow-sm backdrop-blur-md"
                >
                    <div class="flex items-center gap-3 px-2 py-1">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-600/50 bg-slate-700/80 text-[#6D4AFF] shadow-inner"
                        >
                            <User class="h-4 w-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm font-medium text-slate-200"
                            >
                                {{ user?.name || 'Admin User' }}
                            </p>
                            <p
                                class="mt-0.5 truncate text-[11px] font-bold tracking-widest text-[#F59E0B] uppercase"
                            >
                                {{
                                    user?.role?.name?.replace('_', ' ') ||
                                    'Access Level'
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="my-1 h-[1px] w-full bg-slate-700/50"></div>

                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="group flex w-full items-center justify-center gap-2 rounded-xl border border-transparent px-3 py-2 text-slate-400 transition-all duration-300 hover:border-rose-500/20 hover:bg-rose-500/10 hover:text-white"
                    >
                        <LogOut
                            class="h-4 w-4 transition-colors group-hover:text-rose-400"
                        />
                        <span
                            class="text-sm font-medium transition-colors group-hover:text-rose-400"
                            >Sign Out</span
                        >
                    </Link>
                </div>
            </div>
        </aside>

        <div class="relative flex min-w-0 flex-1 flex-col overflow-hidden">
            <header
                class="sticky top-0 z-30 flex items-center justify-between border-b border-slate-200 bg-white/70 px-6 py-4 backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/70"
            >
                <div class="flex items-center gap-4">
                    <button
                        @click="isMobileOpen = true"
                        class="-ml-2 rounded-lg p-2 text-slate-600 transition-colors hover:bg-slate-100 lg:hidden dark:text-slate-400 dark:hover:bg-slate-800"
                    >
                        <Menu class="h-6 w-6" />
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        class="relative rounded-full p-2.5 text-slate-400 transition-all hover:bg-[#6D4AFF]/10 hover:text-[#6D4AFF] active:scale-95"
                    >
                        <Bell class="pointer-events-none h-5 w-5" />
                        <span
                            class="absolute top-2 right-2 h-2 w-2 rounded-full border-2 border-white bg-rose-500 dark:border-slate-900"
                        ></span>
                    </button>
                </div>
            </header>

            <main
                class="hide-scrollbar flex-1 overflow-y-auto p-4 pb-24 md:p-8 lg:p-10"
            >
                <slot></slot>
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    CalendarDays,
    QrCode,
    FileText,
    Award,
    Menu,
    X,
    Bell,
    User,
    LogOut,
    Activity,
} from '@lucide/vue';

const page = usePage();
const user = computed(() => page.props.auth.user as any);

const isMobileOpen = ref(false);
const isSuperAdmin = computed(() => user.value?.role?.name === 'super_admin');

const navItems = computed(() => {
    return isSuperAdmin.value
        ? [
              {
                  name: 'Super Dashboard',
                  href: '/super-admin/dashboard',
                  icon: LayoutDashboard,
              },
              {
                  name: 'User Management',
                  href: '/super-admin/users',
                  icon: User,
              },
              {
                  name: 'Global Events',
                  href: '/super-admin/events',
                  icon: CalendarDays,
              },
              { name: 'Attendance Scanner', href: '/attendance', icon: QrCode },
              {
                  name: 'System Audit Logs',
                  href: '/super-admin/logs',
                  icon: Activity,
              },
          ]
        : [
              {
                  name: 'Admin Dashboard',
                  href: '/dashboard',
                  icon: LayoutDashboard,
              },
              {
                  name: 'Event Registration',
                  href: '/events',
                  icon: CalendarDays,
              },
              { name: 'Attendance Scanner', href: '/attendance', icon: QrCode },
              {
                  name: 'Live Attendance',
                  href: '/live-attendance',
                  icon: Activity,
              },
              { name: 'Evaluations', href: '/evaluations', icon: FileText },
              { name: 'Certificates', href: '/certificates', icon: Award },
          ];
});

const isActive = (href: string) => {
    const currentUrl = page.url;
    if (href === '/dashboard' && currentUrl === '/dashboard') return true;
    if (currentUrl.startsWith(href) && href !== '/dashboard') return true;
    return false;
};
</script>
