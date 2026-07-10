<script setup lang="ts">
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard, login, register } from '@/routes';
import { register as portalRegister } from '@/routes/portal';
import { downloadQrAsPng } from '@/lib/downloadQr';

const props = defineProps<{
    event: {
        id: number;
        title: string;
        description: string;
        start_time: string;
        end_time: string;
        registration_start_date: string;
        registration_end_date: string;
        registration_type: string;
        is_registered?: boolean;
        qr_code_url?: string;
        organizer: { name: string } | null;
    };
}>();

const page = usePage();
const dashboardUrl = computed(() =>
    page.props.currentTeam ? dashboard(page.props.currentTeam.slug).url : '/',
);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const form = useForm({});

const submitRegister = () => {
    form.post(portalRegister({ event: props.event.id }).url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="event.title" />

    <div
        class="min-h-screen overflow-x-hidden bg-surface-dim font-body-md text-on-surface antialiased selection:bg-primary-container selection:text-on-primary-container"
    >
        <!-- TopNavBar -->
        <nav
            class="docked full-width sticky top-0 z-50 border-b border-outline-variant/10 bg-surface-dim/80 shadow-md backdrop-blur-xl"
        >
            <div
                class="mx-auto flex h-20 max-w-container-max items-center justify-between px-margin-desktop max-md:px-margin-mobile"
            >
                <Link
                    href="/"
                    class="font-headline-md text-headline-md font-bold tracking-widest text-primary"
                >
                    QUARTZ
                </Link>
                <div
                    class="hidden items-center gap-gutter font-body-md text-body-md md:flex"
                >
                    <Link
                        class="text-primary transition-colors duration-300"
                        href="/events"
                        >Events</Link
                    >
                    <a
                        class="text-on-surface-variant transition-colors duration-300 hover:text-primary"
                        href="#"
                        >Certificates</a
                    >
                </div>
                <div class="hidden items-center gap-base md:flex">
                    <Link
                        v-if="!page.props.auth?.user"
                        class="font-body-md text-body-md text-on-surface-variant transition-colors duration-300 hover:text-primary"
                        :href="login().url"
                        >Login</Link
                    >
                    <Link
                        v-else
                        class="font-body-md text-body-md text-on-surface-variant transition-colors duration-300 hover:text-primary"
                        :href="dashboardUrl"
                        >Dashboard</Link
                    >
                </div>
                <!-- Mobile Menu Toggle -->
                <div class="md:hidden">
                    <span
                        class="material-symbols-outlined cursor-pointer text-3xl text-primary"
                        >menu</span
                    >
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="relative bg-surface-container-lowest py-16 min-h-[80vh]">
            <div class="mx-auto max-w-4xl px-margin-desktop max-md:px-margin-mobile">
                <div class="mb-6">
                    <Link href="/events" class="inline-flex items-center text-primary hover:underline font-medium">
                        <span class="material-symbols-outlined text-[20px] mr-1">arrow_back</span>
                        Back to Events
                    </Link>
                </div>
                
                <div class="glass-card overflow-hidden rounded-2xl transition-all duration-500 hover:border-primary/30">
                    <!-- Event Banner Placeholder -->
                    <div class="relative h-64 bg-gradient-to-br from-violet-500 via-purple-600 to-indigo-700">
                        <div class="absolute inset-0 opacity-20">
                            <div class="absolute top-8 right-8 size-32 rounded-full bg-white/30 blur-2xl" />
                            <div class="absolute bottom-8 left-8 size-24 rounded-full bg-white/20 blur-xl" />
                        </div>
                    </div>

                    <div class="p-8 md:p-12">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-8">
                            <div class="flex-1">
                                <h1 class="mb-4 font-headline-lg font-serif text-headline-lg text-on-surface">
                                    {{ event.title }}
                                </h1>
                                <div class="flex flex-wrap gap-4 text-sm text-on-surface-variant">
                                    <div v-if="event.organizer" class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[18px] text-primary">person</span>
                                        <span><strong>Organizer:</strong> {{ event.organizer.name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[18px] text-primary">event_available</span>
                                        <span><strong>Type:</strong> <span class="capitalize">{{ event.registration_type }}</span></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="shrink-0">
                                <div v-if="event.is_registered" class="mt-4 md:mt-0 overflow-hidden rounded-xl border border-primary/20 bg-surface-dim p-6 text-center shadow-sm">
                                    <h3 class="mb-4 text-lg font-bold text-on-surface">Your Ticket</h3>
                                    <div v-if="event.qr_code_url" class="flex flex-col items-center justify-center gap-4">
                                        <div class="relative inline-block rounded-xl bg-white p-3 shadow-sm">
                                            <img :src="event.qr_code_url" alt="QR Code Ticket" class="size-40 object-contain" />
                                        </div>
                                        <button @click="downloadQrAsPng(event.qr_code_url)" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-on-primary hover:bg-primary/90">
                                            <span class="material-symbols-outlined text-[18px]">download</span> Download
                                        </button>
                                    </div>
                                </div>
                                <div v-else>
                                    <button 
                                        @click="submitRegister"
                                        :disabled="form.processing || event.registration_type === 'closed'"
                                        class="inline-flex items-center justify-center rounded-lg bg-primary px-8 py-3 text-base font-semibold text-on-primary transition-colors hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/50 w-full md:w-auto disabled:opacity-50"
                                    >
                                        <span v-if="form.processing" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                        {{ event.registration_type === 'closed' ? 'Registration Closed' : 'Register Now' }}
                                    </button>
                                    <p v-if="!page.props.auth?.user" class="text-xs text-on-surface-variant text-center mt-2">
                                        Requires account login
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-3 gap-8 mb-8 border-y border-outline-variant/10 py-8">
                            <div class="space-y-1">
                                <div class="font-medium text-on-surface mb-2 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[20px] text-primary">calendar_clock</span>
                                    Event Timing
                                </div>
                                <div class="text-sm text-on-surface-variant"><strong>Start:</strong> {{ formatDate(event.start_time) }}</div>
                                <div class="text-sm text-on-surface-variant"><strong>End:</strong> {{ formatDate(event.end_time) }}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="font-medium text-on-surface mb-2 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[20px] text-primary">how_to_reg</span>
                                    Registration Period
                                </div>
                                <div class="text-sm text-on-surface-variant"><strong>Opens:</strong> {{ formatDate(event.registration_start_date) }}</div>
                                <div class="text-sm text-on-surface-variant"><strong>Closes:</strong> {{ formatDate(event.registration_end_date) }}</div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xl font-semibold mb-4 text-on-surface">About this Event</h3>
                            <div class="prose prose-invert max-w-none text-on-surface-variant whitespace-pre-wrap">
                                {{ event.description || 'No detailed description provided for this event.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-outline-variant/10 bg-surface-dim">
            <div
                class="mx-auto flex max-w-container-max flex-col items-center justify-between gap-base px-margin-desktop py-16 max-md:px-margin-mobile md:flex-row"
            >
                <div
                    class="font-headline-md text-headline-md font-bold tracking-widest text-primary"
                >
                    QUARTZ
                </div>
                <div
                    class="text-center font-body-md text-body-md text-on-surface-variant md:text-left"
                >
                    © 2026 Quartz Event Management.
                </div>
                <div class="flex gap-6 font-label-sm text-label-sm">
                    <a class="text-on-surface-variant transition-colors hover:text-primary" href="#">Privacy Policy</a>
                    <a class="text-on-surface-variant transition-colors hover:text-primary" href="#">Terms of Service</a>
                    <a class="text-on-surface-variant transition-colors hover:text-primary" href="#">Contact Us</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.glass-card {
    background: linear-gradient(
        135deg,
        rgba(18, 27, 46, 0.8) 0%,
        rgba(14, 19, 30, 0.9) 100%
    );
    border: 1px solid rgba(194, 199, 205, 0.1);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    backdrop-filter: blur(16px);
}
</style>
