<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, Clock, QrCode } from '@lucide/vue';
import ParticipantLayout from '@/layouts/ParticipantLayout.vue';

interface Event {
    id: number;
    title: string;
    start_time: string;
    end_time: string;
    location?: string;
}

interface ParticipantData {
    id: number;
    status: string;
    user: { name: string; email: string };
    qr_code_url: string | null;
}

defineProps<{
    event: Event;
    participant: ParticipantData;
}>();

defineOptions({ layout: ParticipantLayout });

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}

function formatTime(dateStr: string) {
    return new Date(dateStr).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="`QR Code — ${event.title}`" />

    <div
        class="flex h-full flex-1 flex-col items-center justify-center gap-6 p-6"
    >
        <div class="w-full max-w-md">
            <!-- Back Link -->
            <Link
                href="/portal/events"
                class="mb-6 inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-4" />
                Back to Events
            </Link>

            <!-- Card -->
            <div
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-lg"
            >
                <!-- Gradient Header -->
                <div
                    class="relative bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 px-6 py-8 text-center"
                >
                    <div class="absolute inset-0 opacity-10">
                        <div
                            class="absolute top-0 right-0 size-32 rounded-full bg-white blur-3xl"
                        />
                        <div
                            class="absolute bottom-0 left-0 size-24 rounded-full bg-white blur-2xl"
                        />
                    </div>
                    <div class="relative">
                        <div
                            class="mb-2 flex items-center justify-center gap-2 text-sm text-white/70"
                        >
                            <QrCode class="size-4" />
                            <span>Event Ticket</span>
                        </div>
                        <h1
                            class="font-serif text-xl leading-snug font-bold text-white"
                        >
                            {{ event.title }}
                        </h1>
                    </div>
                </div>

                <!-- QR Code Section -->
                <div class="flex flex-col items-center gap-4 p-8">
                    <div v-if="participant.qr_code_url" class="relative">
                        <!-- Decorative border frame -->
                        <div
                            class="absolute -inset-3 -z-10 rounded-2xl bg-gradient-to-br from-violet-200 to-indigo-200 dark:from-violet-900/40 dark:to-indigo-900/40"
                        />
                        <img
                            :src="participant.qr_code_url"
                            :alt="`QR Code for ${participant.user.name}`"
                            class="size-56 rounded-xl bg-white object-contain p-2 shadow-md"
                        />
                    </div>
                    <div
                        v-else
                        class="flex size-56 items-center justify-center rounded-xl border-2 border-dashed border-muted bg-muted/30"
                    >
                        <div class="text-center">
                            <QrCode
                                class="mx-auto size-12 text-muted-foreground/40"
                            />
                            <p class="mt-2 text-sm text-muted-foreground">
                                QR Code pending...
                            </p>
                            <p class="text-xs text-muted-foreground/70">
                                Check your email
                            </p>
                        </div>
                    </div>

                    <!-- Participant Name -->
                    <div class="text-center">
                        <p class="text-xl font-bold text-foreground">
                            {{ participant.user.name }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ participant.user.email }}
                        </p>
                    </div>

                    <!-- Divider -->
                    <div class="w-full border-t border-dashed border-border" />

                    <!-- Event Details -->
                    <div class="w-full space-y-2.5">
                        <div class="flex items-start gap-3">
                            <CalendarDays
                                class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                            />
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Start
                                </p>
                                <p class="text-sm font-medium text-foreground">
                                    {{ formatDate(event.start_time) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Clock
                                class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                            />
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Time
                                </p>
                                <p class="text-sm font-medium text-foreground">
                                    {{ formatTime(event.start_time) }} –
                                    {{ formatTime(event.end_time) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions Footer -->
                <div
                    class="border-t border-border bg-muted/30 px-6 py-4 text-center"
                >
                    <p class="text-xs leading-relaxed text-muted-foreground">
                        📱 Show this QR code to the event organizer at the
                        entrance to record your attendance. Screenshot or save
                        this page for offline access.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
