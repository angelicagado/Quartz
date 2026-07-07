<template>
  <Head title="System Overview" />
  <div class="mx-auto max-w-7xl space-y-6 px-0 pb-10 sm:space-y-8 sm:px-2">
    <!-- Header Profile Dashboard -->
    <div
      class="relative flex flex-col justify-between gap-6 overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 p-6 text-white shadow-2xl transition-all duration-500 hover:shadow-[#d4af37]/5 sm:p-10 md:flex-row md:items-center lg:p-12"
    >
      <div
        class="absolute top-0 right-0 h-[500px] w-[500px] translate-x-1/3 -translate-y-1/2 rounded-full bg-[#d4af37]/10 blur-3xl"
      ></div>

      <div class="relative z-10 flex-1 space-y-4">
        <div
          class="mb-2 inline-flex items-center gap-2 rounded-full border border-slate-700/50 bg-slate-800/80 px-3 py-1.5 text-[10px] font-bold tracking-widest text-[#d4af37] uppercase shadow-sm backdrop-blur-sm sm:text-xs"
        >
          <ShieldCheck class="h-3.5 h-4 w-3.5 sm:w-4" />
          SUPER ADMIN ACCESS
        </div>
        <h1
          class="font-serif text-3xl leading-tight font-extrabold tracking-tight text-white drop-shadow-sm sm:text-4xl lg:text-6xl"
        >
          System <span class="text-[#d4af37]">Overview</span>
        </h1>
        <p
          class="max-w-2xl text-base leading-relaxed font-light text-slate-300 sm:text-lg"
        >
          Welcome to the master control panel. You have full access to manage all users,
          configurations, and global events across the entire
          <span class="font-medium text-white">Quartz</span> platform.
        </p>
      </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4 lg:gap-6">
      <div
        class="flex flex-col items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-white p-5 text-center shadow-sm transition-shadow hover:shadow-md sm:flex-row sm:items-start sm:p-6 sm:text-left dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="order-2 sm:order-1">
          <p
            class="mb-1 text-[10px] font-bold tracking-widest text-slate-400 uppercase sm:text-xs"
          >
            Total Users
          </p>
          <h3
            class="font-serif text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
          >
            {{ stats.totalUsers.toLocaleString() }}
          </h3>
        </div>
        <div
          class="order-1 flex h-10 h-12 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 sm:order-2 sm:w-12 dark:bg-blue-500/10 dark:text-blue-400"
        >
          <Users class="h-5 h-6 w-5 sm:w-6" />
        </div>
      </div>

      <div
        class="flex flex-col items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-white p-5 text-center shadow-sm transition-shadow hover:shadow-md sm:flex-row sm:items-start sm:p-6 sm:text-left dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="order-2 sm:order-1">
          <p
            class="mb-1 text-[10px] font-bold tracking-widest text-slate-400 uppercase sm:text-xs"
          >
            Active Events
          </p>
          <h3
            class="font-serif text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
          >
            {{ stats.activeEvents }}
          </h3>
        </div>
        <div
          class="order-1 flex h-10 h-12 w-10 shrink-0 items-center justify-center rounded-xl bg-[#d4af37]/10 text-[#d4af37] sm:order-2 sm:w-12"
        >
          <Activity class="h-5 h-6 w-5 sm:w-6" />
        </div>
      </div>

      <div
        class="flex flex-col items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-white p-5 text-center shadow-sm transition-shadow hover:shadow-md sm:flex-row sm:items-start sm:p-6 sm:text-left dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="order-2 sm:order-1">
          <p
            class="mb-1 text-[10px] font-bold tracking-widest text-slate-400 uppercase sm:text-xs"
          >
            Organizers
          </p>
          <h3
            class="font-serif text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
          >
            {{ stats.totalOrganizers }}
          </h3>
        </div>
        <div
          class="order-1 flex h-10 h-12 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 sm:order-2 sm:w-12 dark:bg-emerald-500/10 dark:text-emerald-400"
        >
          <ShieldCheck class="h-5 h-6 w-5 sm:w-6" />
        </div>
      </div>

      <div
        class="flex flex-col items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-white p-5 text-center shadow-sm transition-shadow hover:shadow-md sm:flex-row sm:items-start sm:p-6 sm:text-left dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="order-2 sm:order-1">
          <p
            class="mb-1 text-[10px] font-bold tracking-widest text-slate-400 uppercase sm:text-xs"
          >
            Health
          </p>
          <h3
            class="font-serif text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
          >
            {{ stats.systemHealth }}
          </h3>
        </div>
        <div
          class="order-1 flex h-10 h-12 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 sm:order-2 sm:w-12 dark:bg-indigo-500/10 dark:text-indigo-400"
        >
          <Settings class="h-5 h-6 w-5 sm:w-6" />
        </div>
      </div>
    </div>

    <!-- Management Section -->
    <div class="pt-4 sm:pt-6">
      <h2
        class="mb-6 flex items-center gap-2 font-serif text-2xl font-bold tracking-tight text-slate-900 sm:mb-8 dark:text-slate-100"
      >
        Global Management
      </h2>
      <div class="grid grid-cols-1 gap-6 sm:gap-8 md:grid-cols-2 lg:grid-cols-3">
        <Link
          href="/super-admin/users"
          class="group relative flex min-h-[260px] cursor-pointer flex-col justify-between overflow-hidden rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition-all duration-500 hover:shadow-xl active:scale-[0.98] sm:p-8 dark:border-slate-800 dark:bg-slate-900"
        >
          <div class="relative z-10 mb-6 flex items-center justify-between">
            <div
              class="flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 text-slate-400 shadow-inner transition-all duration-500 group-hover:scale-110 group-hover:rotate-3 group-hover:bg-[#d4af37] group-hover:text-white dark:border-slate-800 dark:bg-slate-800"
            >
              <Users class="h-8 w-8" />
            </div>
          </div>
          <div class="relative z-10 mt-auto">
            <h3
              class="mb-3 font-serif text-2xl leading-tight font-bold tracking-tight text-slate-900 transition-colors group-hover:text-[#d4af37] dark:text-slate-100"
            >
              User Management
            </h3>
            <p
              class="text-[15px] leading-relaxed font-light text-slate-500 dark:text-slate-400"
            >
              Create new users, assign roles (Admin, Organizer), and activate or
              deactivate accounts.
            </p>
          </div>
        </Link>

        <Link
          href="/super-admin/events"
          class="group relative flex min-h-[260px] cursor-pointer flex-col justify-between overflow-hidden rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition-all duration-500 hover:shadow-xl active:scale-[0.98] sm:p-8 dark:border-slate-800 dark:bg-slate-900"
        >
          <div class="relative z-10 mb-6 flex items-center justify-between">
            <div
              class="flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 text-slate-400 shadow-inner transition-all duration-500 group-hover:scale-110 group-hover:-rotate-3 group-hover:bg-slate-900 group-hover:text-white dark:border-slate-800 dark:bg-slate-800"
            >
              <Activity class="h-8 w-8" />
            </div>
            <span
              class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-[10px] font-bold tracking-widest text-slate-400 uppercase shadow-sm dark:border-slate-700 dark:bg-slate-800"
              >VIEW ONLY</span
            >
          </div>
          <div class="relative z-10 mt-auto">
            <h3
              class="mb-3 font-serif text-2xl leading-tight font-bold tracking-tight text-slate-900 transition-colors group-hover:text-slate-800 dark:text-slate-100 dark:group-hover:text-slate-300"
            >
              Global Events & Reports
            </h3>
            <p
              class="text-[15px] leading-relaxed font-light text-slate-500 dark:text-slate-400"
            >
              Monitor all events globally, view attendance logs, and export high-level
              summary reports.
            </p>
          </div>
        </Link>
      </div>
    </div>

    <!-- Live Event Tracking -->
    <div class="pt-4 sm:pt-6">
      <div class="mb-6 flex items-center justify-between">
        <h2
          class="flex items-center gap-2 font-serif text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100"
        >
          <Activity class="h-6 w-6 text-[#d4af37]" />
          Live Event Feed
        </h2>
        <div
          class="flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1"
        >
          <div class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></div>
          <span class="text-[10px] font-black tracking-widest text-emerald-600 uppercase"
            >Events Happening Today</span
          >
        </div>
      </div>

      <div
        class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="divide-y divide-slate-50 dark:divide-slate-800">
          <div
            v-if="liveEvents.length === 0"
            class="p-16 text-center font-light text-slate-400 italic"
          >
            No events scheduled for today.
          </div>
          <div
            v-else
            v-for="event in liveEvents"
            :key="event.id"
            class="group flex items-center justify-between p-5 transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50"
          >
            <div class="flex items-center gap-4">
              <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-xs font-bold text-[#d4af37] ring-4 ring-slate-900/5 transition-transform group-hover:scale-110"
              >
                EV
              </div>
              <div>
                <p class="leading-tight font-bold text-slate-900 dark:text-slate-100">
                  {{ event.title }}
                </p>
                <p
                  class="mt-0.5 text-[11px] font-medium tracking-wider text-slate-400 uppercase"
                >
                  {{ event.organizer?.name || "No Organizer" }}
                </p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-xs font-bold text-slate-900 dark:text-slate-100">
                {{
                  new Date(event.start_time).toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit",
                  })
                }}
                -
                {{
                  new Date(event.end_time).toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit",
                  })
                }}
              </p>
              <p class="text-[10px] font-black tracking-tighter text-slate-400 uppercase">
                {{ new Date(event.start_time).toLocaleDateString() }}
              </p>
            </div>
          </div>
        </div>
        <Link
          v-if="liveEvents.length > 0"
          href="/events"
          class="block w-full border-t border-slate-50 py-4 text-center text-xs font-bold tracking-widest text-slate-400 uppercase transition-colors hover:text-[#d4af37] dark:border-slate-800 dark:hover:bg-slate-800"
        >
          View All Events
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link, Head } from "@inertiajs/vue3";
import { ShieldCheck, Users, Activity, Settings } from "@lucide/vue";

const props = defineProps<{
  stats: {
    totalUsers: number;
    activeEvents: number;
    totalOrganizers: number;
    upcomingEvents: number;
    systemHealth: string;
  };
  liveEvents: any[];
}>();
</script>
