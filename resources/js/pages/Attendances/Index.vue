<template>
  <Head title="Attendance Scanner" />
    <div class="mx-auto max-w-5xl animate-in space-y-8 pb-20 duration-500 fade-in">
      <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div class="space-y-1">
          <div class="inline-flex items-center gap-2 rounded-full border border-[#C5A059]/20 bg-[#C5A059]/10 px-3 py-1 text-[10px] font-bold tracking-widest text-[#C5A059] uppercase dark:border-slate-700 dark:bg-slate-800">
            <Activity class="h-3 w-3" />
            Live Terminal
          </div>
          <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Attendance Scanner
          </h1>
          <p class="text-slate-500 dark:text-slate-400">
            Scan participant QR codes for entry and exit tracking.
          </p>
        </div>
      </div>

      <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-12">
        <div class="lg:col-span-12">
          <div class="relative overflow-hidden rounded-[2.5rem] border border-slate-800 bg-slate-900 p-6 shadow-2xl sm:p-10 dark:border-slate-700 dark:bg-slate-800">
            <div class="absolute top-0 right-0 h-64 w-64 translate-x-1/2 -translate-y-1/2 rounded-full bg-[#C5A059]/10 blur-3xl"></div>

            <div class="relative z-10">
              <QRScanner @scan="handleScan" />
            </div>
          </div>
        </div>

        <div class="space-y-4 lg:col-span-12">
          <div class="flex items-center justify-between px-2">
            <h2 class="flex items-center gap-2 text-xl font-bold text-slate-900 dark:text-slate-100">
              <History class="h-5 w-5 text-[#C5A059]" />
              Recent Scans Feed
            </h2>
            <div class="flex items-center gap-3">
              <span class="text-xs font-bold tracking-widest text-slate-400 uppercase">
                {{ recentScans.length }} Scans
              </span>
              <div class="flex items-center gap-1.5 rounded-md border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5">
                <div class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></div>
                <span class="text-[9px] font-black tracking-tighter text-emerald-600 uppercase">
                  Live
                </span>
              </div>
            </div>
          </div>

          <div class="min-h-[100px] overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="divide-y divide-slate-50 dark:divide-slate-800">
              <div v-if="recentScans.length === 0" class="flex flex-col items-center gap-3 p-12 text-center font-light text-slate-400 italic">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 dark:bg-slate-800">
                  <Users class="h-6 w-6 opacity-20" />
                </div>
                Scan a QR code to start the live feed.
              </div>
              
              <div v-else v-for="scan in recentScans" :key="scan.id" class="group flex animate-in items-center justify-between p-5 transition-all duration-500 slide-in-from-left-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                <div class="flex items-center gap-4">
                  <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl text-xs font-bold"
                    :class="scan.status === 'success' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'animate-shake bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'"
                  >
                    {{ scan.status === 'success' ? '✓' : '!' }}
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ scan.name }}</div>
                    <div class="flex items-center gap-2 text-[10px] font-medium text-slate-400">
                      <span class="tracking-widest uppercase">{{ scan.type.replace('_', ' ') }}</span>
                      <span>•</span>
                      <span class="italic">{{ scan.event }}</span>
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ scan.time }}</div>
                  <div
                    class="text-[9px] font-black tracking-tighter uppercase"
                    :class="scan.status === 'success' ? 'text-emerald-500' : 'text-rose-500'"
                  >
                    {{ scan.status === 'success' ? 'Verification Passed' : 'Verification Failed' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ScanLine, History, Users, Activity } from '@lucide/vue';
import QRScanner from '@/components/QRScanner.vue';

const page = usePage();
const auth = computed(() => page.props.auth as any);
const role = computed(() => auth.value.user.role.name);

const recentScans = ref<any[]>([]);

const handleScan = async (token: string, type: string) => {
    if (!token) return;

    let cleanToken = token.trim();
    if (cleanToken.includes('/')) {
        const parts = cleanToken.replace(/\/$/, '').split('/');
        cleanToken = parts[parts.length - 1];
    }

    try {
        console.log('Sending scan to backend:', cleanToken, type);
        const response = await axios.post('/attendance/scan', {
            qr_token: cleanToken,
            scan_type: type,
        });

        if (response.data.status === 'success') {
            const newScan = {
                id: response.data.data.id,
                name: response.data.participant_name,
                event: response.data.event_title,
                type: response.data.data.scan_type,
                time: response.data.scanned_at || new Date(response.data.data.scanned_at).toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                }),
                status: 'success',
            };
            recentScans.value = [newScan, ...recentScans.value].slice(0, 8);
        } else {
            alert('Scan Failed: ' + (response.data.message || 'Unknown Server Error'));
        }
    } catch (error: any) {
        console.error('Attendance Scan Failed:', error);
        const message = error.response?.data?.message || 'Invalid Scan / Connection Error';

        alert('Scan Result: ' + message);

        const errorScan = {
            id: Date.now(),
            name: message,
            event: 'System Check',
            type: type,
            time: new Date().toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            }),
            status: 'error',
        };
        recentScans.value = [errorScan, ...recentScans.value].slice(0, 8);
    }
};
</script>
