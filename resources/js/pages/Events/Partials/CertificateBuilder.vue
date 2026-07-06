<template>
  <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Certificate Builder</h3>
    <form @submit.prevent="submit" class="mt-4 space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Template Name</label>
        <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" required />
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Background Image (PNG)</label>
        <input type="file" @change="onFileChange" accept="image/png" class="mt-1 block w-full text-slate-700 dark:text-slate-300" />
      </div>

      <div v-if="previewUrl" class="relative mt-4 border border-slate-200 dark:border-slate-700" style="overflow: auto;">
        <p class="text-xs text-slate-500 mb-2 p-2">Click on the image to set the position for the selected field.</p>
        <div class="mb-4 flex gap-4 px-2">
            <select v-model="selectedField" class="rounded border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                <option value="participant_name">Participant Name</option>
                <option value="event_title">Event Title</option>
                <option value="date">Event Date</option>
            </select>
        </div>
        
        <div class="relative inline-block cursor-crosshair overflow-hidden border border-slate-300" @click="setImageCoordinates">
            <img :src="previewUrl" alt="Certificate Preview" style="max-width: 800px; display: block;" />
            
            <div v-for="(field, key) in form.dynamic_fields_mapping" :key="key" 
                 class="absolute flex items-center justify-center border border-dashed border-red-500 text-red-900 dark:text-red-300"
                 style="transform: translate(0, -100%);"
                 :style="{ top: field.y + 'px', left: field.x + 'px', fontSize: (field.size || 24) + 'px' }">
                 {{ key }}
                 <input type="number" v-model="field.size" class="ml-2 w-16 p-0 text-xs text-black" @click.stop title="Font Size" />
            </div>
        </div>
      </div>

      <div class="flex justify-end pt-4">
        <button type="submit" class="rounded-xl bg-slate-900 px-6 py-2.5 font-bold text-white shadow hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900" :disabled="form.processing">
          Save Template
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  event: Object,
  existingTemplate: Object
});

const form = useForm({
  name: props.existingTemplate?.name || '',
  background_image: null,
  dynamic_fields_mapping: props.existingTemplate?.dynamic_fields_mapping || {}
});

const previewUrl = ref(props.existingTemplate?.background_path ? `/storage/${props.existingTemplate.background_path.replace('public/', '')}` : null);
const selectedField = ref('participant_name');

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.background_image = file;
    previewUrl.value = URL.createObjectURL(file);
  }
};

const setImageCoordinates = (e) => {
  if (e.target.tagName !== 'IMG') return;
  
  const rect = e.target.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;

  form.dynamic_fields_mapping = {
      ...form.dynamic_fields_mapping,
      [selectedField.value]: {
          x: Math.round(x),
          y: Math.round(y),
          size: 24,
          color: '#000000'
      }
  };
};

const submit = () => {
  form.post(`/events/${props.event.id}/certificates`, {
    preserveScroll: true,
  });
};
</script>
