<template>
  <Head :title="event.title" />
  <div
    class="flex h-full flex-1 flex-col animate-in space-y-4 pb-20 duration-500 fade-in p-6"
  >
    <!-- Breadcrumbs / Back -->
    <Link
      :href="role === 'organizer' ? '/organizer/events' : '/admin/events'"
      class="group inline-flex items-center gap-2 text-slate-500 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200"
    >
      <ArrowLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
      <span class="text-sm font-medium">Back to Events</span>
    </Link>

    <!-- Hero / Header -->
    <div
      class="relative overflow-hidden rounded-[2.5rem] border border-slate-100 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
      <div
        class="absolute top-0 right-0 h-96 w-96 translate-x-1/2 -translate-y-1/2 rounded-full bg-[#d4af37]/5 blur-3xl"
      ></div>

      <div
        class="relative z-10 flex flex-col justify-between gap-8 lg:flex-row lg:items-center"
      >
        <div class="max-w-2xl space-y-4">
          <div
            class="inline-flex items-center gap-2 rounded-full border border-[#d4af37]/20 bg-[#d4af37]/10 px-3 py-1 text-[10px] font-bold tracking-widest text-[#d4af37] uppercase"
          >
            <Calendar class="h-3 w-3" />
            Event Details
          </div>
          <h1
            class="font-serif text-4xl leading-tight font-black text-slate-900 dark:text-slate-100"
          >
            {{ event.title }}
          </h1>
          <p
            class="text-lg leading-relaxed font-light text-slate-500 dark:text-slate-400"
          >
            {{ event.description }}
          </p>

          <div class="flex flex-wrap gap-6 pt-4">
            <div
              class="flex items-center gap-2.5 font-medium text-slate-600 dark:text-slate-300"
            >
              <Clock class="h-5 w-5 text-[#d4af37]" />
              <span>{{ new Date(event.start_time).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) }}</span>
            </div>
            <div
              class="flex items-center gap-2.5 font-medium text-slate-600 dark:text-slate-300"
            >
              <MapPin class="h-5 w-5 text-[#d4af37]" />
              <span>{{ event.location || "Virtual Venue" }}</span>
            </div>
          </div>
        </div>

        <div class="flex shrink-0 gap-6 lg:flex-row lg:items-center">
          <div
            v-if="['admin', 'super_admin', 'organizer'].includes(role)"
            class="group flex flex-col items-center gap-3 rounded-3xl border border-slate-100 bg-slate-50 p-4 transition-all hover:bg-white hover:shadow-xl dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700"
          >
            <div class="rounded-xl bg-white p-2 shadow-sm">
              <qrcode-vue :value="registrationUrl" :size="100" />
            </div>
            <div class="text-center">
              <p class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                Registration QR
              </p>
              <button
                @click="copyRegistrationLink"
                class="text-[11px] font-bold text-[#d4af37] hover:underline"
              >
                Copy Link
              </button>
            </div>
          </div>

          <div class="flex flex-col gap-3">
            <div
              class="flex min-w-[140px] flex-col items-center justify-center rounded-3xl border border-slate-100 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-800"
            >
              <span class="text-3xl font-black text-slate-900 dark:text-slate-100">
                {{ event.participants_count || 0 }}
              </span>
              <span
                class="mt-1 text-[10px] font-bold tracking-widest text-slate-400 uppercase"
              >
                Registrations
              </span>
            </div>
            <div v-if="['admin', 'super_admin'].includes(role)" class="flex gap-2 w-full">
              <Link
                :href="`/admin/events/${event.id}/edit`"
                class="flex flex-1 items-center justify-center rounded-2xl bg-slate-900 py-3 text-white shadow-lg transition-all hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200"
                title="Edit Event"
              >
                <Pencil class="h-5 w-5" />
              </Link>
              <button
                @click="deleteEvent"
                class="flex flex-1 items-center justify-center rounded-2xl bg-red-500 py-3 text-white shadow-lg transition-all hover:bg-red-600 dark:bg-red-500/10 dark:text-red-500 dark:hover:bg-red-500/20"
                title="Delete Event"
              >
                <Trash2 class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div>
        <!-- Certificate Builder Section -->
        <div
          v-if="['admin', 'super_admin'].includes(role) && event.certificate_enabled"
        >
          <CertificateBuilder
            :event="event"
            :existingTemplate="event.certificate_template"
          />
        </div>
      </div>
      <div>
        <!-- Evaluation Form Section -->
        <div v-if="['admin', 'super_admin'].includes(role) && event.evaluation_required">
          <EvaluationFormBuilder :event="event" />
        </div>
      </div>
    </section>

    <!-- Attendees List Section -->
    <div class="space-y-6">
      <div class="flex items-center justify-between px-2">
        <h2 class="font-serif text-2xl font-bold text-slate-900 dark:text-slate-100">
          Registered Participants
        </h2>
        <div class="flex flex-wrap items-center gap-3">
          <select
            v-model="filtersForm.status"
            class="rounded-xl border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 focus:border-[#d4af37] focus:ring-[#d4af37] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
          >
            <option value="all">All Status</option>
            <option value="registered">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="attended">Attended</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <select
            v-model="filtersForm.sort"
            class="rounded-xl border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 focus:border-[#d4af37] focus:ring-[#d4af37] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
          >
            <option value="latest">Latest First</option>
            <option value="earliest">Earliest First</option>
          </select>

          <button
            class="p-2 text-slate-400 transition-colors hover:text-slate-900 dark:hover:text-slate-200"
          >
            <Download class="h-5 w-5" />
          </button>
          <button
            class="p-2 text-slate-400 transition-colors hover:text-slate-900 dark:hover:text-slate-200"
          >
            <Mail class="h-5 w-5" />
          </button>
        </div>
      </div>

      <div
        class="overflow-hidden rounded-[2.5rem] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
      >
        <table class="w-full text-left">
          <thead>
            <tr
              class="border-b border-slate-100 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-800/50"
            >
              <th
                class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase"
              >
                Participant
              </th>
              <th
                class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase"
              >
                Registration
              </th>
              <th
                class="px-8 py-5 text-right text-xs font-bold tracking-widest text-slate-400 uppercase"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
            <tr v-if="!participants.data?.length">
              <td
                colspan="3"
                class="px-8 py-12 text-center font-light text-slate-400 italic"
              >
                No one has registered for this event yet.
              </td>
            </tr>
            <tr
              v-else
              v-for="participant in participants.data"
              :key="participant.id"
              class="group transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50"
            >
              <td class="px-8 py-6">
                <div class="flex items-center gap-4">
                  <div
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-900 dark:bg-slate-800 dark:text-slate-100"
                  >
                    {{ (participant.name || participant.user?.name || "G").charAt(0) }}
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 dark:text-slate-100">
                      {{
                        participant.name || participant.user?.name || "Guest Participant"
                      }}
                    </div>
                    <div
                      class="text-[11px] font-light text-slate-500 dark:text-slate-400"
                    >
                      {{
                        participant.email ||
                        participant.user?.email ||
                        "No email provided"
                      }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-8 py-6">
                <span
                  class="rounded-full px-3 py-1 text-[10px] font-black tracking-tighter uppercase"
                  :class="
                    participant.registration_status === 'confirmed'
                      ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                      : 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400'
                  "
                >
                  {{ participant.registration_status }}
                </span>
              </td>
              <td class="px-8 py-6 text-right">
                <button
                  class="p-2 text-slate-300 transition-colors hover:text-slate-600 dark:hover:text-slate-400"
                >
                  <MoreVertical class="h-5 w-5" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="participants.links?.length > 3" class="flex justify-center pt-4">
        <div class="flex gap-1">
          <template v-for="(link, p) in participants.links" :key="p">
            <div
              v-if="link.url === null"
              class="rounded-xl px-4 py-2 text-sm text-slate-400"
              v-html="link.label"
            />
            <Link
              v-else
              :href="link.url"
              class="rounded-xl px-4 py-2 text-sm font-medium transition-colors"
              :class="
                link.active
                  ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                  : 'bg-white text-slate-600 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'
              "
              v-html="link.label"
            />
          </template>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { Link, usePage, Head, router } from "@inertiajs/vue3";
import {
  Calendar,
  MapPin,
  Clock,
  Download,
  Mail,
  ArrowLeft,
  MoreVertical,
  Pencil,
  Trash2,
} from "@lucide/vue";
import QrcodeVue from "qrcode.vue";
import { computed, ref, watch } from "vue";
import CertificateBuilder from "./Partials/CertificateBuilder.vue";
import EvaluationFormBuilder from "./Partials/EvaluationFormBuilder.vue";
import type { Event } from "@/types/Event";

const props = defineProps<{
  event: Event;
  participants: any;
  filters: { status: string; sort: string };
}>();

const filtersForm = ref({
  status: props.filters.status || 'all',
  sort: props.filters.sort || 'latest',
});

watch(
  filtersForm,
  (newFilters) => {
    router.get(
      window.location.pathname,
      { status: newFilters.status, sort: newFilters.sort },
      { preserveState: true, preserveScroll: true, replace: true }
    );
  },
  { deep: true }
);

const page = usePage();
const auth = computed(() => page.props.auth as any);
const role = computed(() => auth.value.user.role.name);

const registrationUrl = computed(
  () => `${window.location.origin}/events/${props.event.registration_token}/register`
);

const copyRegistrationLink = () => {
  navigator.clipboard.writeText(registrationUrl.value);
  alert("Registration link copied to clipboard!");
};

const deleteEvent = () => {
  if (confirm("Are you sure you want to delete this event? This action cannot be undone.")) {
    router.delete(`/admin/events/${props.event.id}`);
  }
};
</script>
