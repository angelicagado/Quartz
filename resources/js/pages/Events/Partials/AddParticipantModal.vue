<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 animate-in fade-in duration-200">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative z-10 w-full max-w-lg transform overflow-hidden rounded-[2rem] bg-white shadow-2xl transition-all flex flex-col max-h-[90vh] dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
      
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-slate-800">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#d4af37]/10 text-[#d4af37]">
            <UserPlus class="h-5 w-5" />
          </div>
          <div>
            <h3 class="font-serif text-lg font-bold text-slate-900 dark:text-white">Add Participants</h3>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
              {{ event?.title }}
            </p>
          </div>
        </div>
        <button @click="$emit('close')" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors dark:hover:bg-slate-800 dark:hover:text-slate-300">
          <X class="h-5 w-5" />
        </button>
      </div>

      <!-- Mode Selector / Tabs -->
      <div class="flex border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 p-1.5 mx-6 mt-6 rounded-2xl gap-1">
        <button
          type="button"
          @click="activeTab = 'manual'"
          class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-bold uppercase tracking-wider transition-all"
          :class="activeTab === 'manual' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'"
        >
          <User class="h-4 w-4" />
          Manual Add
        </button>
        <button
          type="button"
          @click="activeTab = 'import'"
          class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-bold uppercase tracking-wider transition-all"
          :class="activeTab === 'import' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'"
        >
          <FileSpreadsheet class="h-4 w-4" />
          Excel / CSV Import
        </button>
      </div>

      <!-- Body -->
      <div class="flex-1 overflow-y-auto p-6">
        <!-- Manual Add Tab -->
        <form v-if="activeTab === 'manual'" @submit.prevent="submitManual" class="space-y-5">
          <div class="space-y-2">
            <label class="text-xs font-bold tracking-widest text-slate-500 uppercase">
              Full Name
            </label>
            <input
              v-model="manualForm.name"
              type="text"
              placeholder="e.g. Jane Doe"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#d4af37] focus:outline-none focus:ring-2 focus:ring-[#d4af37]/20 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
              required
            />
            <p v-if="manualForm.errors.name" class="text-xs text-red-500 font-medium">
              {{ manualForm.errors.name }}
            </p>
          </div>

          <div class="space-y-2">
            <label class="text-xs font-bold tracking-widest text-slate-500 uppercase">
              Email Address
            </label>
            <input
              v-model="manualForm.email"
              type="email"
              placeholder="e.g. jane@example.com"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#d4af37] focus:outline-none focus:ring-2 focus:ring-[#d4af37]/20 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
              required
            />
            <p v-if="manualForm.errors.email" class="text-xs text-red-500 font-medium">
              {{ manualForm.errors.email }}
            </p>
          </div>

          <div class="pt-4">
            <button
              type="submit"
              :disabled="manualForm.processing"
              class="w-full flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3.5 text-sm font-bold tracking-widest text-white uppercase transition-all hover:bg-slate-800 active:scale-[0.98] disabled:opacity-50 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white"
            >
              <UserPlus class="h-4 w-4" />
              {{ manualForm.processing ? 'Adding...' : 'Add Participant' }}
            </button>
          </div>
        </form>

        <!-- Import Excel / CSV Tab -->
        <form v-else @submit.prevent="submitImport" class="space-y-5">
          <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center dark:border-slate-800 dark:bg-slate-800/30">
            <input
              ref="fileInput"
              type="file"
              accept=".csv,.txt,.xlsx,.xls"
              @change="handleFileChange"
              class="hidden"
            />
            
            <div v-if="!selectedFile" class="flex flex-col items-center justify-center py-4 cursor-pointer" @click="triggerFileInput">
              <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm dark:bg-slate-800">
                <Upload class="h-6 w-6 text-[#d4af37]" />
              </div>
              <p class="text-sm font-bold text-slate-900 dark:text-white">
                Click to select Excel or CSV file
              </p>
              <p class="mt-1 text-xs text-slate-500">
                Supports .xlsx, .xls, .csv (Max 5MB)
              </p>
            </div>

            <div v-else class="flex items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
              <div class="flex items-center gap-3 overflow-hidden">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                  <FileSpreadsheet class="h-5 w-5" />
                </div>
                <div class="text-left truncate">
                  <p class="text-sm font-bold text-slate-900 dark:text-white truncate">
                    {{ selectedFile.name }}
                  </p>
                  <p class="text-xs text-slate-500">
                    {{ (selectedFile.size / 1024).toFixed(1) }} KB
                  </p>
                </div>
              </div>
              <button
                type="button"
                @click="removeFile"
                class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors dark:hover:bg-red-500/10 dark:hover:text-red-400"
              >
                <X class="h-4 w-4" />
              </button>
            </div>
          </div>

          <p v-if="importForm.errors.file" class="text-xs text-red-500 font-medium text-center">
            {{ importForm.errors.file }}
          </p>
          <p v-if="importForm.errors.csv_file" class="text-xs text-red-500 font-medium text-center">
            {{ importForm.errors.csv_file }}
          </p>

          <div class="rounded-xl bg-amber-50/80 border border-amber-200/60 p-4 dark:bg-amber-500/10 dark:border-amber-500/20">
            <div class="flex gap-2.5">
              <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
              <div class="text-xs text-amber-800 dark:text-amber-300 space-y-1">
                <p class="font-bold">File Format Guidelines:</p>
                <ul class="list-disc pl-4 space-y-0.5 opacity-90">
                  <li>First row should contain column headers: <strong>name</strong> and <strong>email</strong>.</li>
                  <li>Existing registered participants will be automatically skipped to prevent duplicates.</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="pt-2">
            <button
              type="submit"
              :disabled="!selectedFile || importForm.processing"
              class="w-full flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3.5 text-sm font-bold tracking-widest text-white uppercase transition-all hover:bg-slate-800 active:scale-[0.98] disabled:opacity-50 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white"
            >
              <Upload class="h-4 w-4" />
              {{ importForm.processing ? 'Importing...' : 'Import Participants' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Footer -->
      <div class="border-t border-slate-100 bg-slate-50 p-4 sm:px-6 dark:border-slate-800 dark:bg-slate-900/50 flex justify-end">
        <button
          type="button"
          @click="$emit('close')"
          class="rounded-xl px-4 py-2.5 text-xs font-bold tracking-widest text-slate-500 uppercase transition-colors hover:bg-slate-200/50 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-200"
        >
          Cancel
        </button>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { UserPlus, User, FileSpreadsheet, X, Upload, AlertCircle } from '@lucide/vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
  show: boolean;
  event: any;
}>();

const emit = defineEmits(['close']);

const activeTab = ref<'manual' | 'import'>('manual');
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);

const manualForm = useForm({
  name: '',
  email: '',
});

const importForm = useForm({
  file: null as File | null,
});

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    selectedFile.value = target.files[0];
    importForm.file = target.files[0];
  }
};

const removeFile = () => {
  selectedFile.value = null;
  importForm.file = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const submitManual = () => {
  if (!props.event?.id) return;
  manualForm.post(`/events/${props.event.id}/participants`, {
    preserveScroll: true,
    onSuccess: () => {
      manualForm.reset();
      emit('close');
      toast.success('Participant added successfully.');
    },
    onError: () => {
      toast.error('Failed to add participant. Please check the form.');
    }
  });
};

const submitImport = () => {
  if (!props.event?.id || !importForm.file) return;
  importForm.post(`/events/${props.event.id}/participants/import`, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      removeFile();
      emit('close');
      toast.success('Participants imported successfully.');
    },
    onError: () => {
      toast.error('Failed to import file. Please verify the file format.');
    }
  });
};
</script>
