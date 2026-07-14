<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
  ArrowLeft,
  Award,
  CalendarDays,
  CheckCircle2,
  Clock,
  Download,
  QrCode,
  Users,
  Ticket,
  FileText,
  MapPin,
} from "@lucide/vue";
import axios from "axios";

import { computed, ref } from "vue";

import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import ParticipantLayout from "@/layouts/ParticipantLayout.vue";
import { downloadQrAsPng } from "@/lib/downloadQr";

interface Session {
  id: number;
  name: string;
  start_time: string;
  end_time: string;
  requires_checkout: boolean;
  has_check_in?: boolean;
  has_check_out?: boolean;
}

interface Event {
  id: number;
  title: string;
  description: string | null;
  address: string | null;
  start_time: string;
  end_time: string;
  registration_start_date: string | null;
  registration_end_date: string | null;
  registration_type: string;
  attendance_type: string;
  status: "upcoming" | "ongoing" | "completed" | "cancelled";
  participants_count: number;
  certificate_enabled: boolean;
  evaluation_required: boolean;
  evaluation_available: boolean;
  evaluation_submitted: boolean;
  is_registered: boolean;
  registration_status?: string;
  qr_code_url?: string;
  sessions: Session[];
}

const props = defineProps<{
  event: Event;
}>();

defineOptions({ layout: ParticipantLayout });

const statusConfig: Record<string, { label: string; classes: string }> = {
  upcoming: {
    label: "Upcoming",
    classes: "bg-blue-100/80 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300",
  },
  ongoing: {
    label: "Live Now",
    classes: "bg-green-100/80 text-green-700 dark:bg-green-900/50 dark:text-green-300",
  },
  completed: {
    label: "Completed",
    classes: "bg-gray-100/80 text-gray-600 dark:bg-gray-800/80 dark:text-gray-400",
  },
  cancelled: {
    label: "Cancelled",
    classes: "bg-red-100/80 text-red-700 dark:bg-red-900/50 dark:text-red-300",
  },
};

const canRegister = computed(
  () =>
    !props.event.is_registered &&
    props.event.registration_type !== "closed" &&
    props.event.status !== "completed" &&
    props.event.status !== "cancelled"
);

const form = useForm({});
const canDownloadCertificate = computed(
  () =>
    props.event.is_registered &&
    props.event.certificate_enabled &&
    props.event.status === "completed"
);

function register() {
  form.post(`/portal/events/${props.event.id}/register`, {
    preserveScroll: true,
  });
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString("en-US", {
    weekday: "long",
    month: "long",
    day: "numeric",
    year: "numeric",
  });
}

function formatTime(dateStr: string): string {
  return new Date(dateStr).toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
  });
}

const isDownloading = ref(false);
const showCertSuccessModal = ref(false);
const showCertErrorModal = ref(false);

async function downloadFile(url: string, filename: string) {
  if (isDownloading.value) return;

  isDownloading.value = true;

  try {
    const response = await axios.get(url, { responseType: "blob" });
    // If the backend redirects with an error, axios follows it and receives the HTML page.

    if (response.data.type && response.data.type.includes("text/html")) {
      throw new Error("Failed to download. Please make sure you meet all requirements.");
    }

    const blobUrl = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = blobUrl;
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    link.remove();

    showCertSuccessModal.value = true;
  } catch (error) {
    showCertErrorModal.value = true;
  } finally {
    isDownloading.value = false;
  }
}

async function viewCertificate(url: string) {
  if (isDownloading.value) return;

  isDownloading.value = true;

  const newWindow = window.open("about:blank", "_blank");

  if (newWindow) {
    newWindow.document.write(
      '<div style="display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;">Loading certificate...</div>'
    );
  }

  try {
    const response = await axios.get(url, { responseType: "blob" });

    if (response.data.type && response.data.type.includes("text/html")) {
      throw new Error("Failed to view certificate.");
    }

    const blobUrl = window.URL.createObjectURL(
      new Blob([response.data], { type: response.data.type })
    );

    if (newWindow) {
      newWindow.location.href = blobUrl;
    } else {
      window.location.href = blobUrl;
    }
  } catch (error) {
    if (newWindow) newWindow.close();

    showCertErrorModal.value = true;
  } finally {
    isDownloading.value = false;
  }
}
</script>

