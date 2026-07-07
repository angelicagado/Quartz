<template>
  <div
    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
  >
    <h3 class="font-serif text-lg font-bold text-slate-900 dark:text-slate-100">
      Certificate Builder
    </h3>

    <div class="mt-2 text-sm text-slate-500">
      <p v-if="previewUrl">
        A certificate template is currently configured. Participants can download their
        certificates after the event.
      </p>
      <p v-else>No certificate template configured yet.</p>
    </div>

    <div
      class="mt-6 flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800"
    >
      <template v-if="previewUrl">
        <Button variant="outline" size="sm" @click="viewTemplate"> View Template </Button>
        <Button variant="default" size="sm" @click="isModalOpen = true">
          Edit Template
        </Button>
        <Button variant="destructive" size="sm" @click="deleteTemplate">
          Delete Template
        </Button>
      </template>
      <template v-else>
        <Button variant="default" size="sm" @click="isModalOpen = true">
          Create Template
        </Button>
      </template>
    </div>

    <Dialog v-model:open="isModalOpen">
      <DialogContent class="sm:max-w-fit max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Certificate Template Editor</DialogTitle>
          <DialogDescription>
            Upload a background image and drag placeholders to configure the certificate.
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="submit" class="mt-4 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300"
                >Template Name</label
              >
              <input
                v-model="form.name"
                type="text"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                required
              />
              <InputError :message="form.errors.name" class="mt-2" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300"
                >Background Image (PNG)</label
              >
              <input
                type="file"
                @change="onFileChange"
                accept="image/png"
                class="mt-1 block w-full text-slate-700 dark:text-slate-300"
              />
              <InputError :message="form.errors.background_image" class="mt-2" />
            </div>
          </div>

          <InputError :message="form.errors.dynamic_fields_mapping" class="mt-2" />

          <div
            v-if="previewUrl"
            class="relative mt-4 border border-slate-200 dark:border-slate-700 bg-slate-50 flex flex-col items-center p-4 rounded-xl"
          >
            <div class="w-full flex items-center justify-between mb-4">
              <p class="text-xs text-slate-500">
                Click on the canvas to place the selected field, then drag to reposition.
              </p>
              <select
                v-model="selectedField"
                class="rounded border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
              >
                <option value="participant_name">Participant Name</option>
                <option value="event_title">Event Title</option>
                <option value="date">Event Date</option>
              </select>
            </div>

            <div
              id="certificate-canvas"
              class="relative inline-block overflow-hidden border border-slate-300 select-none bg-white shadow-sm"
              :class="draggingField ? 'cursor-grabbing' : 'cursor-crosshair'"
              @mousedown="setImageCoordinates"
            >
              <img
                :src="previewUrl"
                alt="Certificate Preview"
                style="max-width: 800px; display: block"
              />

              <div
                v-for="(field, key) in form.dynamic_fields_mapping"
                :key="key"
                @mousedown.stop="startDrag(key, $event)"
                class="absolute flex flex-col items-center justify-center border-2 border-dashed border-blue-500 bg-white/70 px-2 py-1 text-blue-900 shadow-sm dark:bg-slate-800/80 dark:text-blue-300"
                style="transform: translate(-50%, -50%)"
                :class="draggingField === key ? 'cursor-grabbing' : 'cursor-grab'"
                :style="{
                  top: field.y + '%',
                  left: field.x + '%',
                  fontSize: (field.size || 24) + 'px',
                  color: field.color || '#000000',
                }"
              >
                <span>{{ key.replace("_", " ").toUpperCase() }}</span>
                <div class="mt-1 flex gap-1">
                  <input
                    type="number"
                    v-model="field.size"
                    class="w-16 rounded border border-slate-300 p-1 text-xs text-black"
                    @click.stop
                    title="Font Size"
                  />
                  <input
                    type="color"
                    v-model="field.color"
                    class="h-6 w-6 cursor-pointer border-0 p-0"
                    @click.stop
                    title="Text Color"
                  />
                  <button
                    type="button"
                    class="ml-1 text-red-500 hover:text-red-700 font-bold"
                    @click.stop="removeField(key)"
                    title="Remove Field"
                  >
                    ×
                  </button>
                </div>
              </div>
            </div>
          </div>

          <DialogFooter class="pt-4 mt-6">
            <Button type="button" variant="outline" @click="isModalOpen = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="form.processing"> Save Changes </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Preview Modal -->
    <Dialog v-model:open="isPreviewModalOpen">
      <DialogContent class="sm:max-w-fit max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Template Preview</DialogTitle>
          <DialogDescription>
            This is how the certificate will look for participants (showing sample data).
          </DialogDescription>
        </DialogHeader>

        <div v-if="previewUrl" class="relative mt-4 flex justify-center">
          <div
            class="relative inline-block overflow-hidden border border-slate-300 select-none bg-white shadow-sm pointer-events-none"
          >
            <img
              :src="previewUrl"
              alt="Certificate Preview"
              style="max-width: 800px; display: block"
            />

            <div
              v-for="(field, key) in form.dynamic_fields_mapping"
              :key="key"
              class="absolute flex flex-col items-center justify-center font-bold"
              style="transform: translate(-50%, -50%)"
              :style="{
                top: field.y + '%',
                left: field.x + '%',
                fontSize: (field.size || 24) + 'px',
                color: field.color || '#000000',
              }"
            >
              <span>{{
                key === "participant_name"
                  ? "JOHN DOE"
                  : key === "event_title"
                  ? event.title
                  : "JANUARY 1, 2026"
              }}</span>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import InputError from "@/Components/InputError.vue";
