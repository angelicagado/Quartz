<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    Clock,
    Download,
    QrCode,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import ParticipantLayout from '@/layouts/ParticipantLayout.vue';
import { downloadQrAsPng } from '@/lib/downloadQr';

interface Session {
    id: number;
    name: string;
    start_time: string;
    end_time: string;
    requires_checkout: boolean;
}

interface Event {
    id: number;
    title: string;
    description: string | null;
    start_time: string;
    end_time: string;
    registration_start_date: string | null;
    registration_end_date: string | null;
    registration_type: string;
    status: 'upcoming' | 'ongoing' | 'completed' | 'cancelled';
    participants_count: number;
    certificate_enabled: boolean;
    evaluation_required: boolean;
    is_registered: boolean;
    qr_code_url?: string;
    sessions: Session[];
}

const props = defineProps<{
    event: Event;
}>();

defineOptions({ layout: ParticipantLayout });

const statusConfig: Record<string, { label: string; classes: string }> = {
    upcoming: {
        label: 'Upcoming',
        classes: 'bg-blue-100/80 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
    },
    ongoing: {
        label: 'Live Now',
        classes: 'bg-green-100/80 text-green-700 dark:bg-green-900/50 dark:text-green-300',
    },
    completed: {
        label: 'Completed',
        classes: 'bg-gray-100/80 text-gray-600 dark:bg-gray-800/80 dark:text-gray-400',
    },
    cancelled: {
        label: 'Cancelled',
        classes: 'bg-red-100/80 text-red-700 dark:bg-red-900/50 dark:text-red-300',
    },
};

const canRegister = computed(
    () =>
        !props.event.is_registered &&
        props.event.registration_type !== 'closed' &&
        props.event.status !== 'completed' &&
        props.event.status !== 'cancelled',
);

const form = useForm({});

function register() {
    form.post(`/portal/events/${props.event.id}/register`, {
        preserveScroll: true,
    });
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}

function formatTime(dateStr: string): string {
    return new Date(dateStr).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="event.title" />

    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 sm:p-6">
        <!-- Back Link -->
        <Link
            href="/portal/events"
            class="inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            Back to Events
        </Link>

        <!-- Card -->
        <div
            class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
        >
            <!-- Gradient Header -->
            <div
                class="relative bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 px-6 py-10"
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
                    <div class="mb-3 flex items-center gap-2">
                        <span
                            v-if="statusConfig[event.status]"
                            :class="[
                                'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold backdrop-blur-sm',
                                statusConfig[event.status].classes,
                            ]"
                        >
                            <span
                                v-if="event.status === 'ongoing'"
                                class="mr-1.5 size-1.5 animate-pulse rounded-full bg-green-500"
                            />
                            {{ statusConfig[event.status].label }}
                        </span>
                        <span
                            v-if="event.is_registered"
                            class="inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-green-700 shadow-sm backdrop-blur-sm dark:bg-gray-900/90 dark:text-green-400"
                        >
                            <CheckCircle2 class="size-3" />
                            Registered
                        </span>
                    </div>
                    <h1
                        class="font-serif text-2xl leading-snug font-bold text-white sm:text-3xl"
                    >
                        {{ event.title }}
                    </h1>
                </div>
            </div>

            <!-- Body -->
            <div class="flex flex-col gap-6 p-6">
                <!-- Meta -->
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="flex items-start gap-3">
                        <CalendarDays
                            class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                        />
                        <div>
                            <p class="text-xs text-muted-foreground">Date</p>
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
                            <p class="text-xs text-muted-foreground">Time</p>
                            <p class="text-sm font-medium text-foreground">
                                {{ formatTime(event.start_time) }} –
                                {{ formatTime(event.end_time) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <Users
                            class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                        />
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Registered
                            </p>
                            <p class="text-sm font-medium text-foreground">
                                {{ event.participants_count }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h2
                        class="mb-2 text-xs font-bold tracking-widest text-muted-foreground uppercase"
                    >
                        About this event
                    </h2>
                    <p
                        class="text-sm leading-relaxed whitespace-pre-line text-foreground/90"
                    >
                        {{ event.description || 'No description provided.' }}
                    </p>
                </div>

                <!-- Sessions -->
                <div v-if="event.sessions.length > 0">
                    <h2
                        class="mb-2 text-xs font-bold tracking-widest text-muted-foreground uppercase"
                    >
                        Sessions
                    </h2>
                    <ul class="flex flex-col gap-2">
                        <li
                            v-for="session in event.sessions"
                            :key="session.id"
                            class="flex items-center justify-between gap-3 rounded-xl border border-border bg-muted/30 px-4 py-3"
                        >
                            <div>
                                <p class="text-sm font-medium text-foreground">
                                    {{ session.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatDate(session.start_time) }} ·
                                    {{ formatTime(session.start_time) }} –
                                    {{ formatTime(session.end_time) }}
                                </p>
                            </div>
                            <span
                                v-if="session.requires_checkout"
                                class="shrink-0 rounded-full bg-amber-100/80 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300"
                            >
                                Check-out required
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- Ticket Widget -->
                <div v-if="event.is_registered" class="mt-4 overflow-hidden rounded-xl border-2 border-primary/20 bg-primary/5 p-6 text-center dark:bg-primary/10">
                    <h3 class="mb-4 text-lg font-bold text-foreground">Your Event Ticket</h3>
                    
                    <div v-if="event.qr_code_url" class="flex flex-col items-center justify-center gap-4">
                        <div class="relative inline-block rounded-xl bg-white p-3 shadow-sm">
                            <img :src="event.qr_code_url" alt="Your QR Code Ticket" class="size-48 object-contain" />
                        </div>
                        <button @click="downloadQrAsPng(event.qr_code_url)" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                            <Download class="size-4" /> Download Ticket
                        </button>
                    </div>
                    <div v-else class="flex flex-col items-center gap-2">
                        <QrCode class="size-12 text-muted-foreground/40" />
                        <p class="text-sm text-muted-foreground">Generating your QR code...</p>
                    </div>
                </div>

                <!-- Actions -->
                <div v-else class="flex flex-wrap items-center gap-3 border-t border-border pt-5">
                    <Button
                        v-if="canRegister"
                        class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-sm hover:from-violet-700 hover:to-indigo-700"
                        @click="register"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="mr-2 size-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        Register for this event
                    </Button>

                    <p
                        v-if="event.registration_type === 'closed'"
                        class="text-sm text-muted-foreground"
                    >
                        Registration is closed for this event.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
