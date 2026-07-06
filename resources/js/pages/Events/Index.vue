<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { CalendarDays, Edit, Eye, Plus, Search, Trash2 } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface Event {
    id: number;
    title: string;
    start_time: string;
    end_time: string;
    registration_type: 'public' | 'static';
    attendance_type: 'one_time' | 'am_pm';
    status: 'upcoming' | 'ongoing' | 'completed' | 'cancelled';
    organizer?: { name: string };
}

interface PaginatedEvents {
    data: Event[];
    links: { url: string | null; label: string; active: boolean }[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
}

const props = defineProps<{
    events: PaginatedEvents;
    filters?: { search?: string; status?: string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Events', href: '/events' },
        ],
    },
});

const search = ref(props.filters?.search ?? '');
const statusFilter = ref(props.filters?.status ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;

watch([search, statusFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/events', { search: search.value, status: statusFilter.value }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

function deleteEvent(id: number) {
    if (confirm('Are you sure you want to delete this event?')) {
        router.delete(`/events/${id}`);
    }
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
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

const statusConfig: Record<string, { label: string; classes: string }> = {
    upcoming: { label: 'Upcoming', classes: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800' },
    ongoing: { label: 'Ongoing', classes: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 border-green-200 dark:border-green-800' },
    completed: { label: 'Completed', classes: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-200 dark:border-gray-700' },
    cancelled: { label: 'Cancelled', classes: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 border-red-200 dark:border-red-800' },
};

const registrationConfig: Record<string, { label: string; classes: string }> = {
    public: { label: 'Public', classes: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800' },
    static: { label: 'Static List', classes: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300 border-orange-200 dark:border-orange-800' },
};

const attendanceConfig: Record<string, { label: string; classes: string }> = {
    one_time: { label: 'One-Time', classes: 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 border-teal-200 dark:border-teal-800' },
    am_pm: { label: 'AM/PM', classes: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800' },
};
</script>

<template>
    <Head title="Events" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Events</h1>
                <p class="mt-1 text-sm text-muted-foreground">Manage all your events and track attendance.</p>
            </div>
            <Link href="/events/create">
                <Button class="gap-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white shadow-md shadow-violet-200 dark:shadow-violet-900/30 transition-all duration-200">
                    <Plus class="size-4" />
                    New Event
                </Button>
            </Link>
        </div>

        <!-- Filters -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="search"
                    placeholder="Search events..."
                    class="pl-9 bg-background"
                />
            </div>
            <select
                v-model="statusFilter"
                class="h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-ring/50 transition-colors"
            >
                <option value="">All Statuses</option>
                <option value="upcoming">Upcoming</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <!-- Table -->
        <div class="rounded-xl border border-border bg-card shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/40">
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Title</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Start Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">End Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Registration</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Attendance</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Organizer</th>
                            <th class="px-4 py-3 text-right font-semibold text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <template v-if="events.data.length > 0">
                            <tr
                                v-for="event in events.data"
                                :key="event.id"
                                class="group transition-colors hover:bg-muted/30"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 shadow-sm">
                                            <CalendarDays class="size-4 text-white" />
                                        </div>
                                        <span class="font-medium text-foreground">{{ event.title }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    <div class="flex flex-col">
                                        <span>{{ formatDate(event.start_time) }}</span>
                                        <span class="text-xs text-muted-foreground/70">{{ formatTime(event.start_time) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    <div class="flex flex-col">
                                        <span>{{ formatDate(event.end_time) }}</span>
                                        <span class="text-xs text-muted-foreground/70">{{ formatTime(event.end_time) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        v-if="registrationConfig[event.registration_type]"
                                        :class="['inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium', registrationConfig[event.registration_type].classes]"
                                    >
                                        {{ registrationConfig[event.registration_type].label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        v-if="attendanceConfig[event.attendance_type]"
                                        :class="['inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium', attendanceConfig[event.attendance_type].classes]"
                                    >
                                        {{ attendanceConfig[event.attendance_type].label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        v-if="statusConfig[event.status]"
                                        :class="['inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium', statusConfig[event.status].classes]"
                                    >
                                        {{ statusConfig[event.status].label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ event.organizer?.name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="`/events/${event.id}`">
                                            <Button variant="ghost" size="icon-sm" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                <Eye class="size-4" />
                                            </Button>
                                        </Link>
                                        <Link :href="`/events/${event.id}/edit`">
                                            <Button variant="ghost" size="icon-sm" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                <Edit class="size-4" />
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            class="opacity-0 group-hover:opacity-100 transition-opacity text-destructive hover:text-destructive hover:bg-destructive/10"
                                            @click="deleteEvent(event.id)"
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <tr v-else>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex size-16 items-center justify-center rounded-2xl bg-muted">
                                        <CalendarDays class="size-8 text-muted-foreground/50" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-foreground">No events found</p>
                                        <p class="mt-1 text-sm text-muted-foreground">
                                            {{ search || statusFilter ? 'Try adjusting your filters.' : 'Get started by creating your first event.' }}
                                        </p>
                                    </div>
                                    <Link v-if="!search && !statusFilter" href="/events/create">
                                        <Button size="sm" class="mt-1">
                                            <Plus class="size-4" />
                                            New Event
                                        </Button>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="events.meta && events.meta.last_page > 1" class="flex items-center justify-between border-t border-border px-4 py-3">
                <p class="text-sm text-muted-foreground">
                    Showing <span class="font-medium text-foreground">{{ events.meta.from }}</span>–<span class="font-medium text-foreground">{{ events.meta.to }}</span>
                    of <span class="font-medium text-foreground">{{ events.meta.total }}</span> events
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in events.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-state
                        >
                            <Button
                                variant="ghost"
                                size="sm"
                                :class="link.active ? 'bg-primary text-primary-foreground hover:bg-primary/90' : ''"
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
