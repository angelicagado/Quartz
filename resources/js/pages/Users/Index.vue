<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Edit,
    Plus,
    Search,
    Shield,
    Trash2,
    User,
    Users,
    X,
} from '@lucide/vue';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface UserItem {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
}

interface PaginatedUsers {
    data: UserItem[];
    links: { url: string | null; label: string; active: boolean }[];
    meta: {
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
    };
}

const props = defineProps<{
    users: PaginatedUsers;
    filters?: { search?: string; role?: string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Users', href: '/users' }],
    },
});

const search = ref(props.filters?.search ?? '');
const roleFilter = ref(props.filters?.role ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch([search, roleFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/users',
            { search: search.value, role: roleFilter.value },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 300);
});

function deleteUser(id: number) {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/users/${id}`);
    }
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

const roleConfig: Record<
    string,
    { label: string; classes: string; gradient: string }
> = {
    super_admin: {
        label: 'Super Admin',
        classes:
            'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200 dark:border-rose-800',
        gradient: 'from-rose-500 to-red-600',
    },
    admin: {
        label: 'Admin',
        classes:
            'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800',
        gradient: 'from-purple-500 to-violet-600',
    },
    event_organizer: {
        label: 'Event Organizer',
        classes:
            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800',
        gradient: 'from-blue-500 to-indigo-600',
    },
    participant: {
        label: 'Participant',
        classes:
            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 border-green-200 dark:border-green-800',
        gradient: 'from-green-500 to-teal-600',
    },
};

function getRoleConfig(role: string) {
    return (
        roleConfig[role] ?? {
            label: role,
            classes:
                'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
            gradient: 'from-gray-400 to-gray-500',
        }
    );
}

function getInitialGradient(role: string): string {
    return getRoleConfig(role).gradient;
}
</script>

<template>
    <Head title="Users" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1
                    class="font-serif text-2xl font-bold tracking-tight text-foreground"
                >
                    Users
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Manage user accounts and role assignments.
                </p>
            </div>
            <Link href="/users/create">
                <Button
                    class="gap-2 bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-md hover:from-violet-700 hover:to-indigo-700"
                >
                    <Plus class="size-4" />
                    Create User
                </Button>
            </Link>
        </div>

        <!-- Filters -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <Search
                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    placeholder="Search users..."
                    class="pl-9"
                />
            </div>
            <select
                v-model="roleFilter"
                class="h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus:ring-2 focus:ring-ring/50 focus:outline-none"
            >
                <option value="">All Roles</option>
                <option value="super_admin">Super Admin</option>
                <option value="admin">Admin</option>
                <option value="event_organizer">Event Organizer</option>
                <option value="participant">Participant</option>
            </select>
        </div>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-xl border border-border bg-card shadow-xs"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/40">
                            <th
                                class="px-4 py-3 text-left font-semibold text-muted-foreground"
                            >
                                Name
                            </th>
                            <th
                                class="px-4 py-3 text-left font-semibold text-muted-foreground"
                            >
                                Email
                            </th>
                            <th
                                class="px-4 py-3 text-left font-semibold text-muted-foreground"
                            >
                                Role
                            </th>
                            <th
                                class="px-4 py-3 text-left font-semibold text-muted-foreground"
                            >
                                Joined
                            </th>
                            <th
                                class="px-4 py-3 text-right font-semibold text-muted-foreground"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <template v-if="users.data.length > 0">
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="group transition-colors hover:bg-muted/30"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            :class="[
                                                'flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-xs font-bold text-white',
                                                getInitialGradient(user.role),
                                            ]"
                                        >
                                            {{
                                                user.name
                                                    .charAt(0)
                                                    .toUpperCase()
                                            }}
                                        </div>
                                        <span
                                            class="font-medium text-foreground"
                                            >{{ user.name }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ user.email }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium',
                                            getRoleConfig(user.role).classes,
                                        ]"
                                    >
                                        {{ getRoleConfig(user.role).label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ formatDate(user.created_at) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Link :href="`/users/${user.id}/edit`">
                                            <Button
                                                variant="ghost"
                                                size="icon-sm"
                                                class="opacity-0 transition-opacity group-hover:opacity-100"
                                            >
                                                <Edit class="size-4" />
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            class="text-destructive opacity-0 transition-opacity group-hover:opacity-100 hover:bg-destructive/10 hover:text-destructive"
                                            @click="deleteUser(user.id)"
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="5" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div
                                        class="flex size-16 items-center justify-center rounded-2xl bg-muted"
                                    >
                                        <Users
                                            class="size-8 text-muted-foreground/50"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="font-semibold text-foreground"
                                        >
                                            No users found
                                        </p>
                                        <p
                                            class="mt-1 text-sm text-muted-foreground"
                                        >
                                            {{
                                                search || roleFilter
                                                    ? 'Try adjusting your filters.'
                                                    : 'Create your first user account.'
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="users.meta && users.meta.last_page > 1"
                class="flex items-center justify-between border-t border-border px-4 py-3"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ users.meta.from }}–{{ users.meta.to }} of
                    {{ users.meta.total }} users
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in users.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url" preserve-state>
                            <Button
                                variant="ghost"
                                size="sm"
                                :class="
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : ''
                                "
                                v-html="link.label"
                            />
                        </Link>
                        <Button
                            v-else
                            variant="ghost"
                            size="sm"
                            disabled
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
