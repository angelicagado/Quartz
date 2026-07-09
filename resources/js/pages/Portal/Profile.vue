<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Camera, KeyRound, UserCog } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { getInitials } from '@/composables/useInitials';
import ParticipantLayout from '@/layouts/ParticipantLayout.vue';

defineProps<{
    passwordRules: string;
}>();

const page = usePage();
const user = computed(() => page.props.auth.user as any);
const profile = computed(() => user.value?.profile ?? null);

const profileForm = useForm({
    name: user.value?.name ?? '',
    email: user.value?.email ?? '',
    birthdate: (profile.value?.birthdate ?? '').toString().slice(0, 10),
    bio: profile.value?.bio ?? '',
    phone: profile.value?.phone ?? '',
    avatar: null as File | null,
});

const avatarPreview = ref<string | null>(profile.value?.avatar ?? null);

function onAvatarChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    profileForm.avatar = file;
    avatarPreview.value = file ? URL.createObjectURL(file) : profile.value?.avatar ?? null;
}

function submitProfile() {
    profileForm
        .transform((data) => ({ ...data, _method: 'patch' }))
        .post('/portal/profile', {
            forceFormData: true,
            preserveScroll: true,
        });
}

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submitPassword() {
    passwordForm.put('/settings/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => passwordForm.reset('password', 'password_confirmation'),
    });
}

defineOptions({ layout: ParticipantLayout });
</script>

<template>
    <Head title="My Profile" />

    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        <!-- Header -->
        <div>
            <div class="mb-1 flex items-center gap-2">
                <div
                    class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 shadow-sm"
                >
                    <UserCog class="size-4 text-white" />
                </div>
                <span
                    class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                    >My Profile</span
                >
            </div>
            <h1 class="font-serif text-3xl font-bold tracking-tight text-foreground">
                Account &amp; Profile
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Update your personal details and credentials.
            </p>
        </div>

        <!-- Profile info -->
        <form
            @submit.prevent="submitProfile"
            class="rounded-2xl border border-border bg-card p-6 shadow-xs"
        >
            <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                <div class="relative">
                    <Avatar class="h-24 w-24 border border-border">
                        <AvatarImage
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            :alt="profileForm.name"
                        />
                        <AvatarFallback
                            class="bg-violet-100 text-2xl font-bold text-violet-700 dark:bg-violet-900/40 dark:text-violet-300"
                        >
                            {{ getInitials(profileForm.name) }}
                        </AvatarFallback>
                    </Avatar>
                    <label
                        class="absolute -right-1 -bottom-1 flex size-8 cursor-pointer items-center justify-center rounded-full bg-violet-600 text-white shadow-md transition-colors hover:bg-violet-700"
                    >
                        <Camera class="size-4" />
                        <input
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="onAvatarChange"
                        />
                    </label>
                </div>
                <div class="flex-1 text-center sm:pt-2 sm:text-left">
                    <p class="font-medium text-foreground">Profile picture</p>
                    <p class="text-sm text-muted-foreground">
                        PNG or JPG, up to 2&nbsp;MB.
                    </p>
                    <InputError class="mt-1" :message="profileForm.errors.avatar" />
                </div>
            </div>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="profileForm.name" required />
                    <InputError :message="profileForm.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" v-model="profileForm.email" required />
                    <InputError :message="profileForm.errors.email" />
                </div>
                <div class="grid gap-2">
                    <Label for="birthdate">Birthdate</Label>
                    <Input id="birthdate" type="date" v-model="profileForm.birthdate" />
                    <InputError :message="profileForm.errors.birthdate" />
                </div>
                <div class="grid gap-2">
                    <Label for="phone">Phone</Label>
                    <Input id="phone" v-model="profileForm.phone" placeholder="Optional" />
                    <InputError :message="profileForm.errors.phone" />
                </div>
                <div class="grid gap-2 sm:col-span-2">
                    <Label for="bio">Bio</Label>
                    <textarea
                        id="bio"
                        v-model="profileForm.bio"
                        rows="3"
                        placeholder="Tell us a little about yourself..."
                        class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
                    />
                    <InputError :message="profileForm.errors.bio" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <Button type="submit" :disabled="profileForm.processing">
                    {{ profileForm.processing ? 'Saving...' : 'Save changes' }}
                </Button>
            </div>
        </form>

        <!-- Credentials -->
        <form
            @submit.prevent="submitPassword"
            class="rounded-2xl border border-border bg-card p-6 shadow-xs"
        >
            <div class="mb-5 flex items-center gap-2">
                <KeyRound class="size-5 text-muted-foreground" />
                <div>
                    <h2 class="font-serif font-semibold text-foreground">
                        Credentials
                    </h2>
                    <p class="text-xs text-muted-foreground">
                        Change your password.
                    </p>
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="grid gap-2 sm:col-span-2">
                    <Label for="current_password">Current password</Label>
                    <PasswordInput
                        id="current_password"
                        v-model="passwordForm.current_password"
                        autocomplete="current-password"
                    />
                    <InputError :message="passwordForm.errors.current_password" />
                </div>
                <div class="grid gap-2">
                    <Label for="password">New password</Label>
                    <PasswordInput
                        id="password"
                        v-model="passwordForm.password"
                        autocomplete="new-password"
                    />
                    <InputError :message="passwordForm.errors.password" />
                </div>
                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        v-model="passwordForm.password_confirmation"
                        autocomplete="new-password"
                    />
                    <InputError :message="passwordForm.errors.password_confirmation" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <Button type="submit" variant="outline" :disabled="passwordForm.processing">
                    {{ passwordForm.processing ? 'Updating...' : 'Update password' }}
                </Button>
            </div>
        </form>
    </div>
</template>
