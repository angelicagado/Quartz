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
                        src="/images/quartzlogo.png"
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

                <!-- Desktop: avatar dropdown menu -->
                <div class="hidden items-center gap-3 md:flex">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <button
                                class="flex items-center gap-2 rounded-full p-0.5 transition-colors hover:bg-slate-800"
                            >
                                <Avatar class="h-9 w-9 border border-slate-700">
                                    <AvatarImage
                                        v-if="avatarUrl"
                                        :src="avatarUrl"
                                        :alt="user?.name"
                                    />
                                    <AvatarFallback
                                        class="bg-slate-800 text-sm font-semibold text-[#d4af37]"
                                    >
                                        {{ getInitials(user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <ChevronDown class="h-4 w-4 text-slate-400" />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <DropdownMenuLabel>
                                <div class="flex flex-col">
                                    <span class="truncate font-medium">{{
                                        user?.name
                                    }}</span>
                                    <span
                                        class="truncate text-xs font-normal text-muted-foreground"
                                        >{{ user?.email }}</span
                                    >
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link
                                    href="/portal/profile"
                                    class="flex w-full cursor-pointer items-center"
                                >
                                    <User class="mr-2 h-4 w-4" />
                                    Profile
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                class="cursor-pointer text-rose-500 focus:text-rose-500"
                                @select="showLogoutConfirm = true"
                            >
                                <LogOut class="mr-2 h-4 w-4" />
                                Log out
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <!-- Mobile: avatar (-> profile) + hamburger -->
                <div class="flex items-center gap-2 md:hidden">
                    <Link
                        href="/portal/profile"
                        class="rounded-full p-0.5 transition-colors hover:bg-slate-800"
                    >
                        <Avatar class="h-9 w-9 border border-slate-700">
                            <AvatarImage
                                v-if="avatarUrl"
                                :src="avatarUrl"
                                :alt="user?.name"
                            />
                            <AvatarFallback
                                class="bg-slate-800 text-sm font-semibold text-[#d4af37]"
                            >
                                {{ getInitials(user?.name) }}
                            </AvatarFallback>
                        </Avatar>
                    </Link>
                    <button
                        @click="isMobileOpen = true"
                        class="rounded-xl p-2 text-slate-400 transition-colors hover:bg-slate-800 hover:text-white"
                    >
                        <Menu class="h-6 w-6" />
                    </button>
                </div>
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

                <Link
                    href="/portal/profile"
                    @click="isMobileOpen = false"
                    class="mt-8 mb-6 flex items-center gap-3 border-b border-slate-800 pb-6"
                >
                    <Avatar class="h-12 w-12 border border-slate-700">
                        <AvatarImage
                            v-if="avatarUrl"
                            :src="avatarUrl"
                            :alt="user?.name"
                        />
                        <AvatarFallback
                            class="bg-slate-800 text-base font-semibold text-[#d4af37]"
                        >
                            {{ getInitials(user?.name) }}
                        </AvatarFallback>
                    </Avatar>
                    <div>
                        <p class="font-medium text-white">
                            {{ user?.name || 'Participant Name' }}
                        </p>
                        <p class="text-sm text-slate-400">
                            {{ user?.email || 'user@example.com' }}
                        </p>
                    </div>
                </Link>

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
                    <button
                        @click="
                            isMobileOpen = false;
                            showLogoutConfirm = true;
                        "
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-rose-500/10 px-4 py-3 font-medium text-rose-400 transition-colors hover:bg-rose-500/20"
                    >
                        <LogOut class="h-5 w-5" />
                        Sign Out
                    </button>
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

        <!-- Logout confirmation -->
        <Dialog v-model:open="showLogoutConfirm">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Log out?</DialogTitle>
                    <DialogDescription>
                        You'll need to sign in again to access your events, QR
                        passes, and certificates.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button
                        variant="secondary"
                        @click="showLogoutConfirm = false"
                    >
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmLogout">
                        <LogOut class="h-4 w-4" />
                        Log out
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    ChevronDown,
    Ticket,
    User,
    Menu,
    X,
    LogOut,
    Award,
} from '@lucide/vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { getInitials } from '@/composables/useInitials';
import { logout } from '@/routes';

const page = usePage();
const user = computed(() => page.props.auth.user as any);
const avatarUrl = computed<string | null>(
    () => user.value?.profile?.avatar ?? null,
);

const isMobileOpen = ref(false);
const showLogoutConfirm = ref(false);

function confirmLogout() {
    showLogoutConfirm.value = false;
    router.post(logout().url);
}

const navItems = [
    { name: 'My Events', href: '/portal/my-events', icon: Ticket },
    { name: 'Browse Events', href: '/portal/events', icon: CalendarDays },
    { name: 'Certificates', href: '/portal/certificates', icon: Award },
];

const isActive = (href: string) => page.url.startsWith(href);
</script>
