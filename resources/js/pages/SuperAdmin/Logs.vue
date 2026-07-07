<template>
  <Head title="System Logs" />
  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
      <div class="space-y-1">
        <div
          class="inline-flex items-center gap-2 rounded-full border border-[#d4af37]/20 bg-[#d4af37]/10 px-3 py-1 text-[10px] font-bold tracking-widest text-[#d4af37] uppercase"
        >
          <Activity class="h-3 w-3" />
          System Audit
        </div>
        <h1
          class="font-serif text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100"
        >
          System Logs
        </h1>
        <p class="text-slate-500 dark:text-slate-400">
          Real-time audit trail of all attendance and system activities.
        </p>
      </div>
    </div>

    <div
      class="min-h-[400px] overflow-hidden rounded-[2.5rem] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
      <table class="w-full text-left">
        <thead>
          <tr
            class="border-b border-slate-100 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-800/50"
          >
            <th
              class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
            >
              Timestamp
            </th>
            <th
              class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
            >
              Activity
            </th>
            <th
              class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
            >
              Participant
            </th>
            <th
              class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
            >
              Status / Mode
            </th>
            <th
              class="px-8 py-5 text-right text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
            >
              Performed By
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
          <tr v-if="logs.data.length === 0">
            <td colspan="5" class="px-8 py-20 text-center">
              <div
                class="flex flex-col items-center gap-3 text-slate-300 dark:text-slate-600"
              >
                <Activity class="h-12 w-12 opacity-20" />
                <p class="font-light italic">
                  No system activity logged in the vault yet.
                </p>
              </div>
            </td>
          </tr>
          <tr
            v-else
            v-for="log in logs.data"
            :key="log.id"
            class="group transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50"
          >
            <td class="px-8 py-6">
              <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400">
                <Calendar class="h-4 w-4" />
                <span class="text-xs font-medium">
                  {{ new Date(log.scanned_at).toLocaleString() }}
                </span>
              </div>
            </td>
            <td class="px-8 py-6">
              <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900 dark:text-slate-100">
                  QR Scan Check-in
                </span>
                <ArrowRight class="h-3 w-3 text-slate-300 dark:text-slate-600" />
                <span
                  class="max-w-[150px] truncate text-xs text-slate-500 dark:text-slate-400"
                >
                  {{ log.participant?.event?.title }}
                </span>
              </div>
            </td>
            <td class="px-8 py-6">
              <div class="flex items-center gap-2">
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-900 dark:bg-slate-800 dark:text-slate-100"
                >
                  {{
                    (log.participant?.name || log.participant?.user?.name || "G")?.charAt(
                      0
                    )
                  }}
                </div>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                  {{ log.participant?.name || log.participant?.user?.name || "Guest" }}
                </span>
              </div>
            </td>
            <td class="px-8 py-6">
              <span
                class="rounded-full px-3 py-1 text-[10px] font-black tracking-tighter uppercase"
                :class="
                  log.scan_type.includes('in')
                    ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                    : 'bg-amber-500/10 text-amber-600 dark:text-amber-400'
                "
              >
                {{ log.scan_type.replace("_", " ") }}
              </span>
            </td>
            <td class="px-8 py-6 text-right">
              <div class="flex flex-col items-end">
                <span class="text-sm font-bold text-slate-900 dark:text-slate-100">
                  {{ log.scanner?.name || "System" }}
                </span>
                <span
                  class="text-[10px] font-light tracking-widest text-slate-400 uppercase"
                >
                  Staff Operator
                </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Activity, Calendar, ArrowRight } from "@lucide/vue";
import { Head } from "@inertiajs/vue3";

interface Log {
  id: number;
  scan_type: string;
  scanned_at: string;
  participant?: {
    name?: string;
    email?: string;
    user?: {
      name: string;
    };
    event?: {
      title: string;
    };
  };
  scanner?: {
    name: string;
  };
}

defineProps<{
  logs: {
    data: Log[];
    links: any;
  };
}>();
</script>
