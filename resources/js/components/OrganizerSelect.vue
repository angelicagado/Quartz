<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { Check, ChevronDown, Search, X } from "@lucide/vue";

interface Organizer {
  id: number;
  name: string;
  email: string;
}

const props = defineProps<{
  modelValue: number[];
  organizers: Organizer[];
  error?: string;
}>();

const emit = defineEmits<{
  (e: "update:modelValue", value: number[]): void;
}>();

const open = ref(false);
const query = ref("");
const dropdownRef = ref<HTMLElement | null>(null);

const filteredOrganizers = computed(() => {
  if (!query.value) return props.organizers;
  const q = query.value.toLowerCase();
  return props.organizers.filter(
    (o) => o.name.toLowerCase().includes(q) || o.email.toLowerCase().includes(q)
  );
});

const selectedOrganizers = computed(() => {
  return props.organizers.filter((o) => props.modelValue.includes(o.id));
});

function toggleDropdown() {
  open.value = !open.value;
  if (open.value) {
    query.value = "";
  }
}

function selectOrganizer(id: number) {
  const newValue = [...props.modelValue];
  if (newValue.includes(id)) {
    const index = newValue.indexOf(id);
    newValue.splice(index, 1);
  } else {
    newValue.push(id);
  }
  emit("update:modelValue", newValue);
  query.value = ""; // reset search after select
}

function removeOrganizer(id: number) {
  const newValue = props.modelValue.filter((val) => val !== id);
  emit("update:modelValue", newValue);
}

// Click outside to close
function handleClickOutside(event: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    open.value = false;
  }
}

onMounted(() => {
  document.addEventListener("mousedown", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("mousedown", handleClickOutside);
});
</script>

<template>
  <div class="relative w-full" ref="dropdownRef">
    <!-- Selected Chips & Trigger -->
    <div
      class="min-h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors focus-within:border-ring focus-within:ring-2 focus-within:ring-ring/50"
      :class="{ 'border-destructive ring-destructive/20': error }"
    >
      <div class="flex flex-wrap items-center gap-2">
        <div
          v-for="organizer in selectedOrganizers"
          :key="organizer.id"
          class="flex items-center gap-1 rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-medium text-violet-800 dark:bg-violet-900/30 dark:text-violet-300"
        >
          <span>{{ organizer.name }}</span>
          <button
            type="button"
            @click.prevent="removeOrganizer(organizer.id)"
            class="ml-1 inline-flex size-4 items-center justify-center rounded-full hover:bg-violet-200 dark:hover:bg-violet-800"
          >
            <X class="size-3" />
            <span class="sr-only">Remove {{ organizer.name }}</span>
          </button>
        </div>

        <!-- Search Input embedded in the trigger area -->
        <div class="flex-1 min-w-[120px]">
            <input
              type="text"
              v-model="query"
              @focus="open = true"
              placeholder="Search or add organizers..."
              class="w-full bg-transparent border-0 p-0 text-sm focus:ring-0 focus:outline-none placeholder:text-muted-foreground"
            />
        </div>

        <button type="button" @click="toggleDropdown" class="ml-auto text-muted-foreground hover:text-foreground">
            <ChevronDown class="size-4" />
        </button>
      </div>
    </div>

    <!-- Dropdown Menu -->
    <div
      v-if="open"
      class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-border bg-popover text-popover-foreground shadow-md p-1"
    >
      <div v-if="filteredOrganizers.length === 0" class="py-6 text-center text-sm text-muted-foreground">
        No organizers found.
      </div>
      
      <button
        v-for="organizer in filteredOrganizers"
        :key="organizer.id"
        type="button"
        @click="selectOrganizer(organizer.id)"
        class="relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground"
      >
        <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
          <Check
            v-if="modelValue.includes(organizer.id)"
            class="h-4 w-4 text-primary"
          />
        </span>
        <div class="flex flex-col items-start">
            <span>{{ organizer.name }}</span>
            <span class="text-xs text-muted-foreground">{{ organizer.email }}</span>
        </div>
      </button>
    </div>
    
    <p v-if="error" class="mt-1.5 text-xs text-destructive">
        {{ error }}
    </p>
  </div>
</template>
