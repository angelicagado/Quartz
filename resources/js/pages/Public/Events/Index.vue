<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard, login, register } from '@/routes';
import { show as publicEventsShow } from '@/routes/public/events';

const props = defineProps<{
    events: Array<{
        id: number;
        title: string;
        description: string;
        address: string | null;
        start_time: string;
        end_time: string;
        registration_start_date: string;
        registration_end_date: string;
        organizers: { name: string }[];
    }>;
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

const gradients = [
    'from-violet-500 via-purple-600 to-indigo-700',
    'from-blue-500 via-cyan-500 to-teal-600',
    'from-rose-500 via-pink-500 to-purple-600',
    'from-amber-500 via-orange-500 to-rose-500',
    'from-emerald-500 via-teal-500 to-cyan-600',
    'from-indigo-500 via-blue-500 to-cyan-500',
];

function getGradient(index: number): string {
    return gradients[index % gradients.length];
}
</script>

<template>
    <Head title="Events">
        <link
            href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
            rel="stylesheet"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

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
        <main class="relative bg-surface-container-lowest py-20 min-h-[80vh]">
            <div
                class="mx-auto max-w-container-max px-margin-desktop max-md:px-margin-mobile"
            >
                <div class="mb-12 text-center">
                    <h1
                        class="mb-4 font-headline-lg text-headline-lg text-on-surface max-md:font-headline-md max-md:text-headline-md"
                    >
                        Upcoming <span class="text-primary-container">Events</span>
                    </h1>
                    <p
                        class="mx-auto max-w-2xl font-body-lg text-body-lg text-on-surface-variant"
                    >
                        Discover and participate in upcoming events organized on our platform.
                    </p>
                </div>

                <div v-if="events.length === 0" class="text-center py-12">
                    <p class="text-on-surface-variant">No upcoming events available at the moment.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="(event, index) in events"
                        :key="event.id"
                        class="glass-card flex flex-col justify-between overflow-hidden rounded-xl transition-all duration-500 hover:border-primary/30"
                    >
                        <!-- Image / Gradient Placeholder -->
                        <div
                            :class="[
                                'relative h-40 bg-gradient-to-br',
                                getGradient(index),
                            ]"
                        >
                            <!-- Decorative elements -->
                            <div class="absolute inset-0 opacity-20">
                                <div class="absolute top-4 right-4 size-16 rounded-full bg-white/30 blur-xl" />
                                <div class="absolute bottom-4 left-4 size-12 rounded-full bg-white/20 blur-lg" />
                            </div>
                        </div>

                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="mb-2 font-headline-sm font-serif text-headline-sm text-on-surface">
                                {{ event.title }}
                            </h3>
                            <p class="mb-4 text-sm text-on-surface-variant line-clamp-3">
                                {{ event.description || 'No description provided.' }}
                            </p>
                            
                            
                            <div class="space-y-2 mb-6 flex-1">
                                <div class="flex items-start gap-2 text-sm text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[18px] text-primary mt-0.5">calendar_today</span>
                                    <div>
                                        <div><strong>Start:</strong> {{ formatDate(event.start_time) }}</div>
                                        <div><strong>End:</strong> {{ formatDate(event.end_time) }}</div>
                                    </div>
                                </div>
                                <div v-if="event.organizers?.length" class="flex items-center gap-2 text-sm text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[18px] text-primary">person</span>
                                    <span><strong>Organizers:</strong> {{ event.organizers.map(o => o.name).join(', ') }}</span>
                                </div>
                                <div v-if="event.address" class="flex items-center gap-2 text-sm text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[18px] text-primary">location_on</span>
                                    <span><strong>Location:</strong> {{ event.address }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-outline-variant/10 flex items-center justify-between p-6 pt-0">
                            <div class="text-xs text-on-surface-variant">
                                Reg. starts: <br/> {{ formatDate(event.registration_start_date) }}
                            </div>
                            <Link 
                                :href="publicEventsShow({ event: event.id }).url"
                                class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-on-primary transition-colors hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/50"
                            >
                                View Details
                            </Link>
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
                    <a
                        class="text-on-surface-variant transition-colors hover:text-primary"
                        href="#"
                        >Privacy Policy</a
                    >
                    <a
                        class="text-on-surface-variant transition-colors hover:text-primary"
                        href="#"
                        >Terms of Service</a
                    >
                    <a
                        class="text-on-surface-variant transition-colors hover:text-primary"
                        href="#"
                        >Contact Us</a
                    >
                    <a
                        class="text-on-surface-variant transition-colors hover:text-primary"
                        href="#"
                        >Resources</a
                    >
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
