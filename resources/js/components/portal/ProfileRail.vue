<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { CalendarDays, Mail, Phone, Settings2 } from '@lucide/vue';
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getInitials } from '@/composables/useInitials';

const page = usePage();
const user = computed(() => page.props.auth.user as any);
const profile = computed(() => user.value?.profile ?? null);
const avatarUrl = computed<string | null>(() => profile.value?.avatar ?? null);

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <aside class="hidden lg:block">
        <div class="sticky top-20 flex flex-col">
            <Avatar
                class="h-48 w-48 max-w-full border border-border text-4xl"
            >
                <AvatarImage
                    v-if="avatarUrl"
                    :src="avatarUrl"
                    :alt="user?.name"
                />
                <AvatarFallback
                    class="bg-violet-100 font-bold text-violet-700 dark:bg-violet-900/40 dark:text-violet-300"
                >
                    {{ getInitials(user?.name) }}
                </AvatarFallback>
            </Avatar>

            <h2 class="mt-4 font-serif text-xl font-bold text-foreground">
                {{ user?.name }}
            </h2>

            <p
                v-if="profile?.bio"
                class="mt-2 text-sm leading-relaxed text-muted-foreground"
            >
                {{ profile.bio }}
            </p>

            <div class="mt-4 space-y-2.5">
                <div
                    class="flex items-center gap-2.5 text-sm text-muted-foreground"
                >
                    <Mail class="size-4 shrink-0" />
                    <span class="truncate">{{ user?.email }}</span>
                </div>
                <div
                    v-if="profile?.phone"
                    class="flex items-center gap-2.5 text-sm text-muted-foreground"
                >
                    <Phone class="size-4 shrink-0" />
                    <span class="truncate">{{ profile.phone }}</span>
                </div>
                <div
                    v-if="profile?.birthdate"
                    class="flex items-center gap-2.5 text-sm text-muted-foreground"
                >
                    <CalendarDays class="size-4 shrink-0" />
                    <span>{{ formatDate(profile.birthdate) }}</span>
                </div>
            </div>

            <Link
                href="/portal/profile"
                class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
            >
                <Settings2 class="size-4" />
                Edit Profile
            </Link>
        </div>
    </aside>
</template>
