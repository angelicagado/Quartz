<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { CalendarDays, ChevronLeft, Plus, Trash2 } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Organizer {
    id: number;
    name: string;
    email: string;
}

interface EventSession {
    id?: number;
    name: string;
    start_time: string;
    end_time: string;
    requires_checkout: boolean;
}

interface Event {
    id: number;
    title: string;
    description: string | null;
    organizer_id: number | null;
    start_time: string;
    end_time: string;
    registration_start_date: string | null;
    registration_end_date: string | null;
    registration_type: 'open' | 'scheduled' | 'approval' | 'closed';
    evaluation_required: boolean;
    certificate_enabled: boolean;
    sessions: EventSession[];
}

const props = defineProps<{
    event: Event;
    organizers: Organizer[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Events', href: '/events' },
            { title: 'Edit Event', href: '#' },
        ],
    },
});

// Format datetime-local value from ISO string
function toDatetimeLocal(str: string | null | undefined): string {
    if (!str) return '';
    const d = new Date(str);
    if (isNaN(d.getTime())) return '';
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

const form = useForm({
    title: props.event.title,
    description: props.event.description ?? '',
    organizer_id: props.event.organizer_id
        ? String(props.event.organizer_id)
        : '',
    start_time: toDatetimeLocal(props.event.start_time),
    end_time: toDatetimeLocal(props.event.end_time),
    registration_start_date: toDatetimeLocal(props.event.registration_start_date),
    registration_end_date: toDatetimeLocal(props.event.registration_end_date),
    registration_type: props.event.registration_type,
    evaluation_required: props.event.evaluation_required,
    certificate_enabled: props.event.certificate_enabled,
    sessions: props.event.sessions && props.event.sessions.length > 0 
        ? props.event.sessions.map(s => ({
            id: s.id,
            name: s.name,
            start_time: toDatetimeLocal(s.start_time),
            end_time: toDatetimeLocal(s.end_time),
            requires_checkout: s.requires_checkout ? true : false,
        }))
        : [
            {
                name: "Main Session",
                start_time: toDatetimeLocal(props.event.start_time),
                end_time: toDatetimeLocal(props.event.end_time),
                requires_checkout: false,
            },
        ],
});

function addSession() {
  form.sessions.push({
    name: "New Session",
    start_time: form.start_time || "",
    end_time: form.end_time || "",
    requires_checkout: false,
  });
}

function removeSession(index: number) {
  form.sessions.splice(index, 1);
}

function applyPreset(preset: string) {
  if (preset === 'one_time') {
    form.sessions = [{
      name: "Main Event",
      start_time: form.start_time,
      end_time: form.end_time,
      requires_checkout: false,
    }];
  } else if (preset === 'am_pm') {
    form.sessions = [
      {
        name: "Morning Session",
        start_time: form.start_time,
        end_time: "",
        requires_checkout: true,
      },
      {
        name: "Afternoon Session",
        start_time: "",
        end_time: form.end_time,
        requires_checkout: true,
      }
    ];
  }
}

function submit() {
    form.put(`/events/${props.event.id}`);
}
</script>

<template>
    <Head title="Edit Event" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <Link :href="`/events/${event.id}`">
                <Button variant="ghost" size="icon-sm">
                    <ChevronLeft class="size-4" />
                </Button>
            </Link>
            <div>
                <h1
                    class="font-serif text-2xl font-bold tracking-tight text-foreground"
                >
                    Edit Event
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Update the details for
                    <span class="font-medium text-foreground">{{
                        event.title
                    }}</span
                    >.
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="flex max-w-3xl flex-col gap-6">
            <!-- Section 1: Basic Info -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-xs"
            >
                <div
                    class="flex items-center gap-3 border-b border-border bg-muted/30 px-6 py-4"
                >
                    <div
                        class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600"
                    >
                        <CalendarDays class="size-4 text-white" />
                    </div>
                    <div>
                        <h2 class="font-serif font-semibold text-foreground">
                            Basic Information
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            Event title, description and organizer
                        </p>
                    </div>
                </div>
                <div class="space-y-5 p-6">
                    <div class="grid gap-2">
                        <Label for="title"
                            >Event Title
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="title"
                            v-model="form.title"
                            placeholder="e.g. Annual Tech Summit 2026"
                            :class="{
                                'border-destructive ring-destructive/20':
                                    form.errors.title,
                            }"
                        />
                        <p
                            v-if="form.errors.title"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Describe the event..."
                            rows="4"
                            class="flex min-h-[80px] w-full resize-none rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            :class="{
                                'border-destructive': form.errors.description,
                            }"
                        />
                        <p
                            v-if="form.errors.description"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="organizer_id">Organizer</Label>
                        <select
                            id="organizer_id"
                            v-model="form.organizer_id"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus:border-ring focus:ring-2 focus:ring-ring/50 focus:outline-none"
                            :class="{
                                'border-destructive': form.errors.organizer_id,
                            }"
                        >
                            <option value="">Select an organizer...</option>
                            <option
                                v-for="organizer in organizers"
                                :key="organizer.id"
                                :value="organizer.id"
                            >
                                {{ organizer.name }} ({{ organizer.email }})
                            </option>
                        </select>
                        <p
                            v-if="form.errors.organizer_id"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.organizer_id }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 2: Schedule -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-xs"
            >
                <div
                    class="flex items-center gap-3 border-b border-border bg-muted/30 px-6 py-4"
                >
                    <div
                        class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600"
                    >
                        <CalendarDays class="size-4 text-white" />
                    </div>
                    <div>
                        <h2 class="font-serif font-semibold text-foreground">
                            Event Schedule
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            Set the start and end date/time
                        </p>
                    </div>
                </div>
                <div class="grid gap-5 p-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="start_time"
                            >Event Start Time
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="start_time"
                            v-model="form.start_time"
                            type="datetime-local"
                            :class="{
                                'border-destructive': form.errors.start_time,
                            }"
                        />
                        <p
                            v-if="form.errors.start_time"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.start_time }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="end_time"
                            >Event End Time
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="end_time"
                            v-model="form.end_time"
                            type="datetime-local"
                            :class="{
                                'border-destructive': form.errors.end_time,
                            }"
                        />
                        <p
                            v-if="form.errors.end_time"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.end_time }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Sessions -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-xs">
              <div class="flex items-center justify-between border-b border-border bg-muted/30 px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-teal-500 to-emerald-600">
                    <CalendarDays class="size-4 text-white" />
                  </div>
                  <div>
                    <h2 class="font-serif font-semibold text-foreground">Event Sessions</h2>
                    <p class="text-xs text-muted-foreground">Manage attendance checkpoints</p>
                  </div>
                </div>
                <div class="flex gap-2">
                  <Button type="button" variant="outline" size="sm" @click="applyPreset('one_time')">Preset: 1 Scan</Button>
                  <Button type="button" variant="outline" size="sm" @click="applyPreset('am_pm')">Preset: AM/PM</Button>
                  <Button type="button" variant="outline" size="sm" @click="addSession"><Plus class="size-4 mr-1" /> Add Session</Button>
                </div>
              </div>
              <div class="space-y-4 p-6">
                <div v-for="(session, index) in form.sessions" :key="index" class="relative rounded-lg border border-border p-4 bg-background">
                  <div class="absolute right-2 top-2">
                    <Button type="button" variant="ghost" size="icon-sm" class="text-destructive hover:text-destructive/80" @click="removeSession(index)" v-if="form.sessions.length > 1">
                      <Trash2 class="size-4" />
                    </Button>
                  </div>
                  <div class="grid gap-4 sm:grid-cols-2 mt-2">
                    <div class="col-span-2 grid gap-2">
                      <Label>Session Name <span class="text-destructive">*</span></Label>
                      <Input v-model="session.name" placeholder="e.g. Day 1 Morning" />
                    </div>
                    <div class="grid gap-2">
                      <Label>Start Time <span class="text-destructive">*</span></Label>
                      <Input v-model="session.start_time" type="datetime-local" />
                    </div>
                    <div class="grid gap-2">
                      <Label>End Time <span class="text-destructive">*</span></Label>
                      <Input v-model="session.end_time" type="datetime-local" />
                    </div>
                    <div class="col-span-2 flex items-center gap-2 mt-2">
                      <input type="checkbox" v-model="session.requires_checkout" :id="'checkout-'+index" class="rounded border-input text-violet-600 focus:ring-violet-600">
                      <Label :for="'checkout-'+index" class="cursor-pointer">Require Check-out (Participant must scan twice for this session)</Label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section 4: Configuration -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-xs"
            >
                <div
                    class="flex items-center gap-3 border-b border-border bg-muted/30 px-6 py-4"
                >
                    <div
                        class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-orange-500 to-rose-600"
                    >
                        <CalendarDays class="size-4 text-white" />
                    </div>
                    <div>
                        <h2 class="font-serif font-semibold text-foreground">
                            Registration & Config
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            Registration rules and features
                        </p>
                    </div>
                </div>
                <div class="space-y-6 p-6">
                    <!-- Registration Type -->
                    <div class="grid gap-3">
                        <Label>Registration Rule <span class="text-destructive">*</span></Label>
                        <select v-model="form.registration_type" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <option value="open">Open (Anyone can register anytime)</option>
                        <option value="scheduled">Scheduled (Only during specific dates)</option>
                        <option value="approval">Requires Approval (Admin must approve registrations)</option>
                        <option value="closed">Closed / Admin Only (Self-registration disabled)</option>
                        </select>
                    </div>

                    <div v-if="form.registration_type === 'scheduled'" class="grid gap-5 sm:grid-cols-2 p-4 border rounded-lg bg-muted/10">
                        <div class="grid gap-2">
                        <Label>Reg. Start Date</Label>
                        <Input v-model="form.registration_start_date" type="datetime-local" />
                        </div>
                        <div class="grid gap-2">
                        <Label>Reg. End Date</Label>
                        <Input v-model="form.registration_end_date" type="datetime-local" />
                        </div>
                    </div>

                    <!-- Toggles -->
                    <div class="grid gap-4">
                        <div
                            class="flex items-center justify-between rounded-lg border border-border p-4"
                        >
                            <div>
                                <p class="text-sm font-medium text-foreground">
                                    Evaluation Required
                                </p>
                                <p class="mt-0.5 text-xs text-muted-foreground">
                                    Participants must complete an evaluation
                                    form after attending
                                </p>
                            </div>
                            <button
                                type="button"
                                role="switch"
                                :aria-checked="form.evaluation_required"
                                @click="
                                    form.evaluation_required =
                                        !form.evaluation_required
                                "
                                :class="[
                                    'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
                                    form.evaluation_required
                                        ? 'bg-violet-600'
                                        : 'bg-input',
                                ]"
                            >
                                <span
                                    :class="[
                                        'pointer-events-none inline-block size-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out',
                                        form.evaluation_required
                                            ? 'translate-x-5'
                                            : 'translate-x-0',
                                    ]"
                                />
                            </button>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border border-border p-4"
                        >
                            <div>
                                <p class="text-sm font-medium text-foreground">
                                    Certificate Enabled
                                </p>
                                <p class="mt-0.5 text-xs text-muted-foreground">
                                    Automatically generate certificates for
                                    attendees
                                </p>
                            </div>
                            <button
                                type="button"
                                role="switch"
                                :aria-checked="form.certificate_enabled"
                                @click="
                                    form.certificate_enabled =
                                        !form.certificate_enabled
                                "
                                :class="[
                                    'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
                                    form.certificate_enabled
                                        ? 'bg-violet-600'
                                        : 'bg-input',
                                ]"
                            >
                                <span
                                    :class="[
                                        'pointer-events-none inline-block size-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out',
                                        form.certificate_enabled
                                            ? 'translate-x-5'
                                            : 'translate-x-0',
                                    ]"
                                />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pb-10">
                <Link :href="`/events/${event.id}`">
                    <Button variant="outline" type="button">Cancel</Button>
                </Link>
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-md hover:from-violet-700 hover:to-indigo-700"
                >
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </form>
    </div>
</template>
