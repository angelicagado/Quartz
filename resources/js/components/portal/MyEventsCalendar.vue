<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed, ref } from 'vue';

interface CalendarEvent {
    id: number;
    title: string;
    start_time: string;
    end_time: string;
    status: string;
}

const props = defineProps<{
    events: CalendarEvent[];
}>();

const statusDot: Record<string, string> = {
    registered: 'bg-blue-500',
    attended: 'bg-amber-500',
    completed: 'bg-green-500',
};

function dotClass(status: string): string {
    return statusDot[status] ?? 'bg-violet-500';
}

function toKey(year: number, month: number, day: number): string {
    return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}

/** Map every calendar day (YYYY-MM-DD) to the events occurring on it. */
const eventsByDay = computed<Map<string, CalendarEvent[]>>(() => {
    const map = new Map<string, CalendarEvent[]>();

    for (const event of props.events) {
        const start = new Date(event.start_time);
        const end = new Date(event.end_time);
        if (Number.isNaN(start.getTime())) {
            continue;
        }

        const cursor = new Date(
            start.getFullYear(),
            start.getMonth(),
            start.getDate(),
        );
        const last = Number.isNaN(end.getTime())
            ? cursor
            : new Date(end.getFullYear(), end.getMonth(), end.getDate());

        // Guard against absurd ranges corrupting the loop.
        let guard = 0;
        while (cursor <= last && guard < 366) {
            const key = toKey(
                cursor.getFullYear(),
                cursor.getMonth(),
                cursor.getDate(),
            );
            const bucket = map.get(key);
            if (bucket) {
                bucket.push(event);
            } else {
                map.set(key, [event]);
            }
            cursor.setDate(cursor.getDate() + 1);
            guard++;
        }
    }

    return map;
});

const today = new Date();
const todayKey = toKey(today.getFullYear(), today.getMonth(), today.getDate());

// The month currently in view.
const viewYear = ref(today.getFullYear());
const viewMonth = ref(today.getMonth());

const selectedKey = ref<string | null>(
    eventsByDay.value.has(todayKey) ? todayKey : null,
);

const monthLabel = computed(() =>
    new Date(viewYear.value, viewMonth.value, 1).toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric',
    }),
);

const weekdayLabels = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

interface DayCell {
    day: number;
    key: string;
    isToday: boolean;
    events: CalendarEvent[];
}

/** Cells for the visible month, padded with nulls for leading offset. */
const cells = computed<(DayCell | null)[]>(() => {
    const firstWeekday = new Date(viewYear.value, viewMonth.value, 1).getDay();
    const daysInMonth = new Date(
        viewYear.value,
        viewMonth.value + 1,
        0,
    ).getDate();

    const result: (DayCell | null)[] = [];
    for (let i = 0; i < firstWeekday; i++) {
        result.push(null);
    }
    for (let day = 1; day <= daysInMonth; day++) {
        const key = toKey(viewYear.value, viewMonth.value, day);
        result.push({
            day,
            key,
            isToday: key === todayKey,
            events: eventsByDay.value.get(key) ?? [],
        });
    }
    return result;
});

const selectedEvents = computed<CalendarEvent[]>(() =>
    selectedKey.value ? (eventsByDay.value.get(selectedKey.value) ?? []) : [],
);

const selectedLabel = computed(() => {
    if (!selectedKey.value) {
        return '';
    }
    const [y, m, d] = selectedKey.value.split('-').map(Number);
    return new Date(y, m - 1, d).toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
    });
});

function previousMonth() {
    if (viewMonth.value === 0) {
        viewMonth.value = 11;
        viewYear.value--;
    } else {
        viewMonth.value--;
    }
}

function nextMonth() {
    if (viewMonth.value === 11) {
        viewMonth.value = 0;
        viewYear.value++;
    } else {
        viewMonth.value++;
    }
}

function selectDay(cell: DayCell) {
    if (cell.events.length === 0) {
        selectedKey.value = null;
        return;
    }
    selectedKey.value = selectedKey.value === cell.key ? null : cell.key;
}
</script>

<template>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-md ring-1 ring-border">
        <!-- Month nav -->
        <div class="mb-3 flex items-center justify-between">
            <h3 class="font-serif text-sm font-semibold text-foreground">
                {{ monthLabel }}
            </h3>
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    class="rounded-lg p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    aria-label="Previous month"
                    @click="previousMonth"
                >
                    <ChevronLeft class="size-4" />
                </button>
                <button
                    type="button"
                    class="rounded-lg p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    aria-label="Next month"
                    @click="nextMonth"
                >
                    <ChevronRight class="size-4" />
                </button>
            </div>
        </div>

        <!-- Weekday header -->
        <div
            class="mb-1 grid grid-cols-7 text-center text-[10px] font-medium tracking-wide text-muted-foreground"
        >
            <span v-for="(label, i) in weekdayLabels" :key="i">{{ label }}</span>
        </div>

        <!-- Day grid -->
        <div class="grid grid-cols-7 gap-0.5 rounded-xl bg-muted/50 p-1.5">
            <template v-for="(cell, index) in cells" :key="index">
                <div v-if="!cell" />
                <button
                    v-else
                    type="button"
                    :disabled="cell.events.length === 0"
                    class="relative flex aspect-square flex-col items-center justify-center rounded-lg text-xs transition-colors"
                    :class="[
                        cell.events.length > 0
                            ? 'cursor-pointer bg-card font-semibold text-foreground shadow-xs hover:bg-background'
                            : 'text-muted-foreground/80',
                        cell.isToday
                            ? 'ring-2 ring-violet-400 dark:ring-violet-500'
                            : '',
                        selectedKey === cell.key
                            ? 'bg-violet-100 dark:bg-violet-900/40'
                            : '',
                    ]"
                    @click="selectDay(cell)"
                >
                    <span>{{ cell.day }}</span>
                    <span
                        v-if="cell.events.length > 0"
                        class="absolute bottom-1 flex items-center gap-0.5"
                    >
                        <span
                            v-for="event in cell.events.slice(0, 3)"
                            :key="event.id"
                            class="size-1 rounded-full"
                            :class="dotClass(event.status)"
                        />
                    </span>
                </button>
            </template>
        </div>

        <!-- Selected-day detail -->
        <div
            v-if="selectedKey && selectedEvents.length > 0"
            class="mt-3 border-t border-border pt-3"
        >
            <p
                class="mb-1.5 text-[10px] font-semibold tracking-wide text-muted-foreground uppercase"
            >
                {{ selectedLabel }}
            </p>
            <ul class="flex flex-col gap-1.5">
                <li
                    v-for="event in selectedEvents"
                    :key="event.id"
                    class="flex items-center gap-2 text-xs"
                >
                    <span
                        class="size-1.5 shrink-0 rounded-full"
                        :class="dotClass(event.status)"
                    />
                    <a
                        :href="`/portal/events/${event.id}`"
                        class="truncate text-foreground transition-colors hover:text-violet-600 dark:hover:text-violet-400"
                    >
                        {{ event.title }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>
