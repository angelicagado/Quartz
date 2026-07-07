<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
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
        title: 'Log in to your account',
        description: 'Enter your email and password below to log in',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    teamInvitation?: TeamInvitationContext | null;
}>();
</script>

<template>
    <Head title="Log in" />

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
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label
                    for="email"
                    class="font-medium tracking-wide text-slate-700"
                >
                    Email address
                </Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@example.com"
                    class="h-12 rounded-xl border-slate-200 bg-slate-50 text-[15px] transition-all focus:border-[#C5A059] focus:ring-[#C5A059]"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center">
                    <Label
                        for="password"
                        class="font-medium tracking-wide text-slate-700"
                    >
                        Password
                    </Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="ml-auto text-sm font-medium text-[#C5A059] transition-colors hover:text-slate-900"
                        :tabindex="5"
                    >
                        Forgot password?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Password"
                    class="h-12 rounded-xl border-slate-200 bg-slate-50 text-[15px] transition-all focus:border-[#C5A059] focus:ring-[#C5A059]"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center space-x-3">
                <Checkbox
                    id="remember"
                    name="remember"
                    :tabindex="3"
                    class="data-[state=checked]:border-[#C5A059] data-[state=checked]:bg-[#C5A059]"
                />
                <Label for="remember" class="font-normal text-slate-600">
                    Remember me
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 h-12 w-full rounded-xl bg-slate-900 text-[15px] font-medium text-white shadow-md transition-all duration-300 hover:bg-[#C5A059] hover:shadow-lg active:scale-[0.98]"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" class="mr-2" />
                Sign In
            </Button>
        </div>

        <div class="mt-2 text-center text-[15px] text-slate-500">
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
                class="font-medium text-[#C5A059] transition-colors hover:text-slate-900"
                data-test="register-link"
            >
                Sign up
            </TextLink>
        </div>
    </Form>
</template>