<template>
  <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 sm:p-6">
    <Head :title="event.title" />
    <!-- Back Link -->
    <Link
      href="/portal/events"
      class="inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
    >
      <ArrowLeft class="size-4" />
      Back to Events
    </Link>

    <!-- Card -->
    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
      <!-- Gradient Header -->
      <div
        class="relative bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 px-6 py-10"
      >
        <div class="absolute inset-0 opacity-10">
          <div class="absolute top-0 right-0 size-32 rounded-full bg-white blur-3xl" />
          <div class="absolute bottom-0 left-0 size-24 rounded-full bg-white blur-2xl" />
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
          <h1 class="font-serif text-2xl leading-snug font-bold text-white sm:text-3xl">
            {{ event.title }}
          </h1>
        </div>
      </div>

      <!-- Body -->
      <div class="flex flex-col gap-6 p-6">
        <!-- Meta -->
        <div class="grid gap-4 sm:grid-cols-3">
          <div class="flex items-start gap-3">
            <CalendarDays class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
            <div>
              <p class="text-xs text-muted-foreground">Date</p>
              <p class="text-sm font-medium text-foreground">
                {{ formatDate(event.start_time) }}
              </p>
            </div>
          </div>
          <div v-if="event.address" class="flex items-start gap-3">
            <MapPin class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
            <div>
              <p class="text-xs text-muted-foreground">Location</p>
              <p class="text-sm font-medium text-foreground">
                {{ event.address }}
              </p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <Clock class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
            <div>
              <p class="text-xs text-muted-foreground">Time</p>
              <p class="text-sm font-medium text-foreground">
                {{ formatTime(event.start_time) }} –
                {{ formatTime(event.end_time) }}
              </p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <Users class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
            <div>
              <p class="text-xs text-muted-foreground">Registered</p>
              <p class="text-sm font-medium text-foreground">
                {{ event.participants_count }}
              </p>
            </div>
          </div>
        </div>

        <!-- Event Configurations -->
        <div
          class="grid grid-cols-2 gap-4 border-t border-border pt-6 sm:grid-cols-3 md:grid-cols-5"
        >
          <div class="flex flex-col gap-1">
            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
              <Ticket class="size-3.5" />
              <span>Registration</span>
            </div>
            <p class="text-sm font-medium text-foreground capitalize">
              {{ event.registration_type }}
            </p>
            <p
              v-if="event.registration_start_date && event.registration_end_date"
              class="mt-0.5 text-[10px] text-muted-foreground"
            >
              {{
                new Date(event.registration_start_date).toLocaleString("en-US", {
                  dateStyle: "medium",
                  timeStyle: "short",
                })
              }}
              <br />
              -
              {{
                new Date(event.registration_end_date).toLocaleString("en-US", {
                  dateStyle: "medium",
                  timeStyle: "short",
                })
              }}
            </p>
          </div>
          <div v-if="event.max_participants" class="flex flex-col gap-1">
            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
              <Users class="size-3.5" />
              <span>Max Capacity</span>
            </div>
            <p class="text-sm font-medium text-foreground">
              {{ event.max_participants }}
            </p>
          </div>
          <div class="flex flex-col gap-1">
            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
              <Users class="size-3.5" />
              <span>Attendance</span>
            </div>
            <p class="text-sm font-medium text-foreground capitalize">
              {{ event.attendance_type }}
            </p>
          </div>
          <div class="flex flex-col gap-1">
            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
              <Award class="size-3.5" />
              <span>Certificate</span>
            </div>
            <p class="text-sm font-medium text-foreground">
              <span
                v-if="event.certificate_enabled"
                class="text-green-600 dark:text-green-400"
                >Available</span
              >
              <span v-else class="text-muted-foreground">None</span>
            </p>
          </div>
          <div class="flex flex-col gap-1">
            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
              <FileText class="size-3.5" />
              <span>Evaluation</span>
            </div>
            <p class="text-sm font-medium text-foreground">
              <span
                v-if="event.evaluation_required"
                class="text-amber-600 dark:text-amber-400"
                >Required</span
              >
              <span v-else class="text-muted-foreground">None</span>
            </p>
          </div>
        </div>

        <!-- Description -->
        <div>
          <h2
            class="mb-2 text-xs font-bold tracking-widest text-muted-foreground uppercase"
          >
            About this event
          </h2>
          <p class="text-sm leading-relaxed whitespace-pre-line text-foreground/90">
            {{ event.description || "No description provided." }}
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
              <div class="flex items-center gap-2">
                <template
                  v-if="
                    event.is_registered &&
                    event.registration_status !== 'pending' &&
                    event.registration_status !== 'cancelled'
                  "
                >
                  <span
                    v-if="
                      session.has_check_in &&
                      (!session.requires_checkout || session.has_check_out)
                    "
                    class="inline-flex shrink-0 items-center gap-1 rounded-full bg-green-100/80 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300"
                  >
                    <CheckCircle2 class="size-3" /> Attended
                  </span>
                  <span
                    v-else-if="
                      session.has_check_in &&
                      session.requires_checkout &&
                      !session.has_check_out
                    "
                    class="inline-flex shrink-0 items-center gap-1 rounded-full bg-blue-100/80 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"
                  >
                    Checked In
                  </span>
                  <span
                    v-else
                    class="shrink-0 rounded-full bg-gray-100/80 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800/80 dark:text-gray-400"
                  >
                    Pending Attendance
                  </span>
                </template>

                <span
                  v-if="session.requires_checkout"
                  class="shrink-0 rounded-full bg-amber-100/80 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300"
                >
                  Check-out required
                </span>
              </div>
            </li>
          </ul>
        </div>

        <!-- Ticket Widget -->
        <div
          v-if="event.is_registered"
          class="mt-4 overflow-hidden rounded-xl border-2 border-primary/20 bg-primary/5 p-6 text-center dark:bg-primary/10"
        >
          <h3 class="mb-4 text-lg font-bold text-foreground">Your Event Ticket</h3>

          <div
            v-if="event.qr_code_url"
            class="flex flex-col items-center justify-center gap-4"
          >
            <div class="relative inline-block rounded-xl bg-white p-3 shadow-sm">
              <img
                :src="event.qr_code_url"
                alt="Your QR Code Ticket"
                class="size-48 object-contain"
              />
            </div>
            <button
              @click="downloadQrAsPng(event.qr_code_url)"
              class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
            >
              <Download class="size-4" /> Download Ticket
            </button>
          </div>
          <div v-else class="flex flex-col items-center gap-2">
            <QrCode class="size-12 text-muted-foreground/40" />
            <p class="text-sm text-muted-foreground">Generating your QR code...</p>
          </div>
        </div>

        <!-- Certificate Widget -->
        <div
          v-if="event.certificate_available"
          class="mt-4 overflow-hidden rounded-xl border-2 border-green-500/20 bg-green-500/5 p-6 text-center dark:bg-green-500/10"
        >
          <h3 class="mb-4 text-lg font-bold text-foreground">Your Certificate</h3>
          <p class="mb-4 text-sm text-muted-foreground">
            Congratulations! Your certificate of completion is ready.
          </p>
          <div class="flex flex-wrap items-center justify-center gap-3">
            <Button
              @click="viewCertificate(`/portal/events/${event.id}/certificate/view`)"
              :disabled="isDownloading"
              variant="outline"
              class="gap-2"
            >
              <span
                v-if="isDownloading"
                class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent"
              ></span>
              <Award v-else class="size-4" />
              View
            </Button>
            <Button
              @click="
                downloadFile(
                  `/portal/events/${event.id}/certificate/download`,
                  `certificate_${event.id}.png`
                )
              "
              :disabled="isDownloading"
              class="gap-2 bg-gradient-to-r from-violet-600 to-indigo-600 text-white hover:from-violet-700 hover:to-indigo-700"
            >
              <span
                v-if="isDownloading"
                class="size-4 animate-spin rounded-full border-2 border-white border-t-transparent"
              ></span>
              <Download v-else class="size-4" />
              Image
            </Button>
            <Button
              @click="
                downloadFile(
                  `/portal/events/${event.id}/certificate/pdf`,
                  `certificate_${event.id}.pdf`
                )
              "
              :disabled="isDownloading"
              variant="secondary"
              class="gap-2 border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700"
            >
              <span
                v-if="isDownloading"
                class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent"
              ></span>
              <Download v-else class="size-4" />
              PDF
            </Button>
          </div>
        </div>

        <!-- Evaluation Widget -->
        <div
          v-if="
            event.is_registered &&
            event.evaluation_required &&
            event.status === 'completed'
          "
          class="mt-4 overflow-hidden rounded-xl border-2 border-amber-500/20 bg-amber-500/5 p-6 text-center dark:bg-amber-500/10"
        >
          <h3 class="mb-4 text-lg font-bold text-foreground">Event Evaluation</h3>
          <template v-if="event.evaluation_available && !event.evaluation_submitted">
            <p class="mb-4 text-sm text-muted-foreground">
              Please take a moment to complete the evaluation form for this event.
            </p>
            <Link
              :href="`/portal/events/${event.id}/evaluation`"
              class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700"
            >
              <FileText class="size-4" /> Go to Evaluation
            </Link>
          </template>
          <template v-else-if="event.evaluation_submitted">
            <p class="text-sm text-muted-foreground">
              You have already submitted the evaluation form. Thank you!
            </p>
          </template>
          <template v-else>
            <p class="text-sm text-muted-foreground">
              The evaluation form is not yet available. Please check back later.
            </p>
          </template>
        </div>

        <!-- Actions -->
        <div v-else class="flex flex-wrap items-center gap-3 border-t border-border pt-5">
          <Button
            v-if="canRegister"
            class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-sm hover:from-violet-700 hover:to-indigo-700"
            @click="register"
            :disabled="form.processing"
          >
            <span
              v-if="form.processing"
              class="mr-2 size-4 animate-spin rounded-full border-2 border-white border-t-transparent"
            ></span>
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

    <!-- Certificate Success Modal -->
    <Dialog :open="showCertSuccessModal" @update:open="showCertSuccessModal = $event">
      <DialogContent class="text-center sm:max-w-md">
        <div class="flex flex-col items-center gap-4 py-6">
          <div class="relative">
            <div
              class="flex size-20 items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-teal-500 shadow-lg shadow-green-200 dark:shadow-green-900/30"
            >
              <CheckCircle2 class="size-10 text-white" />
            </div>
          </div>
          <DialogHeader>
            <DialogTitle class="text-center font-serif text-2xl"
              >Download Complete</DialogTitle
            >
            <DialogDescription class="mt-2 text-center text-base">
              Your certificate has been downloaded successfully.
            </DialogDescription>
          </DialogHeader>
        </div>
        <DialogFooter class="sm:justify-center">
          <Button
            type="button"
            @click="showCertSuccessModal = false"
            class="w-full sm:w-auto"
          >
            Close
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Certificate Error Modal -->
    <Dialog :open="showCertErrorModal" @update:open="showCertErrorModal = $event">
      <DialogContent class="text-center sm:max-w-md">
        <div class="flex flex-col items-center gap-4 py-6">
          <div class="relative">
            <div
              class="flex size-20 items-center justify-center rounded-full bg-gradient-to-br from-red-400 to-rose-500 shadow-lg shadow-red-200 dark:shadow-red-900/30"
            >
              <span class="text-4xl font-bold text-white">!</span>
            </div>
          </div>
          <DialogHeader>
            <DialogTitle class="text-center font-serif text-2xl"
              >Download Failed</DialogTitle
            >
            <DialogDescription class="mt-2 text-center text-base">
              We couldn't download your certificate. Please make sure you meet all
              requirements (like attending the event and completing the evaluation).
            </DialogDescription>
          </DialogHeader>
        </div>
        <DialogFooter class="sm:justify-center">
          <Button
            type="button"
            variant="outline"
            @click="showCertErrorModal = false"
            class="w-full sm:w-auto"
          >
            Close
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
