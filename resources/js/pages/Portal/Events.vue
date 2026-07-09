<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { CalendarDays, CheckCircle2, Search, Ticket } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import ParticipantLayout from '@/layouts/ParticipantLayout.vue';
import ProfileRail from '@/components/portal/ProfileRail.vue';

interface Event {
    id: number;
    title: string;
    description: string | null;
    start_time: string;
    end_time: string;
    status: 'upcoming' | 'ongoing' | 'completed' | 'cancelled';
    registration_type: string;
    participants_count: number;
    is_registered: boolean;
    certificate_enabled: boolean;
}

interface PaginatedEvents {
    data: Event[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

const props = defineProps<{
    events: PaginatedEvents;
    filters?: { search?: string };
}>();

defineOptions({ layout: ParticipantLayout });

const search = ref(props.filters?.search ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/portal/events',
            { search: search.value },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 300);
});

function registerForEvent(eventId: number) {
    router.post(`/portal/events/${eventId}/register`);
}

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

function excerpt(text: string | null, length = 120): string {
    if (!text) return 'No description provided.';
    return text.length > length
        ? text.slice(0, length).trimEnd() + '...'
        : text;
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

const statusConfig: Record<string, { label: string; classes: string }> = {
    upcoming: {
        label: 'Upcoming',
        classes:
            'bg-blue-100/80 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
    },
    ongoing: {
        label: 'Live Now',
        classes:
            'bg-green-100/80 text-green-700 dark:bg-green-900/50 dark:text-green-300',
    },
    completed: {
        label: 'Completed',
        classes:
            'bg-gray-100/80 text-gray-600 dark:bg-gray-800/80 dark:text-gray-400',
    },
    cancelled: {
        label: 'Cancelled',
        classes:
            'bg-red-100/80 text-red-700 dark:bg-red-900/50 dark:text-red-300',
    },
};
</script>

<template>
    <Head title="Event Portal" />

    <div class="grid gap-6 p-4 sm:p-6 lg:grid-cols-[240px_minmax(0,1fr)] lg:gap-20">
        <ProfileRail />
        <div class="flex min-w-0 flex-1 flex-col gap-6">
        <!-- Header -->
        <div class="flex items-end justify-between gap-4">
            <div>
                <div class="mb-1 flex items-center gap-2">
                    <div
                        class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 shadow-sm"
                    >
                        <Ticket class="size-4 text-white" />
                    </div>
                    <span
                        class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                        >Event Portal</span
                    >
                </div>
                <h1
                    class="font-serif text-3xl font-bold tracking-tight text-foreground"
                >
                    Discover Events
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Browse and register for upcoming events.
                </p>
            </div>
        </div>

        <!-- Search -->
        <div class="relative max-w-md">
            <Search
                class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="search"
                placeholder="Search events..."
                class="pl-9"
            />
        </div>

        <!-- Events Grid -->
        <div
            v-if="events.data.length > 0"
            class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
        >
            <div
                v-for="(event, index) in events.data"
                :key="event.id"
                class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-xs transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg"
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
                        <div
                            class="absolute top-4 right-4 size-16 rounded-full bg-white/30 blur-xl"
                        />
                        <div
                            class="absolute bottom-4 left-4 size-12 rounded-full bg-white/20 blur-lg"
                        />
                    </div>

                    <div
                        class="absolute inset-0 flex items-center justify-center"
                    >
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
                            <span
                                v-if="event.status === 'ongoing'"
                                class="mr-1.5 size-1.5 animate-pulse rounded-full bg-green-500"
                            />
                            {{ statusConfig[event.status].label }}
                        </span>
                    </div>

                    <!-- Registered badge -->
                    <div
                        v-if="event.is_registered"
                        class="absolute top-3 right-3"
                    >
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-green-700 shadow-sm backdrop-blur-sm dark:bg-gray-900/90 dark:text-green-400"
                        >
                            <CheckCircle2 class="size-3" />
                            Registered
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex flex-1 flex-col p-5">
                    <h3
                        class="line-clamp-2 font-serif leading-snug font-semibold text-foreground transition-colors group-hover:text-violet-600 dark:group-hover:text-violet-400"
                    >
                        {{ event.title }}
                    </h3>

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
                        class="mt-4 flex items-center justify-between gap-3 border-t border-border pt-3"
                    >
                        <span class="text-xs text-muted-foreground">
                            {{ event.participants_count }} registered
                        </span>
                        <div class="flex items-center gap-2">
                            <Link :href="`/portal/events/${event.id}`">
                                <Button variant="ghost" size="sm">View</Button>
                            </Link>
                            <template
                                v-if="
                                    event.status !== 'cancelled' &&
                                    event.status !== 'completed'
                                "
                            >
                                <Button
                                    v-if="!event.is_registered"
                                    size="sm"
                                    class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-sm hover:from-violet-700 hover:to-indigo-700"
                                    @click="registerForEvent(event.id)"
                                >
                                    Register
                                </Button>
                                <Button
                                    v-else
                                    size="sm"
                                    variant="outline"
                                    disabled
                                    class="border-green-200 text-green-600 dark:border-green-800 dark:text-green-400"
                                >
                                    <CheckCircle2 class="size-3.5" />
                                    Registered
                                </Button>
                            </template>
                            <template
                                v-else-if="
                                    event.status === 'completed' &&
                                    isRegistered(event.id) &&
                                    event.certificate_enabled
                                "
                            >
                                <a
                                    :href="`/portal/events/${event.id}/certificate/view`"
                                    target="_blank"
                                    class="mr-2"
                                >
                                    <Button size="sm" variant="outline">
                                        View Cert
                                    </Button>
                                </a>
                                <a
                                    :href="`/portal/events/${event.id}/certificate/download`"
                                >
                                    <Button
                                        size="sm"
                                        variant="default"
                                        class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-sm hover:from-violet-700 hover:to-indigo-700"
                                    >
                                        Download (Image)
                                    </Button>
                                </a>
                                <a
                                    :href="`/portal/events/${event.id}/certificate/pdf`"
                                    class="ml-2"
                                >
                                    <Button size="sm" variant="outline">
                                        PDF
                                    </Button>
                                </a>
                            </template>
                        </div>
                    </div>
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
                <CalendarDays
                    class="size-10 text-violet-500 dark:text-violet-400"
                />
            </div>
            <div class="text-center">
                <p class="text-xl font-semibold text-foreground">
                    No events available
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{
                        search
                            ? 'No events match your search. Try different keywords.'
                            : 'Check back later for upcoming events.'
                    }}
                </p>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="events.last_page > 1"
            class="flex items-center justify-between"
        >
            <p class="text-sm text-muted-foreground">
                Showing {{ events.from }}–{{ events.to }} of
                {{ events.total }} events
            </p>
            <div class="flex items-center gap-1">
                <template v-for="link in events.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" preserve-state>
                        <Button
                            variant="ghost"
                            size="sm"
                            :class="
                                link.active
                                    ? 'bg-primary text-primary-foreground'
                                    : ''
                            "
                            v-html="link.label"
                        />
                    </Link>
                    <Button
                        v-else
                        variant="ghost"
                        size="sm"
                        disabled
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
        </div>
    </div>
</template>
