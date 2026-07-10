<script setup lang="ts">
import { usePage, router, Head, Link } from "@inertiajs/vue3";
import { Plus, Search, Filter, X } from "@lucide/vue";
import { ref, computed } from "vue";

import EventCard from "@/components/EventCard.vue";

interface Event {
  id: number;
  title: string;
  description: string;
  start_time: string;
  end_time: string;
  registration_type?: string;
  certificate_enabled?: boolean;
  evaluation_required?: boolean;
  [key: string]: any;
}

const props = defineProps<{
  events: Event[];
}>();

const page = usePage();
const auth = computed(() => page.props.auth as any);
const role = computed(() => auth.value.user.role.name);

const searchQuery = ref("");
const activeFilter = ref("all");
const isAdvancedFilterOpen = ref(false);
const registrationTypeFilter = ref("all");
const certificateFilter = ref("all");
const evaluationFilter = ref("all");

const filteredEvents = computed(() => {
  return props.events.filter((event) => {
    const matchesSearch =
      event.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      event.description.toLowerCase().includes(searchQuery.value.toLowerCase());

    const isUpcoming = new Date(event.start_time) > new Date();
    const matchesFilter =
      activeFilter.value === "all" ||
      (activeFilter.value === "upcoming" && isUpcoming) ||
      (activeFilter.value === "past" && !isUpcoming);

    const matchesRegistration =
      registrationTypeFilter.value === "all" ||
      event.registration_type === registrationTypeFilter.value;
    const matchesCertificate =
      certificateFilter.value === "all" ||
      (certificateFilter.value === "enabled" && event.certificate_enabled) ||
      (certificateFilter.value === "disabled" && !event.certificate_enabled);
    const matchesEvaluation =
      evaluationFilter.value === "all" ||
      (evaluationFilter.value === "required" && event.evaluation_required) ||
      (evaluationFilter.value === "not_required" && !event.evaluation_required);

    return (
      matchesSearch &&
      matchesFilter &&
      matchesRegistration &&
      matchesCertificate &&
      matchesEvaluation
    );
  });
});

const hasActiveFilters = computed(() => {
  return (
    searchQuery.value !== "" ||
    activeFilter.value !== "all" ||
    registrationTypeFilter.value !== "all" ||
    certificateFilter.value !== "all" ||
    evaluationFilter.value !== "all"
  );
});



const resetFilters = () => {
  searchQuery.value = "";
  activeFilter.value = "all";
  registrationTypeFilter.value = "all";
  certificateFilter.value = "all";
  evaluationFilter.value = "all";
};

const handleViewEvent = (event: Event) => {
  let prefix = "";
  if (["super_admin", "admin"].includes(role.value)) {
    prefix = "/admin";
  } else if (role.value === "organizer") {
    prefix = "/organizer";
  }
  router.get(`${prefix}/events/${event.id}`);
};
</script>

