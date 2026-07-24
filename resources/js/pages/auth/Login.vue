<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Lock, Mail } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TeamInvitationAlert from '@/components/TeamInvitationAlert.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import type { TeamInvitationContext } from '@/types';

defineOptions({
    layout: {
        title: 'Sign In',
        description: '',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    teamInvitation?: TeamInvitationContext | null;
}>();
</script>

<template>
    <Head title="Sign in" />

    <div
        v-if="status"
        class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50 py-3 text-center text-sm font-medium text-emerald-600"
    >
        {{ status }}
    </div>

    <TeamInvitationAlert
        v-if="teamInvitation"
        :invitation="teamInvitation"
        action="Log in"
    />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid">
            <div class="grid gap-3 mb-8">
                <Label
                    for="email"
                    class="font-body-lg font-medium tracking-wide text-slate-700"
                >
                    Email Address
                </Label>
                <div class="relative">
                    <Mail
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                    />
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="example@email.com"
                        class="h-10.5 border-slate-200 bg-slate-50 pl-10 text-[12px] transition-all focus:border-[#C5A059] focus:ring-[#C5A059]"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2 mb-4">
                <Label
                    for="password"
                    class="font-medium tracking-wide text-slate-700"
                >
                    Password
                </Label>
                <div class="relative">
                    <Lock
                        class="pointer-events-none absolute top-1/2 left-3 z-10 size-4 -translate-y-1/2 text-slate-400"
                    />
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="********"
                        class="h-10.5 border-slate-200 bg-slate-50 pl-10 text-[12px] transition-all focus:border-[#C5A059] focus:ring-[#C5A059]"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between mb-14">
                <div class="flex items-center space-x-2.5">
                    <Checkbox
                        id="remember"
                        name="remember"
                        :tabindex="3"
                        class="data-[state=checked]:border-primary data-[state=checked]:bg-primary"
                    />
                    <Label for="remember" class="font-normal text-slate-700">
                        Remember me
                    </Label>
                </div>
                <TextLink
                    v-if="canResetPassword"
                    :href="request()"
                    class="text-sm font-medium text-[#c19c56] decoration-0 transition-colors hover:text-slate-900"
                    :tabindex="5"
                >
                    Forgot password?
                </TextLink>
            </div>

            <Button
                type="submit"
                class="mt-4 h-12 w-full rounded-xl bg-primary text-[15px] font-medium text-primary-foreground shadow-lg shadow-primary/20 transition-all duration-300 hover:bg-primary/90 hover:shadow-primary/40 active:scale-[0.98]"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" class="mr-2" />
                Sign In
            </Button>
        </div>

        <div class="mt-12 text-center text-[15px] text-slate-500">
            Don't have an account?
            <TextLink
                :href="
                    register({
                        query: {
                            invitation: teamInvitation?.code,
                        },
                    })
                "
                :tabindex="5"
                class="font-medium text-[#C5A059] decoration-0 transition-colors hover:text-slate-900"
                data-test="register-link"
            >
                Sign Up
            </TextLink>
        </div>
    </Form>
</template>
