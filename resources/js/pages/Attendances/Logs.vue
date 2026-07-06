<template>
  <Head title="Live Attendance Logs" />
    <div class="mx-auto max-w-7xl animate-in space-y-8 pb-10 font-['Outfit'] duration-500 fade-in">
      <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div class="space-y-1">
          <div class="inline-flex items-center gap-2 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-[10px] font-bold tracking-widest text-indigo-600 uppercase dark:border-indigo-500/30 dark:bg-indigo-500/20 dark:text-indigo-400">
            <Activity class="h-3 w-3" />
            Live Logs
          </div>
          <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Live Attendance
          </h1>
          <p class="text-slate-500 dark:text-slate-400">
            Monitor real-time check-ins and track who has arrived at the event.
          </p>
        </div>
      </div>

      <div class="min-h-[400px] overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
          <table class="w-full text-left whitespace-nowrap">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-800/50">
                <th class="flex max-w-min items-center gap-2 px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase">
                  <Calendar class="h-4 w-4" />
                  Timestamp
                </th>
                <th class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase">Participant</th>
                <th class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase">Event</th>
                <th class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase">Status / Mode</th>
                <th v-if="role !== 'organizer'" class="px-8 py-5 text-right text-xs font-bold tracking-widest text-slate-400 uppercase">Scanned By</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
              <tr v-if="logs.data.length === 0">
                <td :colspan="role !== 'organizer' ? 5 : 4" class="px-8 py-20 text-center">
                  <div class="flex flex-col items-center gap-3 text-slate-300 dark:text-slate-600">
                    <Activity class="h-12 w-12 opacity-20" />
                    <p class="font-light italic">No attendance records found.</p>
                  </div>
                </td>
              </tr>
              <tr v-else v-for="log in logs.data" :key="log.id" class="group transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                <td class="px-8 py-6">
                  <div class="flex flex-col">
                    <span class="w-max rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm font-bold text-slate-900 shadow-sm transition-colors group-hover:border-[#C5A059]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                      {{ new Date(log.scanned_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                    </span>
                    <span class="mt-2 text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">
                      {{ new Date(log.scanned_at).toLocaleDateString() }}
                    </span>
                  </div>
                </td>
                <td class="px-8 py-6">
                  <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl border border-indigo-100 bg-indigo-50 font-bold text-indigo-500 transition-transform group-hover:scale-110 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-400">
                      {{ (log.participant?.name || log.participant?.user?.name || 'G').charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="text-[15px] font-bold text-slate-900 dark:text-slate-100">
                        {{ log.participant?.name || log.participant?.user?.name || 'Guest' }}
                      </p>
                      <div class="mt-0.5 flex items-center gap-1.5">
                        <span class="rounded-md bg-emerald-500/10 px-1.5 py-0.5 text-[9px] font-black tracking-widest text-emerald-600 uppercase dark:text-emerald-400">
                          Verified Check-in
                        </span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-8 py-6">
                  <div class="flex items-center gap-2">
                    <ArrowRight class="h-4 w-4 text-slate-300 dark:text-slate-600" />
                    <span class="max-w-[200px] truncate text-[13px] font-semibold text-slate-600 dark:text-slate-300">
                      {{ log.participant?.event?.title }}
                    </span>
                  </div>
                </td>
                <td class="px-8 py-6">
                  <span
                    class="rounded-xl px-4 py-2 text-[11px] font-black tracking-widest uppercase"
                    :class="log.scan_type.includes('in') ? 'border border-emerald-100 bg-emerald-50 text-emerald-600 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400' : 'border border-amber-100 bg-amber-50 text-amber-600 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-400'"
                  >
                    {{ log.scan_type.replace('_', ' ') }}
                  </span>
                </td>
                <td v-if="role !== 'organizer'" class="px-8 py-6 text-right">
                  <div class="flex flex-col items-end">
                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ log.scanner?.name || 'System' }}</span>
                    <span class="text-[10px] font-light tracking-widest text-slate-400 uppercase">Operator</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="logs.links && logs.links.length > 3" class="flex items-center justify-center gap-1 border-t border-slate-100 p-4 dark:border-slate-800">
          <Link
            v-for="(link, i) in logs.links"
            :key="i"
            :href="link.url || '#'"
            class="rounded-lg px-3 py-1.5 text-sm font-medium"
            :class="link.active ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800'"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage, Head } from '@inertiajs/vue3';
import { Activity, Search, Filter, Calendar, User, ArrowRight, Link as LinkIcon, Download } from '@lucide/vue';

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

const props = defineProps<{
    logs: {
        data: Log[];
        links: any;
    };
}>();

const page = usePage();
const auth = computed(() => page.props.auth as any);
const role = computed(() => auth.value.user.role.name);

const role = computed(() => auth.value.user.role.name);
</script>
