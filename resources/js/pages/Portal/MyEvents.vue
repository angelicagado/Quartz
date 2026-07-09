<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Award,
    CalendarDays,
    CheckCircle2,
    FileText,
    QrCode,
    Ticket,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import ParticipantLayout from '@/layouts/ParticipantLayout.vue';
import ProfileRail from '@/components/portal/ProfileRail.vue';

interface MyEvent {
    id: number;
    title: string;
    description: string | null;
    start_time: string;
    end_time: string;
    attendance_type: string;
    status: 'registered' | 'attended' | 'completed';
    has_qr: boolean;
    evaluation_required: boolean;
    evaluation_available: boolean;
    evaluation_submitted: boolean;
    certificate_available: boolean;
}

defineProps<{
    events: MyEvent[];
}>();

defineOptions({ layout: ParticipantLayout });

const statusConfig: Record<string, { label: string; classes: string }> = {
    registered: {
        label: 'Registered',
        classes:
            'bg-blue-100/80 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
    },
    attended: {
        label: 'Attended',
        classes:
            'bg-amber-100/80 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
    },
    completed: {
        label: 'Completed',
        classes:
            'bg-green-100/80 text-green-700 dark:bg-green-900/50 dark:text-green-300',
    },
};

function formatDateRange(start: string, end: string): string {
    const s = new Date(start);
    const e = new Date(end);
    const opts: Intl.DateTimeFormatOptions = {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    };
    if (s.toDateString() === e.toDateString()) {
        return s.toLocaleDateString('en-US', opts);
    }
    return `${s.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} – ${e.toLocaleDateString('en-US', opts)}`;
}
</script>

<template>
    <Head title="My Events" />

    <div class="grid gap-6 p-4 sm:p-6 lg:grid-cols-[280px_minmax(0,1fr)]">
        <ProfileRail />
        <div class="flex min-w-0 flex-1 flex-col gap-6">
        <!-- Header -->
        <div>
            <div class="mb-1 flex items-center gap-2">
                <div
                    class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 shadow-sm"
                >
                    <Ticket class="size-4 text-white" />
                </div>
                <span
                    class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                    >My Events</span
                >
            </div>
            <h1
                class="font-serif text-3xl font-bold tracking-tight text-foreground"
            >
                Your Registrations
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Access your QR pass, evaluations, and certificates.
            </p>
        </div>

        <!-- Events -->
        <div v-if="events.length > 0" class="flex flex-col gap-4">
            <div
                v-for="event in events"
                :key="event.id"
                class="flex flex-col gap-4 rounded-2xl border border-border bg-card p-5 shadow-xs sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3
                            class="truncate font-serif font-semibold text-foreground"
                        >
                            {{ event.title }}
                        </h3>
                        <span
                            v-if="statusConfig[event.status]"
                            :class="[
                                'inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                statusConfig[event.status].classes,
                            ]"
                        >
                            {{ statusConfig[event.status].label }}
                        </span>
                    </div>
                    <div
                        class="mt-1.5 flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <CalendarDays class="size-3.5 shrink-0" />
                        <span>{{
                            formatDateRange(event.start_time, event.end_time)
                        }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <Link
                        v-if="event.has_qr"
                        :href="`/portal/events/${event.id}/qr`"
                    >
                        <Button variant="outline" size="sm">
                            <QrCode class="size-4" />
                            QR Pass
                        </Button>
                    </Link>

                    <Link
                        v-if="
                            event.evaluation_available &&
                            !event.evaluation_submitted
                        "
                        :href="`/portal/events/${event.id}/evaluation`"
                    >
                        <Button variant="outline" size="sm">
                            <FileText class="size-4" />
                            Evaluate
                        </Button>
                    </Link>
                    <span
                        v-else-if="event.evaluation_submitted"
                        class="inline-flex items-center gap-1 text-xs font-medium text-green-600 dark:text-green-400"
                    >
                        <CheckCircle2 class="size-3.5" />
                        Evaluated
                    </span>

                    <a
                        v-if="event.certificate_available"
                        :href="`/portal/events/${event.id}/certificate`"
                    >
                        <Button
                            size="sm"
                            class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white hover:from-violet-700 hover:to-indigo-700"
                        >
                            <Award class="size-4" />
                            Certificate
                        </Button>
                    </a>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center gap-4 py-20"
        >
            <div
                class="flex size-20 items-center justify-center rounded-3xl bg-gradient-to-br from-violet-100 to-indigo-100 dark:from-violet-900/30 dark:to-indigo-900/30"
            >
                <Ticket class="size-10 text-violet-500 dark:text-violet-400" />
            </div>
            <div class="text-center">
                <p class="text-xl font-semibold text-foreground">
                    No registrations yet
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Browse events and register to see them here.
                </p>
            </div>
            <Link href="/portal/events">
                <Button
                    class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white hover:from-violet-700 hover:to-indigo-700"
                >
                    Browse Events
                </Button>
            </Link>
        </div>
        </div>
    </div>
</template>