<template>
  <Head title="Events" />
  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
      <div>
        <h1
          class="font-serif text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100"
        >
          Events Management
        </h1>
        <p class="mt-1 text-slate-500 dark:text-slate-400">
          Create, manage, and track your organization's events.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <div class="relative">
          <Search
            class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-slate-400"
          />
          <input
            type="text"
            placeholder="Search events..."
            v-model="searchQuery"
            class="w-full rounded-xl border border-slate-200 bg-white py-2 pr-4 pl-10 text-slate-600 shadow-sm transition-all outline-none placeholder:text-slate-400 focus:border-[#d4af37] focus:ring-2 focus:ring-[#d4af37]/20 sm:w-64 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
          />
        </div>
        <Link
          v-if="['admin', 'super_admin'].includes(role)"
          href="/admin/events/create"
          class="hidden items-center gap-2 rounded-xl bg-linear-to-br from-slate-800 from-40% to-[#d4af37] px-5 py-2.5 font-medium text-white shadow-[0_4px_12px_rgba(30,41,59,0.25)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-slate-800 hover:shadow-[0_6px_16px_rgba(30,41,59,0.35)] active:translate-y-0 md:flex"
        >
          <Plus class="h-5 w-5" />
          Create Event
        </Link>
        <Link
          v-if="['admin', 'super_admin'].includes(role)"
          href="/admin/events/create"
          class="flex h-11 w-11 items-center justify-center rounded-full bg-[#1E293B] text-white shadow-md hover:bg-slate-800 active:scale-[0.98] md:hidden"
        >
          <Plus class="h-5 w-5" />
        </Link>
      </div>
    </div>

    <!-- Filters -->
    <div class="hide-scrollbar mb-6 flex items-center gap-2 overflow-x-auto pb-2">
      <button
        @click="activeFilter = 'all'"
        class="rounded-lg border px-5 py-2 font-medium whitespace-nowrap transition-colors"
        :class="
          activeFilter === 'all'
            ? 'border-[#d4af37]/20 bg-[#d4af37]/10 text-[#d4af37]'
            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'
        "
      >
        All Events
      </button>
      <button
        @click="activeFilter = 'upcoming'"
        class="rounded-lg border px-5 py-2 font-medium whitespace-nowrap transition-colors"
        :class="
          activeFilter === 'upcoming'
            ? 'border-[#d4af37]/20 bg-[#d4af37]/10 text-[#d4af37]'
            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'
        "
      >
        Upcoming
      </button>
      <button
        @click="activeFilter = 'past'"
        class="rounded-lg border px-5 py-2 font-medium whitespace-nowrap transition-colors"
        :class="
          activeFilter === 'past'
            ? 'border-[#d4af37]/20 bg-[#d4af37]/10 text-[#d4af37]'
            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'
        "
      >
        Past
      </button>
      <button
        @click="isAdvancedFilterOpen = !isAdvancedFilterOpen"
        class="ml-auto flex items-center gap-2 rounded-lg border px-4 py-2 font-medium shadow-sm transition-all"
        :class="
          isAdvancedFilterOpen
            ? 'border-slate-900 bg-slate-900 text-white dark:border-slate-100 dark:bg-slate-100 dark:text-slate-900'
            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'
        "
      >
        <Filter class="h-4 w-4" />
        {{ isAdvancedFilterOpen ? "Hide Filters" : "More Filters" }}
      </button>
    </div>

    <!-- Advanced Filter Panel -->
    <div
      v-if="isAdvancedFilterOpen"
      class="mb-8 animate-in rounded-2xl border border-slate-200 bg-white p-6 shadow-sm duration-300 fade-in slide-in-from-top-4 dark:border-slate-700 dark:bg-slate-800"
    >
      <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div>
          <label
            class="mb-2 block text-xs font-bold tracking-widest text-slate-400 uppercase"
            >Registration Type</label
          >
          <select
            v-model="registrationTypeFilter"
            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600 transition-all outline-none focus:border-[#d4af37] dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200"
          >
            <option value="all">Internal & External</option>
            <option value="public">Public Events</option>
            <option value="private">Private (Invite only)</option>
            <option value="internal">Staff Only</option>
          </select>
        </div>
        <div>
          <label
            class="mb-2 block text-xs font-bold tracking-widest text-slate-400 uppercase"
            >Certificate Status</label
          >
          <select
            v-model="certificateFilter"
            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600 transition-all outline-none focus:border-[#d4af37] dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200"
          >
            <option value="all">Any Status</option>
            <option value="enabled">With Certificate</option>
            <option value="disabled">No Certificate</option>
          </select>
        </div>
        <div>
          <label
            class="mb-2 block text-xs font-bold tracking-widest text-slate-400 uppercase"
            >Evaluation Requirement</label
          >
          <select
            v-model="evaluationFilter"
            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600 transition-all outline-none focus:border-[#d4af37] dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200"
          >
            <option value="all">Any Requirement</option>
            <option value="required">Evaluation Required</option>
            <option value="not_required">Optional Feedack</option>
          </select>
        </div>
      </div>
      <div
        class="mt-4 flex justify-end border-t border-slate-100 pt-4 dark:border-slate-700"
      >
        <button
          @click="resetFilters"
          class="text-xs font-bold tracking-widest text-rose-500 uppercase transition-colors hover:text-rose-600"
        >
          Reset All Filters
        </button>
      </div>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <!-- Create New Card (Admin Only) -->
      <Link
        href="/admin/events/create"
        v-if="['admin', 'super_admin'].includes(role) && !hasActiveFilters"
        class="group flex min-h-[360px] cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/50 p-8 transition-all duration-300 hover:-translate-y-1 hover:border-[#d4af37]/50 hover:bg-white hover:shadow-lg dark:border-slate-700 dark:bg-slate-800/50 dark:hover:bg-slate-800"
      >
        <div
          class="relative mb-5 flex h-16 w-16 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 shadow-sm transition-all duration-300 group-hover:border-[#d4af37]/20 group-hover:bg-[#d4af37]/10 group-hover:text-[#d4af37] dark:border-slate-600 dark:bg-slate-700"
        >
          <div
            class="absolute inset-0 scale-0 rounded-full bg-[#d4af37]/20 transition-all duration-700 ease-out group-hover:scale-150 group-hover:opacity-0"
          ></div>
          <Plus class="relative z-10 h-7 w-7" />
        </div>
        <h3
          class="font-serif text-lg font-semibold text-slate-800 transition-colors group-hover:text-slate-900 dark:text-slate-200 dark:group-hover:text-white"
        >
          Create New Event
        </h3>
        <p
          class="mt-2 max-w-[200px] text-center text-sm leading-relaxed text-slate-500 dark:text-slate-400"
        >
          Organize a new conference, seminar, or specialized workshop.
        </p>
      </Link>

      <!-- Rendering Cards from DB -->
      <EventCard
        v-for="event in filteredEvents"
        :key="event.id"
        :event="event"
        @view="handleViewEvent(event)"
      />

      <div
        v-if="filteredEvents.length === 0 && hasActiveFilters"
        class="col-span-full animate-in rounded-[2.5rem] border border-slate-100 bg-white py-20 text-center shadow-sm transition-all duration-500 fade-in slide-in-from-bottom-4 dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="flex flex-col items-center gap-4">
          <Search class="h-12 w-12 text-slate-200 dark:text-slate-700" />
          <div>
            <p class="text-xl font-bold text-slate-900 dark:text-slate-100">
              No events found
            </p>
            <p class="mt-1 font-light text-slate-500 dark:text-slate-400">
              Try adjusting your search or category filters.
            </p>
          </div>
          <button
            @click="resetFilters"
            class="mt-2 font-bold text-[#d4af37] hover:underline"
          >
            Clear all filters
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