import { toast } from "vue-sonner";

const props = defineProps({
  event: Object,
  existingTemplate: Object,
});

const isModalOpen = ref(false);
const isPreviewModalOpen = ref(false);

const form = useForm({
  name: props.existingTemplate?.name || "",
  background_image: null,
  dynamic_fields_mapping: props.existingTemplate?.dynamic_fields_mapping || {},
});

const previewUrl = ref(
  props.existingTemplate?.background_path
    ? `/storage/${props.existingTemplate.background_path.replace("public/", "")}`
    : null
);
const selectedField = ref("participant_name");

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.background_image = file;
    previewUrl.value = URL.createObjectURL(file);

    if (Object.keys(form.dynamic_fields_mapping).length === 0) {
      form.dynamic_fields_mapping = {
        participant_name: {
          x: 50,
          y: 50,
          size: 48,
          color: "#000000",
        },
      };
    }
  }
};

const setImageCoordinates = (e) => {
  if (e.target.tagName !== "IMG") return;

  const rect = e.target.getBoundingClientRect();
  const x = ((e.clientX - rect.left) / rect.width) * 100;
  const y = ((e.clientY - rect.top) / rect.height) * 100;

  form.dynamic_fields_mapping = {
    ...form.dynamic_fields_mapping,
    [selectedField.value]: {
      x: Number(x.toFixed(2)),
      y: Number(y.toFixed(2)),
      size: form.dynamic_fields_mapping[selectedField.value]?.size || 24,
      color: form.dynamic_fields_mapping[selectedField.value]?.color || "#000000",
    },
  };

  startDrag(selectedField.value, e);
};

const removeField = (key) => {
  const newMapping = { ...form.dynamic_fields_mapping };
  delete newMapping[key];
  form.dynamic_fields_mapping = newMapping;
};

const draggingField = ref(null);

const startDrag = (key, e) => {
  draggingField.value = key;
  selectedField.value = key;
};

const onDrag = (e) => {
  if (!draggingField.value) return;

  const container = document.getElementById("certificate-canvas");
  if (!container) return;

  const rect = container.getBoundingClientRect();
  let x = ((e.clientX - rect.left) / rect.width) * 100;
  let y = ((e.clientY - rect.top) / rect.height) * 100;

  x = Math.max(0, Math.min(100, x));
  y = Math.max(0, Math.min(100, y));

  form.dynamic_fields_mapping[draggingField.value].x = Number(x.toFixed(2));
  form.dynamic_fields_mapping[draggingField.value].y = Number(y.toFixed(2));
};

const stopDrag = () => {
  draggingField.value = null;
};

onMounted(() => {
  window.addEventListener("mousemove", onDrag);
  window.addEventListener("mouseup", stopDrag);
});

onUnmounted(() => {
  window.removeEventListener("mousemove", onDrag);
  window.removeEventListener("mouseup", stopDrag);
});

const submit = () => {
  form.post(`/events/${props.event.id}/certificates`, {
    preserveScroll: true,
    onSuccess: () => {
      isModalOpen.value = false;
      toast.success("Certificate template saved successfully!");
    },
    onError: () => {
      toast.error("Failed to save certificate template. Please check the form errors.");
    },
  });
};

const viewTemplate = () => {
  if (previewUrl.value) {
    isPreviewModalOpen.value = true;
  }
};

const deleteTemplate = () => {
  if (!confirm("Are you sure you want to delete this template?")) return;

  router.delete(`/events/${props.event.id}/certificates`, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success("Certificate template deleted successfully!");
      form.reset();
      form.name = "";
      form.background_image = null;
      form.dynamic_fields_mapping = {};
      previewUrl.value = null;
      isModalOpen.value = false;
    },
    onError: () => {
      toast.error("Failed to delete certificate template.");
    },
  });
};
</script>
