<template>
  <Head title="System Users" />
  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
      <div>
        <h1
          class="font-serif text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100"
        >
          System Users
        </h1>
        <p class="mt-1 text-slate-500 dark:text-slate-400">
          Manage all access levels, assign roles, and handle accounts.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <div class="relative">
          <Search
            class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-slate-400"
          />
          <input
            type="text"
            placeholder="Search users by name, email, or role..."
            v-model="searchQuery"
            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pr-4 pl-10 text-slate-600 shadow-sm transition-all outline-none placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 sm:w-80 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:placeholder-slate-500"
          />
        </div>
        <button
          @click="openCreateModal"
          class="hidden items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 font-medium text-white shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-lg active:translate-y-0 md:flex"
        >
          <Plus class="h-5 w-5" />
          Create User
        </button>
      </div>
    </div>

    <!-- Users Table -->
    <div
      class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left">
          <thead>
            <tr
              class="border-b border-slate-200 bg-slate-50 text-sm font-semibold tracking-wide text-slate-500 uppercase dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400"
            >
              <th class="px-6 py-4">User Details</th>
              <th class="px-6 py-4">System Role</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4">Joined Date</th>
              <th class="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr
              v-for="user in filteredUsers"
              :key="user.id"
              class="group transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-100 font-bold text-slate-500 uppercase dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400"
                  >
                    {{ user.name.charAt(0) }}
                  </div>
                  <div>
                    <div class="font-semibold text-slate-900 dark:text-slate-100">
                      {{ user.name }}
                    </div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                      {{ user.email }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-1.5">
                  <Shield
                    class="h-4 w-4"
                    :class="
                      user.role.name === 'super_admin'
                        ? 'text-rose-500'
                        : user.role.name === 'admin'
                        ? 'text-[#d4af37]'
                        : user.role.name === 'organizer'
                        ? 'text-indigo-500'
                        : 'text-slate-400'
                    "
                  />
                  <span class="font-medium text-slate-700 capitalize dark:text-slate-300">
                    {{ user.role.name.replace("_", " ") }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span
                  v-if="user.status === 'active'"
                  class="inline-flex items-center gap-1.5 rounded-md border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                  Active
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                  Inactive
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                {{
                  new Date(user.created_at).toLocaleDateString("en-US", {
                    month: "short",
                    day: "numeric",
                    year: "numeric",
                  })
                }}
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="openEditModal(user)"
                    title="Edit User"
                    class="rounded-lg p-2 text-indigo-400 transition-all hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400"
                  >
                    <Settings class="h-5 w-5" />
                  </button>
                  <button
                    @click="toggleStatus(user)"
                    :title="user.status === 'active' ? 'Deactivate' : 'Activate'"
                    class="rounded-lg p-2 transition-all"
                    :class="
                      user.status === 'active'
                        ? 'text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10'
                        : 'text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10'
                    "
                  >
                    <UserX v-if="user.status === 'active'" class="h-5 w-5" />
                    <UserCheck v-else class="h-5 w-5" />
                  </button>
                  <button
                    @click="handleDelete(user.id)"
                    title="Delete User"
                    class="rounded-lg p-2 text-rose-400 transition-all hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400"
                  >
                    <Trash2 class="h-5 w-5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredUsers.length === 0">
              <td
                colspan="5"
                class="px-6 py-12 text-center text-slate-500 dark:text-slate-400"
              >
                No users found matching your search.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit User Modal -->
    <div
      v-if="isCreateModalOpen || isEditModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4 pb-20 sm:p-6"
    >
      <div
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
        @click="closeModal"
      ></div>
      <div
        class="relative my-auto flex w-full max-w-lg animate-in flex-col rounded-2xl border border-slate-100 bg-white shadow-2xl duration-200 zoom-in-95 fade-in dark:border-slate-800 dark:bg-slate-900"
      >
        <!-- Modal Header -->
        <div
          class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800"
        >
          <h3 class="font-serif text-xl font-bold text-slate-900 dark:text-slate-100">
            {{ editingUser ? "Edit User details" : "Add New User" }}
          </h3>
          <button
            @click="closeModal"
            class="rounded-full bg-slate-50 p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:bg-slate-800 dark:hover:bg-slate-700 dark:hover:text-slate-300"
          >
            <X class="h-5 w-5" />
          </button>
        </div>

        <!-- Modal Body / Form -->
        <div class="max-h-[75vh] overflow-y-auto p-6">
          <form @submit.prevent="submit" class="space-y-5">
            <!-- Name -->
            <div>
              <label
                class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300"
              >
                Full Name *
              </label>
              <input
                type="text"
                v-model="form.name"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 transition-all outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-emerald-500"
                placeholder="e.g. John Doe"
                required
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-rose-500">
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Email -->
            <div>
              <label
                class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300"
              >
                Email Address *
              </label>
              <input
                type="email"
                v-model="form.email"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 transition-all outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-emerald-500"
                placeholder="user@quartz.com"
                required
              />
              <p v-if="form.errors.email" class="mt-1 text-sm text-rose-500">
                {{ form.errors.email }}
              </p>
            </div>

            <!-- Password -->
            <div>
              <label
                class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300"
              >
                {{ editingUser ? "New Password (Optional)" : "Temporary Password *" }}
              </label>
              <input
                type="password"
                v-model="form.password"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 transition-all outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-emerald-500"
                placeholder="••••••••"
                :required="!editingUser"
                minlength="8"
              />
              <p v-if="form.errors.password" class="mt-1 text-sm text-rose-500">
                {{ form.errors.password }}
              </p>
            </div>

            <!-- Role and Status Container -->
            <div class="grid grid-cols-2 gap-4 pt-1">
              <div>
                <label
                  class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                >
                  System Role *
                </label>
                <select
                  v-model="form.role_id"
                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 capitalize transition-all outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-emerald-500"
                  required
                >
                  <option value="" disabled>Select Role...</option>
                  <option v-for="role in roles" :key="role.id" :value="role.id">
                    {{ role.name.replace("_", " ") }}
                  </option>
                </select>
                <p v-if="form.errors.role_id" class="mt-1 text-sm text-rose-500">
                  {{ form.errors.role_id }}
                </p>
              </div>
              <div>
                <label
                  class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                >
                  Account Status *
                </label>
                <select
                  v-model="form.status"
                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 transition-all outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-emerald-500"
                >
                  <option value="active">Active (Access Granted)</option>
                  <option value="inactive">Inactive (Suspended)</option>
                </select>
              </div>
            </div>

            <!-- Actions -->
            <div
              class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-6 pb-2 dark:border-slate-800"
            >
              <button
                type="button"
                @click="closeModal"
                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 font-medium text-slate-600 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="flex items-center gap-2 rounded-xl border border-emerald-700 bg-emerald-600 px-6 py-2.5 font-medium text-white shadow-md transition-all hover:bg-emerald-700"
              >
                {{
                  form.processing
                    ? "Saving..."
                    : editingUser
                    ? "Update Record"
                    : "Create Record"
                }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { useForm, router, Head } from "@inertiajs/vue3";
import {
  Plus,
  Search,
  UserCheck,
  UserX,
  Shield,
  MoreVertical,
  X,
  Trash2,
  Settings,
} from "@lucide/vue";

interface Role {
  id: number;
  name: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  status: "active" | "inactive";
  created_at: string;
  role: Role;
}

const props = defineProps<{
  users: User[];
  roles: Role[];
}>();

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const editingUser = ref<User | null>(null);
const searchQuery = ref("");

const form = useForm({
  name: "",
  email: "",
  password: "",
  role_id: "",
  status: "active",
});

const openCreateModal = () => {
  editingUser.value = null;
  form.reset();
  isCreateModalOpen.value = true;
};

const openEditModal = (user: User) => {
  editingUser.value = user;
  form.name = user.name;
  form.email = user.email;
  form.password = ""; // Leave empty unless changing
  form.role_id = user.role.id.toString();
  form.status = user.status;
  isEditModalOpen.value = true;
};

const closeModal = () => {
  isCreateModalOpen.value = false;
  isEditModalOpen.value = false;
};

const submit = () => {
  if (editingUser.value) {
    form.patch(`/users/${editingUser.value.id}`, {
      onSuccess: () => {
        closeModal();
        form.reset();
      },
    });
  } else {
    form.post("/users", {
      onSuccess: () => {
        closeModal();
        form.reset();
      },
    });
  }
};

const handleDelete = (id: number) => {
  if (confirm("Are you sure you want to remove this user from the system?")) {
    router.delete(`/users/${id}`);
  }
};

const toggleStatus = (user: User) => {
  const newStatus = user.status === "active" ? "inactive" : "active";
  router.patch(`/users/${user.id}`, {
    status: newStatus,
  });
};

const filteredUsers = computed(() => {
  const query = searchQuery.value.toLowerCase();
  if (!query) return props.users;
  return props.users.filter(
    (user) =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query) ||
      user.role.name.toLowerCase().includes(query)
  );
});
</script>
