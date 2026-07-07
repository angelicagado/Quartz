<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import {
  Activity,
  Calendar as CalendarIcon,
  Clock,
  Layers,
  Plus,
  Search,
} from "@lucide/vue";
import { computed, onMounted, ref } from "vue";
import VueApexCharts from "vue3-apexcharts";

interface LinePoint {
  name: string;
  events: number;
  participants: number;
}

interface BarPoint {
  name: string;
  value: number;
}

interface Scan {
  id: number;
  scan_type: string;
  scanned_at: string;
  participant?: { name: string } | null;
  event?: { title: string } | null;
}

const props = defineProps<{
  stats: {
    upcomingEvents: number;
    pendingApprovals: number;
    totalEvents: number;
  };
  recentScans: Scan[];
  lineData: LinePoint[];
  barData: BarPoint[];
}>();

defineOptions({
  layout: {
    breadcrumbs: [{ title: "Super Admin", href: "/super-admin/dashboard" }],
  },
});

const page = usePage();
const adminName = computed(
  () => (page.props.auth.user as { name?: string } | null)?.name ?? "Admin"
);

// ApexCharts touches the DOM, so only render it after the client mounts.
const isMounted = ref(false);
onMounted(() => {
  isMounted.value = true;
});

const barColors = ["#1E293B", "#C5A059", "#94A3B8", "#0EA5E9", "#10B981"];

const lineSeries = computed(() => [
  { name: "Events", data: props.lineData.map((point) => point.events) },
  {
    name: "Participants",
    data: props.lineData.map((point) => point.participants),
  },
]);

const lineOptions = computed(() => ({
  chart: {
    type: "line" as const,
    fontFamily: "inherit",
    toolbar: { show: false },
    zoom: { enabled: false },
  },
  colors: ["#1E293B", "#C5A059"],
  stroke: { curve: "smooth" as const, width: 3 },
  dataLabels: { enabled: false },
  grid: { borderColor: "#f1f5f9", strokeDashArray: 4 },
  xaxis: {
    categories: props.lineData.map((point) => point.name),
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: { style: { colors: "#94a3b8", fontSize: "13px" } },
  },
  yaxis: {
    labels: { style: { colors: "#94a3b8", fontSize: "13px" } },
  },
  legend: {
    position: "bottom" as const,
    markers: { shape: "circle" as const },
    labels: { colors: "#64748b" },
    fontSize: "13px",
  },
  tooltip: { theme: "light" as const },
}));

const barSeries = computed(() => [
  { name: "Total Held", data: props.barData.map((point) => point.value) },
]);

const barOptions = computed(() => ({
  chart: {
    type: "bar" as const,
    fontFamily: "inherit",
    toolbar: { show: false },
  },
  colors: barColors,
  plotOptions: {
    bar: {
      horizontal: true,
      distributed: true,
      borderRadius: 6,
      borderRadiusApplication: "end" as const,
      barHeight: "55%",
    },
  },
  dataLabels: { enabled: false },
  grid: { borderColor: "#f1f5f9", strokeDashArray: 4 },
  legend: { show: false },
  xaxis: {
    categories: props.barData.map((point) => point.name),
    labels: { show: false },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: { style: { colors: "#475569", fontSize: "13px" } },
  },
  tooltip: { theme: "light" as const },
}));

const scanLabel = (scanType: string): string => {
  if (scanType === "am_in") {
    return "AM";
  }
  if (scanType === "pm_in") {
    return "PM";
  }
  return "✓";
};

const formatTime = (value: string): string => new Date(value).toLocaleTimeString();
const formatDate = (value: string): string => new Date(value).toLocaleDateString();
</script>

