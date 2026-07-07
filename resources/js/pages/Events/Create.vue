<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { CalendarDays, ChevronLeft } from "@lucide/vue";
import { Link } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

interface Organizer {
  id: number;
  name: string;
  email: string;
}

const props = defineProps<{
  organizers: Organizer[];
}>();

defineOptions({
  layout: {
    breadcrumbs: [
      { title: "Events", href: "/events" },
      { title: "Create Event", href: "/events/create" },
    ],
  },
});

const form = useForm({
  title: "",
  description: "",
  organizer_id: "",
  start_time: "",
  end_time: "",
  registration_type: "public" as "public" | "static",
  attendance_type: "one_time" as "one_time" | "am_pm",
  evaluation_required: false,
  certificate_enabled: false,
});

function submit() {
  form.post("/events");
}
</script>

<template>
  <Head title="Create Event" />

  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
      <Link href="/events">
        <Button variant="ghost" size="icon-sm">
          <ChevronLeft class="size-4" />
        </Button>
      </Link>
      <div>
        <h1 class="font-serif text-2xl font-bold tracking-tight text-foreground">
          Create Event
        </h1>
        <p class="mt-0.5 text-sm text-muted-foreground">
          Fill in the details below to create a new event.
        </p>
      </div>
    </div>

    <form @submit.prevent="submit" class="flex max-w-3xl flex-col gap-6">
      <!-- Section 1: Basic Info -->
      <div class="overflow-hidden rounded-xl border border-border bg-card shadow-xs">
        <div class="flex items-center gap-3 border-b border-border bg-muted/30 px-6 py-4">
          <div
            class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600"
          >
            <CalendarDays class="size-4 text-white" />
          </div>
          <div>
            <h2 class="font-serif font-semibold text-foreground">Basic Information</h2>
            <p class="text-xs text-muted-foreground">
              Event title, description and organizer
            </p>
          </div>
        </div>
        <div class="space-y-5 p-6">
          <div class="grid gap-2">
            <Label for="title">Event Title <span class="text-destructive">*</span></Label>
            <Input
              id="title"
              v-model="form.title"
              placeholder="e.g. Annual Tech Summit 2026"
              :class="{
                'border-destructive ring-destructive/20': form.errors.title,
              }"
            />
            <p v-if="form.errors.title" class="text-xs text-destructive">
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
            <p v-if="form.errors.description" class="text-xs text-destructive">
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
            <p v-if="form.errors.organizer_id" class="text-xs text-destructive">
              {{ form.errors.organizer_id }}
            </p>
          </div>
        </div>
      </div>

      <!-- Section 2: Schedule -->
      <div class="overflow-hidden rounded-xl border border-border bg-card shadow-xs">
        <div class="flex items-center gap-3 border-b border-border bg-muted/30 px-6 py-4">
          <div
            class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600"
          >
            <CalendarDays class="size-4 text-white" />
          </div>
          <div>
            <h2 class="font-serif font-semibold text-foreground">Schedule</h2>
            <p class="text-xs text-muted-foreground">Set the start and end date/time</p>
          </div>
        </div>
        <div class="grid gap-5 p-6 sm:grid-cols-2">
          <div class="grid gap-2">
            <Label for="start_time"
              >Start Date & Time <span class="text-destructive">*</span></Label
            >
            <Input
              id="start_time"
              v-model="form.start_time"
              type="datetime-local"
              :class="{
                'border-destructive': form.errors.start_time,
              }"
            />
            <p v-if="form.errors.start_time" class="text-xs text-destructive">
              {{ form.errors.start_time }}
            </p>
          </div>
          <div class="grid gap-2">
            <Label for="end_time"
              >End Date & Time <span class="text-destructive">*</span></Label
            >
            <Input
              id="end_time"
              v-model="form.end_time"
              type="datetime-local"
              :class="{
                'border-destructive': form.errors.end_time,
              }"
            />
            <p v-if="form.errors.end_time" class="text-xs text-destructive">
              {{ form.errors.end_time }}
            </p>
          </div>
        </div>
      </div>

      <!-- Section 3: Configuration -->
      <div class="overflow-hidden rounded-xl border border-border bg-card shadow-xs">
        <div class="flex items-center gap-3 border-b border-border bg-muted/30 px-6 py-4">
          <div
            class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-orange-500 to-rose-600"
          >
            <CalendarDays class="size-4 text-white" />
          </div>
          <div>
            <h2 class="font-serif font-semibold text-foreground">Configuration</h2>
            <p class="text-xs text-muted-foreground">
              Registration type, attendance mode, and features
            </p>
          </div>
        </div>
        <div class="space-y-6 p-6">
          <!-- Registration Type -->
          <div class="grid gap-3">
            <Label>Registration Type <span class="text-destructive">*</span></Label>
            <div class="grid grid-cols-2 gap-3">
              <label
                :class="[
                  'flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-all',
                  form.registration_type === 'public'
                    ? 'border-violet-500 bg-violet-50 ring-2 ring-violet-200 dark:bg-violet-900/20 dark:ring-violet-800'
                    : 'border-border hover:border-muted-foreground/30 hover:bg-muted/20',
                ]"
              >
                <input
                  type="radio"
                  v-model="form.registration_type"
                  value="public"
                  class="mt-0.5 accent-violet-600"
                />
                <div>
                  <p class="text-sm font-medium text-foreground">Public</p>
                  <p class="mt-0.5 text-xs text-muted-foreground">
                    Anyone can register for this event
                  </p>
                </div>
              </label>
              <label
                :class="[
                  'flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-all',
                  form.registration_type === 'static'
                    ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200 dark:bg-orange-900/20 dark:ring-orange-800'
                    : 'border-border hover:border-muted-foreground/30 hover:bg-muted/20',
                ]"
              >
                <input
                  type="radio"
                  v-model="form.registration_type"
                  value="static"
                  class="mt-0.5 accent-orange-600"
                />
                <div>
                  <p class="text-sm font-medium text-foreground">Static List</p>
                  <p class="mt-0.5 text-xs text-muted-foreground">
                    Upload a CSV of pre-registered participants
                  </p>
                </div>
              </label>
            </div>
            <p v-if="form.errors.registration_type" class="text-xs text-destructive">
              {{ form.errors.registration_type }}
            </p>
          </div>

          <!-- Attendance Type -->
          <div class="grid gap-3">
            <Label>Attendance Type <span class="text-destructive">*</span></Label>
            <div class="grid grid-cols-2 gap-3">
              <label
                :class="[
                  'flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-all',
                  form.attendance_type === 'one_time'
                    ? 'border-teal-500 bg-teal-50 ring-2 ring-teal-200 dark:bg-teal-900/20 dark:ring-teal-800'
                    : 'border-border hover:border-muted-foreground/30 hover:bg-muted/20',
                ]"
              >
                <input
                  type="radio"
                  v-model="form.attendance_type"
                  value="one_time"
                  class="mt-0.5 accent-teal-600"
                />
                <div>
                  <p class="text-sm font-medium text-foreground">One-Time</p>
                  <p class="mt-0.5 text-xs text-muted-foreground">
                    Single scan for the whole event
                  </p>
                </div>
              </label>
              <label
                :class="[
                  'flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-all',
                  form.attendance_type === 'am_pm'
                    ? 'border-cyan-500 bg-cyan-50 ring-2 ring-cyan-200 dark:bg-cyan-900/20 dark:ring-cyan-800'
                    : 'border-border hover:border-muted-foreground/30 hover:bg-muted/20',
                ]"
              >
                <input
                  type="radio"
                  v-model="form.attendance_type"
                  value="am_pm"
                  class="mt-0.5 accent-cyan-600"
                />
                <div>
                  <p class="text-sm font-medium text-foreground">AM / PM</p>
                  <p class="mt-0.5 text-xs text-muted-foreground">
                    Separate scans for morning and afternoon
                  </p>
                </div>
              </label>
            </div>
            <p v-if="form.errors.attendance_type" class="text-xs text-destructive">
              {{ form.errors.attendance_type }}
            </p>
          </div>

          <!-- Toggles -->
          <div class="grid gap-4">
            <div
              class="flex items-center justify-between rounded-lg border border-border p-4"
            >
              <div>
                <p class="text-sm font-medium text-foreground">Evaluation Required</p>
                <p class="mt-0.5 text-xs text-muted-foreground">
                  Participants must complete an evaluation form after attending
                </p>
              </div>
              <button
                type="button"
                role="switch"
                :aria-checked="form.evaluation_required"
                @click="form.evaluation_required = !form.evaluation_required"
                :class="[
                  'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
                  form.evaluation_required ? 'bg-violet-600' : 'bg-input',
                ]"
              >
                <span
                  :class="[
                    'pointer-events-none inline-block size-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out',
                    form.evaluation_required ? 'translate-x-5' : 'translate-x-0',
                  ]"
                />
              </button>
            </div>

            <div
              class="flex items-center justify-between rounded-lg border border-border p-4"
            >
              <div>
                <p class="text-sm font-medium text-foreground">Certificate Enabled</p>
                <p class="mt-0.5 text-xs text-muted-foreground">
                  Automatically generate certificates for attendees
                </p>
              </div>
              <button
                type="button"
                role="switch"
                :aria-checked="form.certificate_enabled"
                @click="form.certificate_enabled = !form.certificate_enabled"
                :class="[
                  'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
                  form.certificate_enabled ? 'bg-violet-600' : 'bg-input',
                ]"
              >
                <span
                  :class="[
                    'pointer-events-none inline-block size-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out',
                    form.certificate_enabled ? 'translate-x-5' : 'translate-x-0',
                  ]"
                />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3">
        <Link href="/events">
          <Button variant="outline" type="button">Cancel</Button>
        </Link>
        <Button
          type="submit"
          :disabled="form.processing"
          class="bg-linear-to-br from-slate-800 from-40% to-[#d4af37] text-white shadow-md"
        >
          {{ form.processing ? "Creating..." : "Create Event" }}
        </Button>
      </div>
    </form>
  </div>
</template>
