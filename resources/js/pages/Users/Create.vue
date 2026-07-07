<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeft, User } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    roles: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Users', href: '/users' },
            { title: 'Create User', href: '/users/create' },
        ],
    },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'participant',
});

function submit() {
    form.post('/users');
}

const roleLabels: Record<string, string> = {
    super_admin: 'Super Admin',
    admin: 'Admin',
    event_organizer: 'Event Organizer',
    participant: 'Participant',
};
</script>

<template>
    <Head title="Create User" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center gap-4">
            <Link href="/users">
                <Button variant="ghost" size="icon-sm">
                    <ChevronLeft class="size-4" />
                </Button>
            </Link>
            <div>
                <h1
                    class="font-serif text-2xl font-bold tracking-tight text-foreground"
                >
                    Create User
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Add a new user account to the system.
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="max-w-xl">
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-xs"
            >
                <div
                    class="flex items-center gap-3 border-b border-border bg-muted/30 px-6 py-4"
                >
                    <div
                        class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600"
                    >
                        <User class="size-4 text-white" />
                    </div>
                    <div>
                        <h2 class="font-serif font-semibold text-foreground">
                            User Details
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            Name, email, password and role
                        </p>
                    </div>
                </div>

                <div class="space-y-5 p-6">
                    <div class="grid gap-2">
                        <Label for="name"
                            >Full Name
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="John Doe"
                            :class="{ 'border-destructive': form.errors.name }"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="email"
                            >Email Address
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="john@example.com"
                            :class="{ 'border-destructive': form.errors.email }"
                        />
                        <p
                            v-if="form.errors.email"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="role"
                            >Role <span class="text-destructive">*</span></Label
                        >
                        <select
                            id="role"
                            v-model="form.role"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus:border-ring focus:ring-2 focus:ring-ring/50 focus:outline-none"
                            :class="{ 'border-destructive': form.errors.role }"
                        >
                            <option
                                v-for="role in roles"
                                :key="role"
                                :value="role"
                            >
                                {{ roleLabels[role] ?? role }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.role"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.role }}
                        </p>
                    </div>

                    <div class="border-t border-border pt-5">
                        <p
                            class="mb-4 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Password
                        </p>
                        <div class="grid gap-4">
                            <div class="grid gap-2">
                                <Label for="password"
                                    >Password
                                    <span class="text-destructive"
                                        >*</span
                                    ></Label
                                >
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Min. 8 characters"
                                    :class="{
                                        'border-destructive':
                                            form.errors.password,
                                    }"
                                />
                                <p
                                    v-if="form.errors.password"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.password }}
                                </p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="password_confirmation"
                                    >Confirm Password
                                    <span class="text-destructive"
                                        >*</span
                                    ></Label
                                >
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Repeat password"
                                    :class="{
                                        'border-destructive':
                                            form.errors.password_confirmation,
                                    }"
                                />
                                <p
                                    v-if="form.errors.password_confirmation"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.password_confirmation }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Link href="/users">
                    <Button variant="outline" type="button">Cancel</Button>
                </Link>
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-md hover:from-violet-700 hover:to-indigo-700"
                >
                    {{ form.processing ? 'Creating...' : 'Create User' }}
                </Button>
            </div>
        </form>
    </div>
</template>