<template>
  <Head title="Admin Overview" />

  <div
    class="flex h-full flex-1 flex-col gap-6 p-6 animate-in duration-700 fade-in slide-in-from-bottom-4"
  >
    <!-- Header Row -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
      <div>
        <p
          class="mb-3 inline-flex items-center gap-2 rounded-full border border-[#C5A059]/20 bg-[#C5A059]/10 px-3 py-1 text-xs font-bold tracking-widest text-[#C5A059]"
        >
          ADMIN OVERVIEW
        </p>
        <h1
          class="font-serif text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl dark:text-slate-100"
        >
          Welcome back, {{ adminName }}!
        </h1>
        <p class="mt-2 max-w-xl text-slate-500 dark:text-slate-400">
          Manage event operations, create certificate templates, and handle participant
          registration.
        </p>
      </div>
    </div>

    <!-- Toolbar Row -->
    <div class="flex flex-col items-center justify-between gap-4 pt-4 sm:flex-row">
      <div class="relative w-full sm:w-96">
        <input
          type="text"
          placeholder="Search events, users, or certificates..."
          class="w-full rounded-2xl border border-slate-200 bg-white py-3.5 pr-4 pl-12 text-[15px] font-light text-slate-700 shadow-sm transition-all placeholder:text-slate-400 hover:border-slate-300 focus:border-[#C5A059] focus:ring-4 focus:ring-[#C5A059]/10 focus:outline-none"
        />
        <Search
          class="pointer-events-none absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-slate-400"
        />
      </div>

      <Link
        href="/events"
        class="group flex w-full items-center justify-center gap-2 rounded-2xl bg-linear-60 from-slate-800 from-50% to-primary-container px-7 py-3.5 text-[15px] font-medium text-white shadow-sm hover:to-50% hover: transition-all duration-300 active:scale-[0.98] sm:w-auto"
      >
        <Plus class="h-5 w-5 text-[#C5A059]" />
        Manage Events
      </Link>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 gap-6 pt-2 md:grid-cols-3">
      <div
        class="group relative flex items-start justify-between overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white p-7 shadow-sm transition-all hover:shadow-md"
      >
        <div
          class="absolute top-0 right-0 -z-10 h-32 w-32 rounded-bl-full bg-gradient-to-br from-[#C5A059]/5 to-transparent transition-transform duration-500 group-hover:scale-110"
        ></div>
        <div>
          <p class="mb-2 text-sm font-medium text-slate-500">Upcoming Events</p>
          <div class="mt-1 text-5xl font-semibold tracking-tight text-slate-900">
            {{ stats.upcomingEvents }}
          </div>
        </div>
        <div
          class="flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-100 bg-white shadow-sm"
        >
          <CalendarIcon class="h-7 w-7 text-[#C5A059]" />
        </div>
      </div>

      <div
        class="group relative flex items-start justify-between overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white p-7 shadow-sm transition-all hover:shadow-md"
      >
        <div
          class="absolute top-0 right-0 -z-10 h-32 w-32 rounded-bl-full bg-gradient-to-br from-slate-900/5 to-transparent transition-transform duration-500 group-hover:scale-110"
        ></div>
        <div>
          <p class="mb-2 text-sm font-medium text-slate-500">Pending Approvals</p>
          <div class="mt-1 text-5xl font-semibold tracking-tight text-slate-900">
            {{ stats.pendingApprovals }}
          </div>
        </div>
        <div
          class="flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-100 bg-white shadow-sm"
        >
          <Clock class="h-7 w-7 text-slate-700" />
        </div>
      </div>

      <div
        class="group relative flex items-start justify-between overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white p-7 shadow-sm transition-all hover:shadow-md"
      >
        <div
          class="absolute top-0 right-0 -z-10 h-32 w-32 rounded-bl-full bg-gradient-to-br from-[#C5A059]/5 to-transparent transition-transform duration-500 group-hover:scale-110"
        ></div>
        <div>
          <p class="mb-2 text-sm font-medium text-slate-500">Total Events Managed</p>
          <div class="mt-1 text-5xl font-semibold tracking-tight text-slate-900">
            {{ stats.totalEvents }}
          </div>
        </div>
        <div
          class="flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-100 bg-white shadow-sm"
        >
          <Layers class="h-7 w-7 text-[#C5A059]" />
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 gap-6 pt-4 lg:grid-cols-3">
      <div
        class="flex flex-col rounded-[1.5rem] border border-slate-100 bg-white p-7 shadow-sm transition-shadow hover:shadow-md lg:col-span-2"
      >
        <div class="mb-8 flex items-center justify-between">
          <h3 class="text-[1.15rem] font-semibold text-slate-900">Growth Analysis</h3>
          <Link
            href="/reports"
            class="rounded-full px-3 py-1 text-sm font-semibold text-[#C5A059] transition-colors hover:bg-[#C5A059]/10 hover:text-[#b38d45]"
          >
            View Full Report
          </Link>
        </div>
        <div class="mt-auto h-[320px] w-full">
          <VueApexCharts
            v-if="isMounted"
            type="line"
            height="100%"
            width="100%"
            :options="lineOptions"
            :series="lineSeries"
          />
        </div>
      </div>

      <div
        class="flex flex-col rounded-[1.5rem] border border-slate-100 bg-white p-7 shadow-sm transition-shadow hover:shadow-md lg:col-span-1"
      >
        <div class="mb-8 flex items-center justify-between">
          <h3 class="text-[1.15rem] font-semibold text-slate-900">Event Distribution</h3>
        </div>
        <div class="mt-auto h-[320px] w-full">
          <VueApexCharts
            v-if="isMounted && barData.length > 0"
            type="bar"
            height="100%"
            width="100%"
            :options="barOptions"
            :series="barSeries"
          />
          <div
            v-else-if="isMounted"
            class="flex h-full items-center justify-center text-center font-light text-slate-400 italic"
          >
            No events to chart yet.
          </div>
        </div>
      </div>
    </div>

    <!-- Live Attendance Tracking -->
    <div class="pt-4 sm:pt-6">
      <div class="mb-6 flex items-center justify-between">
        <h2
          class="flex items-center gap-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100"
        >
          <Activity class="h-6 w-6 text-[#C5A059]" />
          Live Attendance Feed
        </h2>
        <div
          class="flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1"
        >
          <div class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></div>
          <span class="text-[10px] font-black tracking-widest text-emerald-600 uppercase">
            Live System Status
          </span>
        </div>
      </div>

      <div
        class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="divide-y divide-slate-50 dark:divide-slate-800">
          <div
            v-if="recentScans.length === 0"
            class="p-16 text-center font-light text-slate-400 italic"
          >
            No attendance activity recorded yet.
          </div>
          <div
            v-for="scan in recentScans"
            v-else
            :key="scan.id"
            class="group flex items-center justify-between p-5 transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50"
          >
            <div class="flex items-center gap-4">
              <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-xs font-bold text-[#C5A059] ring-4 ring-slate-900/5 transition-transform group-hover:scale-110"
              >
                {{ scanLabel(scan.scan_type) }}
              </div>
              <div>
                <p class="leading-tight font-bold text-slate-900 dark:text-slate-100">
                  {{ scan.participant?.name || "Guest" }}
                </p>
                <p
                  class="mt-0.5 text-[11px] font-medium tracking-wider text-slate-400 uppercase"
                >
                  {{ scan.event?.title }}
                </p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-xs font-bold text-slate-900 dark:text-slate-100">
                {{ formatTime(scan.scanned_at) }}
              </p>
              <p class="text-[10px] font-black tracking-tighter text-slate-400 uppercase">
                {{ formatDate(scan.scanned_at) }}
              </p>
            </div>
          </div>
        </div>
        <Link
          v-if="recentScans.length > 0"
          href="/attendance"
          class="block w-full border-t border-slate-50 py-4 text-center text-xs font-bold tracking-widest text-slate-400 uppercase transition-colors hover:text-[#C5A059] dark:border-slate-800 dark:hover:bg-slate-800"
        >
          Manage Full Attendance
        </Link>
      </div>
    </div>
  </div>
</template>
