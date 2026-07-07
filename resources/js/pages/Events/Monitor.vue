<script setup lang="ts">
import { Head, router, useForm, usePoll } from '@inertiajs/vue3';
import {
    CalendarDays,
    ChevronLeft,
    Clock,
    QrCode,
    RefreshCw,
    UserCheck,
    Users,
    Wifi,
} from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface AttendanceRecord {
    id: number;
    participant: {
        user: { name: string; email: string };
    };
    scan_type: 'one_time' | 'am' | 'pm';
    scanned_at: string;
}

interface Event {
    id: number;
    title: string;
    start_time: string;
    end_time: string;
    attendance_type: 'one_time' | 'am_pm';
}

const props = defineProps<{
    event: Event;
    attendances: AttendanceRecord[];
    participantsCount: number;
    checkedInCount: number;
}>();

defineOptions({
    layout: (props: { event?: Event }) => ({
        breadcrumbs: [
            { title: 'Events', href: '/events' },
            {
                title: props.event?.title ?? 'Event',
                href: `/events/${props.event?.id}`,
            },
            { title: 'Monitor', href: '#' },
        ],
    }),
});

// Auto-poll every 5 seconds
usePoll(5000, { only: ['attendances', 'participantsCount', 'checkedInCount'] });

const pendingCount = computed(
    () => props.participantsCount - props.checkedInCount,
);
const completionRate = computed(() =>
    props.participantsCount > 0
        ? Math.round((props.checkedInCount / props.participantsCount) * 100)
        : 0,
);

const scanForm = useForm({
    token: '',
    scan_type: 'one_time' as 'one_time' | 'am' | 'pm',
});

function submitScan() {
    scanForm.post(`/events/${props.event.id}/attendance/scan`, {
        onSuccess: () => scanForm.reset('token'),
        preserveScroll: true,
    });
}

function formatDateTime(dateStr: string) {
    return new Date(dateStr).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}

const scanTypeConfig: Record<string, { label: string; classes: string }> = {
    one_time: {
        label: 'Check-In',
        classes:
            'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300',
    },
    am: {
        label: 'AM',
        classes:
            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    },
    pm: {
        label: 'PM',
        classes:
            'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
    },
};

// SVG ring progress
const radius = 36;
const circumference = 2 * Math.PI * radius;
const strokeDashoffset = computed(
    () => circumference - (completionRate.value / 100) * circumference,
);
</script>

