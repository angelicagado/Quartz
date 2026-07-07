<template>
    <Head title="Participants" />
    <div
        class="mx-auto max-w-7xl animate-in space-y-8 font-sans duration-500 fade-in"
    >
        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
        >
            <div>
                <h1
                    class="font-serif text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100"
                >
                    Participants
                </h1>
                <p class="mt-1 text-slate-500 dark:text-slate-400">
                    Manage attendees and track registration status.
                </p>
            </div>
            <button
                v-if="role === 'admin' || role === 'super_admin'"
                class="flex items-center gap-2 rounded-xl bg-[#1E293B] px-6 py-3 font-medium text-white shadow-sm transition-all hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200"
            >
                <UserPlus class="h-5 w-5" />
                Add Participant
            </button>
        </div>

        <div class="flex flex-col items-center gap-4 md:flex-row">
            <div class="relative w-full flex-1">
                <Search
                    class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-slate-400"
                />
                <input
                    type="text"
                    placeholder="Search by name or email..."
                    v-model="searchQuery"
                    class="w-full rounded-2xl border border-slate-200 bg-white py-3 pr-4 pl-12 shadow-sm transition-all outline-none focus:border-[#d4af37] focus:ring-4 focus:ring-[#d4af37]/5 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-[#d4af37]"
                />
            </div>
            <button
                class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            >
                <Filter class="h-5 w-5" />
                Filters
            </button>
        </div>

        <div
            class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr
                            class="border-b border-slate-100 dark:border-slate-800"
                        >
                            <th
                                class="px-8 py-5 text-sm font-bold tracking-wider text-slate-900 uppercase dark:text-slate-300"
                            >
                                Participant
                            </th>
                            <th
                                class="px-8 py-5 text-sm font-bold tracking-wider text-slate-900 uppercase dark:text-slate-300"
                            >
                                Event
                            </th>
                            <th
                                class="px-8 py-5 text-sm font-bold tracking-wider text-slate-900 uppercase dark:text-slate-300"
                            >
                                Status
                            </th>
                            <th
                                class="px-8 py-5 text-right text-sm font-bold tracking-wider text-slate-900 uppercase dark:text-slate-300"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-50 dark:divide-slate-800/50"
                    >
                        <tr v-if="filteredParticipants.length === 0">
                            <td
                                colspan="4"
                                class="px-8 py-12 text-center font-light text-slate-400 italic dark:text-slate-500"
                            >
                                No participants found matching "{{
                                    searchQuery
                                }}".
                            </td>
                        </tr>
                        <tr
                            v-else
                            v-for="person in filteredParticipants"
                            :key="person.id"
                            class="group transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50"
                        >
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 transition-all group-hover:bg-[#d4af37]/10 group-hover:text-[#d4af37] dark:bg-slate-800 dark:text-slate-500"
                                    >
                                        <Users class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <div
                                            class="font-bold text-slate-900 dark:text-slate-100"
                                        >
                                            {{
                                                person.name ||
                                                person.user?.name ||
                                                'Unknown User'
                                            }}
                                        </div>
                                        <div
                                            class="text-sm font-light text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                person.email ||
                                                person.user?.email ||
                                                'No email'
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div
                                    class="text-sm font-medium text-slate-600 dark:text-slate-300"
                                >
                                    {{ person.event?.title || 'Unknown Event' }}
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span
                                    class="rounded-full border px-4 py-1.5 text-xs font-bold tracking-widest uppercase"
                                    :class="
                                        person.registration_status ===
                                        'confirmed'
                                            ? 'border-emerald-100 bg-emerald-50 text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400'
                                            : person.registration_status ===
                                                'pending'
                                              ? 'border-amber-100 bg-amber-50 text-amber-600 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-400'
                                              : 'border-rose-100 bg-rose-50 text-rose-600 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-400'
                                    "
                                >
                                    {{ person.registration_status }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button
                                    class="p-2 text-slate-300 transition-colors hover:text-slate-600 dark:text-slate-600 dark:hover:text-slate-300"
                                >
                                    <MoreHorizontal class="h-6 w-6" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, Head } from '@inertiajs/vue3';
import { Users, Search, Filter, MoreHorizontal, UserPlus } from '@lucide/vue';

interface Participant {
    id: number;
    name: string;
    email: string;
    user?: {
        name: string;
        email: string;
    };
    event?: {
        title: string;
    };
    registration_status: string;
}

const props = defineProps<{
    participants: Participant[];
}>();

const page = usePage();
const auth = computed(() => page.props.auth as any);
const role = computed(() => auth.value.user.role.name);

const searchQuery = ref('');

const filteredParticipants = computed(() => {
    const query = searchQuery.value.toLowerCase();
    if (!query) return props.participants;

    return props.participants.filter(
        (p) =>
            p.name?.toLowerCase().includes(query) ||
            p.user?.name?.toLowerCase().includes(query) ||
            p.email?.toLowerCase().includes(query) ||
            p.user?.email?.toLowerCase().includes(query) ||
            p.event?.title?.toLowerCase().includes(query),
    );
});
</script>
