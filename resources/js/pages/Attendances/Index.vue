<template>
  <Head title="Attendance Scanner" />
  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
      <div class="space-y-1">
        <div
          class="inline-flex items-center gap-2 rounded-full border border-[#d4af37]/20 bg-[#d4af37]/10 px-3 py-1 text-[10px] font-bold tracking-widest text-[#d4af37] uppercase dark:border-slate-700 dark:bg-slate-800"
        >
          <Activity class="h-3 w-3" />
          Live Terminal
        </div>
        <h1
          class="font-serif text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100"
        >
          Attendance Scanner
        </h1>
        <p class="text-slate-500 dark:text-slate-400">
          Scan participant QR codes for entry and exit tracking.
        </p>
      </div>
    </div>

    <!-- Main Layout -->
    <div class="mt-8 flex flex-col gap-8 lg:flex-row-reverse">
      
      <!-- Card B (Right on desktop, Top on mobile) -->
      <div class="w-full lg:w-1/3">
        <div class="flex flex-col gap-6 rounded-[2.5rem] border border-slate-800 bg-slate-900 p-6 shadow-2xl sm:p-10 dark:border-slate-700 dark:bg-slate-800">
          <div class="space-y-4">
            <h3 class="font-serif text-xl font-bold text-white">Event Details</h3>
            
            <!-- Active Event Dropdown -->
            <div class="space-y-2">
              <label class="text-xs font-bold tracking-widest text-slate-400 uppercase">Select Event</label>
              <select v-model="selectedEventId" class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white focus:border-[#d4af37] focus:ring-1 focus:ring-[#d4af37] outline-none">
                <option value="" disabled>Select an active event...</option>
                <option v-for="event in events" :key="event.id" :value="event.id">
                  {{ event.title }}
                </option>
              </select>
            </div>

            <!-- Selected Event Info -->
            <div v-if="selectedEvent" class="rounded-xl border border-slate-700 bg-slate-800/50 p-4">
              <h4 class="font-bold text-white">{{ selectedEvent.title }}</h4>
              <div class="mt-2 space-y-1 text-xs text-slate-400">
                <p>
                  <span class="font-bold text-slate-300">Date:</span> 
                  {{ formatDate(selectedEvent.start_time) }}
                </p>
                <p>
                  <span class="font-bold text-slate-300">Time:</span> 
                  {{ formatTime(selectedEvent.start_time) }} - {{ formatTime(selectedEvent.end_time) }}
                </p>
              </div>
            </div>
            
            <!-- Scanner Mode Toggle -->
            <div class="space-y-2 pt-4 border-t border-slate-800">
              <label class="text-xs font-bold tracking-widest text-slate-400 uppercase">Input Method</label>
              <div class="flex rounded-xl border border-slate-700 bg-slate-800/50 p-1">
                <button
                  @click="mode = 'camera'"
                  class="flex-1 rounded-lg px-4 py-2 text-xs font-bold tracking-tight uppercase transition-all"
                  :class="mode === 'camera' ? 'bg-[#d4af37] text-white' : 'text-slate-500 hover:text-white'"
                >
                  Camera
                </button>
                <button
                  @click="mode = 'file'"
                  class="flex-1 rounded-lg px-4 py-2 text-xs font-bold tracking-tight uppercase transition-all"
                  :class="mode === 'file' ? 'bg-[#d4af37] text-white' : 'text-slate-500 hover:text-white'"
                >
                  Upload File
                </button>
              </div>
            </div>

            <!-- Start Button -->
            <button
              @click="handleStartAction"
              :disabled="!selectedEventId || isScanning"
              class="group relative mt-4 flex w-full items-center justify-center gap-3 overflow-hidden rounded-2xl py-4 text-sm font-bold tracking-widest uppercase shadow-xl transition-all"
              :class="
                !selectedEventId
                  ? 'cursor-not-allowed bg-slate-800 text-slate-500'
                  : isScanning && mode === 'camera'
                    ? 'border border-slate-700 bg-slate-800 text-slate-400'
                    : 'bg-gradient-to-r from-[#d4af37] to-[#b38d45] text-white hover:shadow-[#d4af37]/30 active:scale-[0.98]'
              "
            >
              <Camera v-if="mode === 'camera'" class="h-5 w-5" :class="{ 'animate-pulse': isScanning }" />
              <Upload v-else class="h-5 w-5" />
              
              <span v-if="!selectedEventId">Select Event to Start</span>
              <span v-else-if="mode === 'camera'">{{ isScanning ? 'Camera Active' : 'Start Camera Scanner' }}</span>
              <span v-else>Upload QR File</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Card A (Left on desktop, Bottom on mobile) -->
      <div class="w-full lg:w-2/3">
        <div
          class="relative h-full overflow-hidden rounded-[2.5rem] border border-slate-800 bg-slate-900 p-6 shadow-2xl sm:p-10 dark:border-slate-700 dark:bg-slate-800"
        >
          <div
            class="absolute top-0 right-0 h-64 w-64 translate-x-1/2 -translate-y-1/2 rounded-full bg-[#d4af37]/10 blur-3xl"
          ></div>

          <div class="relative z-10 h-full">
            <QRScanner 
              ref="scannerRef"
              :mode="mode" 
              v-model:isScanning="isScanning"
              @scan="handleScan" 
            />
          </div>
        </div>
      </div>

    </div>

    <div class="space-y-4 mt-8">
      <div class="flex items-center justify-between px-2">
        <h2
          class="flex items-center gap-2 font-serif text-xl font-bold text-slate-900 dark:text-slate-100"
        >
          <History class="h-5 w-5 text-[#d4af37]" />
          Recent Scans Feed
        </h2>
        <div class="flex items-center gap-3">
          <span class="text-xs font-bold tracking-widest text-slate-400 uppercase">
            {{ recentScans.length }} Scans
          </span>
          <div
            class="flex items-center gap-1.5 rounded-md border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5"
          >
            <div class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></div>
            <span
              class="text-[9px] font-black tracking-tighter text-emerald-600 uppercase"
            >
              Live
            </span>
          </div>
        </div>
      </div>

      <div
        class="min-h-[100px] overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="divide-y divide-slate-50 dark:divide-slate-800">
          <div
            v-if="recentScans.length === 0"
            class="flex flex-col items-center gap-3 p-12 text-center font-light text-slate-400 italic"
          >
            <div
              class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 dark:bg-slate-800"
            >
              <Users class="h-6 w-6 opacity-20" />
            </div>
            Scan a QR code to start the live feed.
          </div>

          <div
            v-else
            v-for="scan in recentScans"
            :key="scan.id"
            class="group flex animate-in items-center justify-between p-5 transition-all duration-500 slide-in-from-left-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/50"
          >
            <div class="flex items-center gap-4">
              <div
                class="flex h-10 w-10 items-center justify-center rounded-xl text-xs font-bold"
                :class="
                  scan.status === 'success'
                    ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                    : 'animate-shake bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'
                "
              >
                {{ scan.status === "success" ? "✓" : "!" }}
              </div>
              <div>
                <div class="font-bold text-slate-900 dark:text-slate-100">
                  {{ scan.name }}
                </div>
                <div
                  class="flex items-center gap-2 text-[10px] font-medium text-slate-400"
                >
                  <span class="tracking-widest uppercase" v-if="scan.type">{{
                    scan.type.replace("_", " ")
                  }}</span>
                  <span v-if="scan.type">•</span>
                  <span class="italic">{{ scan.event }}</span>
                </div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-xs font-bold text-slate-900 dark:text-slate-100">
                {{ scan.time }}
              </div>
              <div
                class="text-[9px] font-black tracking-tighter uppercase"
                :class="
                  scan.status === 'success' ? 'text-emerald-500' : 'text-rose-500'
                "
              >
                {{
                  scan.status === "success"
                    ? "Verification Passed"
                    : "Verification Failed"
                }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { usePage, Head } from "@inertiajs/vue3";
import axios from "axios";
import { ScanLine, History, Users, Activity, Camera, Upload } from "@lucide/vue";
import QRScanner from "@/components/QRScanner.vue";

const page = usePage();
const auth = computed(() => page.props.auth as any);
const events = computed(() => (page.props.events as any[]) || []);

const recentScans = ref<any[]>([]);

const selectedEventId = ref<number | string>("");
const mode = ref<'camera' | 'file'>('camera');
const isScanning = ref(false);
const scannerRef = ref<any>(null);

const selectedEvent = computed(() => {
  return events.value.find(e => e.id === selectedEventId.value);
});

const formatDate = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatTime = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const handleStartAction = () => {
  if (!scannerRef.value) return;
  
  if (mode.value === 'camera') {
    scannerRef.value.startCamera();
  } else {
    scannerRef.value.triggerFileUpload();
  }
};

const handleScan = async (token: string) => {
  if (!token) return;
  if (!selectedEventId.value) {
    alert("Please select an event first.");
    return;
  }

  let cleanToken = token.trim();
  if (cleanToken.includes("/")) {
    const parts = cleanToken.replace(/\/$/, "").split("/");
    cleanToken = parts[parts.length - 1];
  }

  try {
    console.log("Sending scan to backend:", cleanToken, "Event:", selectedEventId.value);
    
    // We use the specific event scan route instead of globalScan
    const response = await axios.post(`/events/${selectedEventId.value}/attendance/scan`, {
      token: cleanToken,
    });

    if (response.data.status === "success") {
      const newScan = {
        id: Date.now(),
        name: response.data.participant_name,
        event: selectedEvent.value?.title || 'Unknown Event',
        type: response.data.scan_type || 'Attendance',
        time:
          response.data.scanned_at ||
          new Date().toLocaleString("en-US", {
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
          }),
        status: "success",
      };
      recentScans.value = [newScan, ...recentScans.value].slice(0, 8);
    } else {
      alert("Scan Failed: " + (response.data.message || "Unknown Server Error"));
    }
  } catch (error: any) {
    console.error("Attendance Scan Failed:", error);
    const message = error.response?.data?.message || "Invalid Scan / Connection Error";

    alert("Scan Result: " + message);

    const errorScan = {
      id: Date.now(),
      name: message,
      event: selectedEvent.value?.title || "System Check",
      type: "error",
      time: new Date().toLocaleString("en-US", {
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
      }),
      status: "error",
    };
    recentScans.value = [errorScan, ...recentScans.value].slice(0, 8);
  }
};
</script>
