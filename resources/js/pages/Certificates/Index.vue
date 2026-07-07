<template>
    <Head title="Certificates" />
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
                    Certificates
                </h1>
                <p class="mt-1 text-slate-500 dark:text-slate-400">
                    Generate and distribute rule-based certificates.
                </p>
            </div>
            <button
                class="flex items-center gap-2 rounded-xl bg-[#d4af37] px-6 py-3 font-bold text-white shadow-lg transition-all hover:bg-[#b38f4d] hover:shadow-[#d4af37]/30 active:scale-95 dark:hover:bg-[#a68241]"
            >
                <Award class="h-5 w-5" />
                Generate Batch
            </button>
        </div>

        <div class="flex flex-col items-center gap-4 md:flex-row">
            <div class="relative w-full flex-1">
                <Search
                    class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-slate-400"
                />
                <input
                    type="text"
                    placeholder="Search by name, event or cert #..."
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
                            Cert Number
                        </th>
                        <th
                            class="px-8 py-5 text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
                        >
                            Issue Date
                        </th>
                        <th
                            class="px-8 py-5 text-right text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-300"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-slate-50 dark:divide-slate-800/50"
                >
                    <tr v-if="certificates.length === 0">
                        <td
                            colspan="5"
                            class="px-8 py-12 text-center font-light text-slate-400 italic dark:text-slate-500"
                        >
                            No certificates found in the vault.
                        </td>
                    </tr>
                    <tr
                        v-else
                        v-for="cert in certificates"
                        :key="cert.id"
                        class="group transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50"
                    >
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#d4af37]/10 text-[#d4af37]"
                                >
                                    <Award class="h-5 w-5" />
                                </div>
                                <div>
                                    <div
                                        class="font-bold text-slate-900 dark:text-slate-100"
                                    >
                                        {{
                                            cert.participant?.user?.name ||
                                            'Unknown'
                                        }}
                                    </div>
                                    <div
                                        class="text-xs font-light text-slate-500 dark:text-slate-400"
                                    >
                                        {{
                                            cert.participant?.user?.email ||
                                            'N/A'
                                        }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div
                                class="text-sm font-medium text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    cert.participant?.event?.title ||
                                    'Unknown Event'
                                }}
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <code
                                class="rounded bg-slate-100 px-2 py-1 text-[10px] font-bold tracking-tighter text-slate-600 uppercase dark:bg-slate-800 dark:text-slate-300"
                            >
                                {{ cert.certificate_number }}
                            </code>
                        </td>
                        <td class="px-8 py-6">
                            <div
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    new Date(
                                        cert.issued_at,
                                    ).toLocaleDateString()
                                }}
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    title="Download PDF"
                                    class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                                >
                                    <Download class="h-5 w-5" />
                                </button>
                                <button
                                    title="Send Email"
                                    class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-[#d4af37]/10 hover:text-[#d4af37]"
                                >
                                    <Send class="h-5 w-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Award, Download, Search, Filter, Send } from '@lucide/vue';
import { Head } from '@inertiajs/vue3';

interface Certificate {
    id: number;
    certificate_number: string;
    participant?: {
        user?: {
            name: string;
            email: string;
        };
        event?: {
            title: string;
        };
    };
    issued_at: string;
}

defineProps<{
    certificates: Certificate[];
}>();
</script>
