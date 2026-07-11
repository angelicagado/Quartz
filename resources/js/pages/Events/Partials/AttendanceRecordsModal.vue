<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative z-10 w-full max-w-lg transform overflow-hidden rounded-[2rem] bg-white shadow-2xl transition-all flex flex-col max-h-[90vh] dark:bg-slate-900">
      
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-slate-800">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
            <History class="h-5 w-5" />
          </div>
          <div>
            <h3 class="font-serif text-lg font-bold text-slate-900 dark:text-white">Attendance Records</h3>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
              {{ participant?.user?.name }}
            </p>
          </div>
        </div>
        <button @click="$emit('close')" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors dark:hover:bg-slate-800 dark:hover:text-slate-300">
          <X class="h-5 w-5" />
        </button>
      </div>

      <!-- Body -->
      <div class="flex-1 overflow-y-auto p-6">
        <div v-if="!participant?.attendances || participant.attendances.length === 0" class="flex flex-col items-center justify-center py-12 text-center text-slate-500">
          <ScanLine class="h-12 w-12 opacity-20 mb-3" />
          <p class="text-sm">No attendance records found for this participant.</p>
        </div>

        <div v-else class="space-y-6">
          <div class="relative border-l-2 border-slate-100 dark:border-slate-800 ml-4 space-y-8 pb-4">
            
            <div v-for="(record, index) in sortedAttendances" :key="record.id" class="relative pl-6">
              <!-- Timeline Dot -->
              <div class="absolute -left-[9px] top-1 h-4 w-4 rounded-full border-2 border-white bg-[#d4af37] dark:border-slate-900 shadow-sm"></div>
              
              <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    {{ formatScanType(record.type || record.scan_type) }}
                  </span>
                  <span class="text-xs font-medium text-slate-500">
                    {{ formatDate(record.scanned_at) }}
                  </span>
                </div>
                
                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                  <Clock class="h-3 w-3" />
                  {{ formatTime(record.scanned_at) }}
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t border-slate-100 bg-slate-50 p-4 sm:px-6 dark:border-slate-800 dark:bg-slate-900/50">
        <button @click="$emit('close')" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold tracking-widest text-white uppercase transition-all hover:bg-slate-800 active:scale-[0.98] dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">
          Close
        </button>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { History, X, Clock, ScanLine } from '@lucide/vue';

const props = defineProps<{
  show: boolean;
  participant: any;
  event: any;
}>();

defineEmits(['close']);

const sortedAttendances = computed(() => {
  if (!props.participant?.attendances) return [];
  // Sort chronologically
  return [...props.participant.attendances].sort((a, b) => {
    return new Date(a.scanned_at).getTime() - new Date(b.scanned_at).getTime();
  });
});

const formatScanType = (type: string) => {
  if (!type) return 'Attendance Scan';
  const map: Record<string, string> = {
    'one-time': 'One-Time Scan',
    'am_in': 'Morning Time In',
    'am_out': 'Morning Time Out',
    'pm_in': 'Afternoon Time In',
    'pm_out': 'Afternoon Time Out',
  };
  return map[type] || type.replace('_', ' ');
};

const formatDate = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const formatTime = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
