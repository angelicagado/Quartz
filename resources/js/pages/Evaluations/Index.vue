<template>
    <Head title="Evaluations" />
    <div
        class="mx-auto max-w-7xl animate-in space-y-8 pb-10 font-sans duration-500 fade-in"
    >
        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
        >
            <div>
                <h1
                    class="font-serif text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100"
                >
                    Evaluations
                </h1>
                <p class="mt-1 text-slate-500 dark:text-slate-400">
                    Review feedback and satisfaction ratings from participants.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div
                class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mb-4 flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#d4af37]/10 text-[#d4af37]"
                    >
                        <Star class="h-6 w-6 fill-current" />
                    </div>
                    <h3
                        class="font-serif font-bold text-slate-900 dark:text-slate-100"
                    >
                        Average Rating
                    </h3>
                </div>
                <div
                    class="text-4xl font-black text-slate-900 dark:text-slate-100"
                >
                    4.8
                    <span class="text-lg font-normal text-slate-400">/5.0</span>
                </div>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-[2.5rem] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <table class="w-full text-left">
                <thead>
                    <tr
                        class="border-b border-slate-100 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-800/50"
                    >
                        <th
                            class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
                        >
                            Participant
                        </th>
                        <th
                            class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
                        >
                            Event
                        </th>
                        <th
                            class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
                        >
                            Rating
                        </th>
                        <th
                            class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
                        >
                            Feedback
                        </th>
                        <th
                            class="px-8 py-5 text-right text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
                        >
                            Date
                        </th>
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-slate-50 dark:divide-slate-800/50"
                >
                    <tr v-if="evaluations.length === 0">
                        <td
                            colspan="5"
                            class="px-8 py-12 text-center font-light text-slate-400 italic dark:text-slate-500"
                        >
                            No evaluations submitted yet.
                        </td>
                    </tr>
                    <tr
                        v-else
                        v-for="ev in evaluations"
                        :key="ev.id"
                        class="group transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50"
                    >
                        <td class="px-8 py-6">
                            <div
                                class="font-bold text-slate-900 dark:text-slate-100"
                            >
                                {{ ev.participant?.user?.name || 'Anonymous' }}
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div
                                class="text-sm font-medium text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    ev.participant?.event?.title ||
                                    'Unknown Event'
                                }}
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-1">
                                <Star
                                    v-for="i in 5"
                                    :key="i"
                                    class="h-3.5 w-3.5"
                                    :class="
                                        i <= ev.rating
                                            ? 'fill-current text-[#d4af37]'
                                            : 'text-slate-200 dark:text-slate-700'
                                    "
                                />
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div
                                class="max-w-xs truncate text-sm text-slate-500 dark:text-slate-400"
                                :title="ev.comments"
                            >
                                {{ ev.comments }}
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div
                                class="text-xs text-slate-400 dark:text-slate-500"
                            >
                                {{
                                    new Date(
                                        ev.submitted_at,
                                    ).toLocaleDateString()
                                }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Star } from '@lucide/vue';
import { Head } from '@inertiajs/vue3';

interface Evaluation {
    id: number;
    rating: number;
    comments: string;
    submitted_at: string;
    participant?: {
        user?: {
            name: string;
        };
        event?: {
            title: string;
        };
    };
}

defineProps<{
    evaluations: Evaluation[];
}>();
</script>
