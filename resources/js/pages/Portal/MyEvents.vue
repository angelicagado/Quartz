<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Award,
    CalendarDays,
    CheckCircle2,
    FileText,
    QrCode,
    Search,
    Ticket,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import ParticipantLayout from '@/layouts/ParticipantLayout.vue';
import ProfileRail from '@/components/portal/ProfileRail.vue';
import MyEventsCalendar from '@/components/portal/MyEventsCalendar.vue';
import { computed, ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';

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

const props = defineProps<{
    events: MyEvent[];
}>();

defineOptions({ layout: ParticipantLayout });

type EventFilter = 'all' | 'upcoming' | 'done';

const filter = ref<EventFilter>('all');
const search = ref('');

/** An event is "done" once its end time has passed. */
function isDone(event: MyEvent): boolean {
    return new Date(event.end_time).getTime() < Date.now();
}

/** Events matching the current search term (title or description). */
const searchedEvents = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.events;
    }
    return props.events.filter(
        (event) =>
            event.title.toLowerCase().includes(term) ||
            (event.description ?? '').toLowerCase().includes(term),
    );
});

const filteredEvents = computed(() => {
    if (filter.value === 'upcoming') {
        return searchedEvents.value.filter((event) => !isDone(event));
    }
    if (filter.value === 'done') {
        return searchedEvents.value.filter((event) => isDone(event));
    }
    return searchedEvents.value;
});

function countFor(key: EventFilter): number {
    if (key === 'upcoming') {
        return searchedEvents.value.filter((event) => !isDone(event)).length;
    }
    if (key === 'done') {
        return searchedEvents.value.filter((event) => isDone(event)).length;
    }
    return searchedEvents.value.length;
}

const filterTabs: { key: EventFilter; label: string }[] = [
    { key: 'all', label: 'All' },
    { key: 'upcoming', label: 'Upcoming' },
    { key: 'done', label: 'Done' },
];

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

// Gradient palettes for cards (cycling)
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

function excerpt(text: string | null, length = 120): string {
    if (!text) return 'No description provided.';
    return text.length > length
        ? text.slice(0, length).trimEnd() + '...'
        : text;
}

const isProcessing = ref<Record<number, boolean>>({});

async function viewCertificate(eventId: number) {
    if (isProcessing.value[eventId]) return;
    isProcessing.value[eventId] = true;
    
    const newWindow = window.open('about:blank', '_blank');
    if (newWindow) {
        newWindow.document.write('<div style="display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;">Loading certificate...</div>');
    }
    
    try {
        const url = `/portal/events/${eventId}/certificate/view`;
        const response = await axios.get(url, { responseType: 'blob' });
        
        if (response.data.type && response.data.type.includes('text/html')) {
             throw new Error("Failed to load certificate.");
        }
        
        const blobUrl = window.URL.createObjectURL(new Blob([response.data], { type: response.data.type }));
        if (newWindow) {
            newWindow.location.href = blobUrl;
        } else {
            window.location.href = blobUrl;
        }
    } catch (error) {
        if (newWindow) newWindow.close();
        toast.error("We couldn't load your certificate. Please ensure you meet all requirements (like attending the event and completing the evaluation).");
    } finally {
        isProcessing.value[eventId] = false;
    }
}
</script>

<template>
    <Head title="My Events" />

    <div class="grid gap-6 p-4 sm:p-6 lg:grid-cols-[240px_minmax(0,1fr)] lg:gap-20">
        <!-- Left column: profile + calendar (sticky, desktop only) -->
        <div class="hidden lg:block">
            <div class="sticky top-20 flex flex-col gap-6">
                <ProfileRail />
                <MyEventsCalendar :events="events" />
            </div>
        </div>

        <div class="flex min-w-0 flex-1 flex-col gap-6">
        <!-- Calendar (mobile / tablet only — rail is hidden below lg) -->
        <MyEventsCalendar :events="events" class="lg:hidden" />

        <!-- Header -->
        <div>
            <div class="mb-2.5 flex items-center gap-2">
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

        <!-- Search + filter tabs -->
        <div
            v-if="events.length > 0"
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="relative w-full sm:max-w-xs">
                <Search
                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    placeholder="Search your events..."
                    class="border-2 bg-card pl-9 shadow-sm"
                />
            </div>

            <div
                class="flex w-fit items-center gap-1 rounded-xl border-2 border-border bg-card p-1 shadow-sm"
            >
                <button
                    v-for="tab in filterTabs"
                    :key="tab.key"
                    type="button"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                    :class="
                        filter === tab.key
                            ? 'bg-violet-600 text-white shadow-sm'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                    "
                    @click="filter = tab.key"
                >
                    {{ tab.label }}
                    <span class="ml-1 text-xs opacity-70">{{ countFor(tab.key) }}</span>
                </button>
            </div>
        </div>

        <!-- Events Grid -->
        <div v-if="filteredEvents.length > 0" class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="(event, index) in filteredEvents"
                :key="event.id"
                class="group relative flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-xs transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg"
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

                    <div class="absolute inset-0 flex items-center justify-center">
                        <CalendarDays class="size-16 text-white/30" />
                    </div>

                    <!-- Status Badge -->
                    <div class="absolute top-3 left-3">
                        <span
                            v-if="statusConfig[event.status]"
                            :class="[
                                'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold backdrop-blur-sm',
                                statusConfig[event.status].classes,
                            ]"
                        >
                            {{ statusConfig[event.status].label }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex flex-1 flex-col p-5">
                    <Link :href="`/portal/events/${event.id}`" class="before:absolute before:inset-0 before:z-0">
                        <h3
                            class="line-clamp-2 font-serif leading-snug font-semibold text-foreground transition-colors group-hover:text-violet-600 dark:group-hover:text-violet-400"
                        >
                            {{ event.title }}
                        </h3>
                    </Link>

                    <div
                        class="mt-2 flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <CalendarDays class="size-3.5 shrink-0" />
                        <span>{{
                            formatDateRange(event.start_time, event.end_time)
                        }}</span>
                    </div>

                    <p
                        class="mt-3 line-clamp-3 flex-1 text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ excerpt(event.description) }}
                    </p>

                    <div
                        class="relative z-10 mt-4 flex items-center justify-end gap-3 border-t border-border pt-4"
                    >
                        <div class="flex flex-wrap items-center justify-end gap-2">
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

                            <Button
                                v-if="event.certificate_available"
                                @click="viewCertificate(event.id)"
                                :disabled="isProcessing[event.id]"
                                size="sm"
                                class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-sm hover:from-violet-700 hover:to-indigo-700"
                            >
                                <span v-if="isProcessing[event.id]" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent mr-2"></span>
                                <Award v-else class="size-4 mr-1" />
                                Certificate
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else-if="events.length === 0"
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

        <!-- Filtered-empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center gap-3 py-16 text-center"
        >
            <div
                class="flex size-14 items-center justify-center rounded-2xl bg-muted"
            >
                <CalendarDays class="size-7 text-muted-foreground" />
            </div>
            <p class="text-sm text-muted-foreground">
                {{
                    search.trim()
                        ? 'No events match your search.'
                        : `No ${filter === 'done' ? 'completed' : 'upcoming'} events.`
                }}
            </p>
        </div>
        </div>
    </div>
</template>
