<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Award, BarChart3, CalendarDays, ChevronRight, ClipboardCheck, Download, TrendingUp, Users } from '@lucide/vue';
import { computed } from 'vue';

interface EventStats {
    id: number;
    title: string;
    start_time: string;
    registered: number;
    attended: number;
    evaluations: number;
    certificates: number;
}

const props = defineProps<{
    events: EventStats[];
    totalEvents: number;
    totalParticipants: number;
    totalAttendances: number;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Reports', href: '/reports' },
        ],
    },
});

function attendanceRate(event: EventStats): number {
    if (event.registered === 0) return 0;
    return Math.round((event.attended / event.registered) * 100);
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
    });
}

const overallAttendanceRate = computed(() => {
    if (props.totalParticipants === 0) return 0;
    return Math.round((props.totalAttendances / props.totalParticipants) * 100);
});

function rateColor(rate: number): string {
    if (rate >= 80) return 'text-green-600 dark:text-green-400';
    if (rate >= 50) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
}

function rateBarColor(rate: number): string {
    if (rate >= 80) return 'bg-green-500';
    if (rate >= 50) return 'bg-amber-500';
    return 'bg-red-500';
}
</script>

<template>
    <Head title="Reports" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Reports</h1>
                <p class="mt-1 text-sm text-muted-foreground">Overview of events, attendance, and engagement metrics.</p>
            </div>
            <div class="flex items-center gap-2">
                <Link href="/reports/export?format=csv">
                    <button class="inline-flex items-center gap-2 rounded-lg border border-border bg-background px-3.5 py-2 text-sm font-medium text-foreground hover:bg-muted/50 transition-colors shadow-xs">
                        <Download class="size-4" />
                        Export CSV
                    </button>
                </Link>
                <Link href="/reports/export?format=pdf">
                    <button class="inline-flex items-center gap-2 rounded-lg border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20 px-3.5 py-2 text-sm font-medium text-rose-700 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/30 transition-colors shadow-xs">
                        <Download class="size-4" />
                        Export PDF
                    </button>
                </Link>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-border bg-card p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Total Events</p>
                        <p class="mt-1.5 text-3xl font-bold text-foreground">{{ totalEvents }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">All time</p>
                    </div>
                    <div class="flex size-12 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/30 shadow-sm">
                        <CalendarDays class="size-6 text-violet-600 dark:text-violet-400" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Total Participants</p>
                        <p class="mt-1.5 text-3xl font-bold text-foreground">{{ totalParticipants.toLocaleString() }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">Registered across all events</p>
                    </div>
                    <div class="flex size-12 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30 shadow-sm">
                        <Users class="size-6 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Total Attendances</p>
                        <p class="mt-1.5 text-3xl font-bold text-foreground">{{ totalAttendances.toLocaleString() }}</p>
                        <div class="mt-1 flex items-center gap-1">
                            <TrendingUp class="size-3 text-green-500" />
                            <span class="text-xs font-medium" :class="rateColor(overallAttendanceRate)">
                                {{ overallAttendanceRate }}% rate
                            </span>
                        </div>
                    </div>
                    <div class="flex size-12 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30 shadow-sm">
                        <BarChart3 class="size-6 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Events Table -->
        <div class="rounded-xl border border-border bg-card shadow-xs overflow-hidden">
            <div class="flex items-center gap-3 border-b border-border bg-muted/30 px-5 py-4">
                <div class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600">
                    <BarChart3 class="size-4 text-white" />
                </div>
                <div>
                    <h2 class="font-semibold text-foreground">Event Breakdown</h2>
                    <p class="text-xs text-muted-foreground">Attendance and engagement per event</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/20">
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Event</th>
                            <th class="px-4 py-3 text-center font-semibold text-muted-foreground">Registered</th>
                            <th class="px-4 py-3 text-center font-semibold text-muted-foreground">Attended</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground w-40">Rate</th>
                            <th class="px-4 py-3 text-center font-semibold text-muted-foreground">Evaluations</th>
                            <th class="px-4 py-3 text-center font-semibold text-muted-foreground">Certificates</th>
                            <th class="px-4 py-3 text-right font-semibold text-muted-foreground">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <template v-if="events.length > 0">
                            <tr
                                v-for="event in events"
                                :key="event.id"
                                class="group hover:bg-muted/30 transition-colors cursor-pointer"
                                @click="$inertia.visit(`/events/${event.id}`)"
                            >
                                <td class="px-4 py-3.5">
                                    <div>
                                        <p class="font-medium text-foreground group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                                            {{ event.title }}
                                        </p>
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ formatDate(event.start_time) }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center size-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-sm font-semibold text-blue-700 dark:text-blue-300">
                                        {{ event.registered }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center size-8 rounded-full bg-green-100 dark:bg-green-900/30 text-sm font-semibold text-green-700 dark:text-green-300">
                                        {{ event.attended }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 rounded-full bg-muted overflow-hidden">
                                            <div
                                                :class="['h-full rounded-full transition-all duration-500', rateBarColor(attendanceRate(event))]"
                                                :style="{ width: `${attendanceRate(event)}%` }"
                                            />
                                        </div>
                                        <span :class="['text-xs font-semibold w-10 text-right', rateColor(attendanceRate(event))]">
                                            {{ attendanceRate(event) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <ClipboardCheck class="size-3.5 text-muted-foreground" />
                                        <span class="text-foreground font-medium">{{ event.evaluations }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <Award class="size-3.5 text-muted-foreground" />
                                        <span class="text-foreground font-medium">{{ event.certificates }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <Link :href="`/events/${event.id}`" @click.stop>
                                        <button class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors opacity-0 group-hover:opacity-100">
                                            View
                                            <ChevronRight class="size-3" />
                                        </button>
                                    </Link>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="7" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex size-16 items-center justify-center rounded-2xl bg-muted">
                                        <BarChart3 class="size-8 text-muted-foreground/50" />
                                    </div>
                                    <p class="font-medium text-muted-foreground">No event data yet</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