<template>
    <Head :title="`Monitor — ${event.title}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <Link :href="`/events/${event.id}`">
                    <Button variant="ghost" size="icon-sm" class="mt-1">
                        <ChevronLeft class="size-4" />
                    </Button>
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-300"
                        >
                            <span
                                class="size-1.5 animate-pulse rounded-full bg-green-500"
                            />
                            Live
                        </span>
                    </div>
                    <h1
                        class="mt-1 font-serif text-2xl font-bold tracking-tight text-foreground"
                    >
                        {{ event.title }}
                    </h1>
                    <p
                        class="mt-0.5 flex items-center gap-1.5 text-sm text-muted-foreground"
                    >
                        <CalendarDays class="size-3.5" />
                        {{ formatDate(event.start_time) }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <Wifi class="size-4 animate-pulse text-green-500" />
                Auto-refreshing every 5s
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Registered -->
            <div class="rounded-xl border border-border bg-card p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Registered
                        </p>
                        <p class="mt-1.5 text-4xl font-bold text-foreground">
                            {{ participantsCount }}
                        </p>
                    </div>
                    <div
                        class="flex size-12 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/30"
                    >
                        <Users
                            class="size-6 text-violet-600 dark:text-violet-400"
                        />
                    </div>
                </div>
            </div>

            <!-- Checked In -->
            <div class="rounded-xl border border-border bg-card p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Checked In
                        </p>
                        <p
                            class="mt-1.5 text-4xl font-bold text-green-600 dark:text-green-400"
                        >
                            {{ checkedInCount }}
                        </p>
                    </div>
                    <div
                        class="flex size-12 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30"
                    >
                        <UserCheck
                            class="size-6 text-green-600 dark:text-green-400"
                        />
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="rounded-xl border border-border bg-card p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Pending
                        </p>
                        <p
                            class="mt-1.5 text-4xl font-bold text-amber-600 dark:text-amber-400"
                        >
                            {{ pendingCount }}
                        </p>
                    </div>
                    <div
                        class="flex size-12 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30"
                    >
                        <Clock
                            class="size-6 text-amber-600 dark:text-amber-400"
                        />
                    </div>
                </div>
            </div>

            <!-- Completion Rate with Ring -->
            <div class="rounded-xl border border-border bg-card p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Completion
                        </p>
                        <p class="mt-1.5 text-4xl font-bold text-foreground">
                            {{ completionRate }}%
                        </p>
                    </div>
                    <div class="relative size-16">
                        <svg class="size-16 -rotate-90" viewBox="0 0 80 80">
                            <circle
                                cx="40"
                                cy="40"
                                :r="radius"
                                fill="none"
                                class="stroke-muted"
                                stroke-width="6"
                            />
                            <circle
                                cx="40"
                                cy="40"
                                :r="radius"
                                fill="none"
                                class="stroke-violet-600 transition-all duration-700 dark:stroke-violet-400"
                                stroke-width="6"
                                stroke-linecap="round"
                                :stroke-dasharray="circumference"
                                :stroke-dashoffset="strokeDashoffset"
                            />
                        </svg>
                        <span
                            class="absolute inset-0 flex items-center justify-center text-xs font-bold text-foreground"
                        >
                            {{ completionRate }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- QR Scanner -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-xs"
            >
                <div
                    class="flex items-center gap-3 border-b border-border bg-muted/30 px-5 py-4"
                >
                    <div
                        class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600"
                    >
                        <QrCode class="size-4 text-white" />
                    </div>
                    <div>
                        <h2 class="font-serif font-semibold text-foreground">
                            QR Scanner
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            Paste or type a QR token to record attendance
                        </p>
                    </div>
                </div>
                <form @submit.prevent="submitScan" class="space-y-4 p-5">
                    <div class="grid gap-2">
                        <Label for="token">QR Token</Label>
                        <Input
                            id="token"
                            v-model="scanForm.token"
                            placeholder="Scan or paste token..."
                            autofocus
                            class="font-mono"
                        />
                        <p
                            v-if="scanForm.errors.token"
                            class="text-xs text-destructive"
                        >
                            {{ scanForm.errors.token }}
                        </p>
                    </div>

                    <div
                        v-if="event.attendance_type === 'am_pm'"
                        class="grid gap-2"
                    >
                        <Label>Scan Type</Label>
                        <div class="grid grid-cols-2 gap-2">
                            <label
                                :class="[
                                    'flex cursor-pointer items-center gap-2 rounded-lg border p-3 text-sm transition-all',
                                    scanForm.scan_type === 'am'
                                        ? 'border-amber-500 bg-amber-50 font-medium text-amber-700 dark:bg-amber-900/20 dark:text-amber-300'
                                        : 'border-border hover:bg-muted/30',
                                ]"
                            >
                                <input
                                    type="radio"
                                    v-model="scanForm.scan_type"
                                    value="am"
                                    class="accent-amber-600"
                                />
                                AM Session
                            </label>
                            <label
                                :class="[
                                    'flex cursor-pointer items-center gap-2 rounded-lg border p-3 text-sm transition-all',
                                    scanForm.scan_type === 'pm'
                                        ? 'border-indigo-500 bg-indigo-50 font-medium text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300'
                                        : 'border-border hover:bg-muted/30',
                                ]"
                            >
                                <input
                                    type="radio"
                                    v-model="scanForm.scan_type"
                                    value="pm"
                                    class="accent-indigo-600"
                                />
                                PM Session
                            </label>
                        </div>
                    </div>

                    <Button
                        type="submit"
                        :disabled="scanForm.processing || !scanForm.token"
                        class="w-full bg-gradient-to-r from-violet-600 to-indigo-600 text-white hover:from-violet-700 hover:to-indigo-700"
                    >
                        <RefreshCw
                            v-if="scanForm.processing"
                            class="size-4 animate-spin"
                        />
                        <QrCode v-else class="size-4" />
                        {{
                            scanForm.processing
                                ? 'Recording...'
                                : 'Record Attendance'
                        }}
                    </Button>

                    <p
                        v-if="scanForm.errors.general"
                        class="text-center text-sm text-destructive"
                    >
                        {{ scanForm.errors.general }}
                    </p>
                    <p
                        v-if="scanForm.wasSuccessful"
                        class="text-center text-sm font-medium text-green-600 dark:text-green-400"
                    >
                        ✓ Attendance recorded!
                    </p>
                </form>
            </div>

            <!-- Live Attendance Log -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-xs lg:col-span-2"
            >
                <div
                    class="flex items-center gap-3 border-b border-border bg-muted/30 px-5 py-4"
                >
                    <div
                        class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-green-500 to-teal-600"
                    >
                        <Clock class="size-4 text-white" />
                    </div>
                    <div>
                        <h2 class="font-serif font-semibold text-foreground">
                            Live Attendance Log
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            Most recent first · {{ attendances.length }} records
                        </p>
                    </div>
                </div>
                <div class="max-h-[400px] overflow-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 z-10 bg-card">
                            <tr class="border-b border-border">
                                <th
                                    class="px-4 py-3 text-left font-semibold text-muted-foreground"
                                >
                                    Participant
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-muted-foreground"
                                >
                                    Type
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-muted-foreground"
                                >
                                    Time
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <template v-if="attendances.length > 0">
                                <tr
                                    v-for="record in attendances"
                                    :key="record.id"
                                    class="transition-colors hover:bg-muted/30"
                                >
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div
                                                class="flex size-7 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-teal-600 text-xs font-semibold text-white"
                                            >
                                                {{
                                                    record.participant.user.name
                                                        .charAt(0)
                                                        .toUpperCase()
                                                }}
                                            </div>
                                            <div>
                                                <p
                                                    class="leading-none font-medium text-foreground"
                                                >
                                                    {{
                                                        record.participant.user
                                                            .name
                                                    }}
                                                </p>
                                                <p
                                                    class="mt-0.5 text-xs text-muted-foreground"
                                                >
                                                    {{
                                                        record.participant.user
                                                            .email
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            v-if="
                                                scanTypeConfig[record.scan_type]
                                            "
                                            :class="[
                                                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                                scanTypeConfig[record.scan_type]
                                                    .classes,
                                            ]"
                                        >
                                            {{
                                                scanTypeConfig[record.scan_type]
                                                    .label
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-4 py-3 font-mono text-xs text-muted-foreground"
                                    >
                                        {{ formatDateTime(record.scanned_at) }}
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td colspan="3" class="py-12 text-center">
                                    <div
                                        class="flex flex-col items-center gap-2"
                                    >
                                        <Clock
                                            class="size-8 text-muted-foreground/40"
                                        />
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Waiting for attendance scans...
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
