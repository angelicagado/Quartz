<template>
    <div
        class="group flex h-full min-h-[360px] cursor-pointer flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-[#6D4AFF]/30 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
    >
        <!-- Header / Banner Area -->
        <div class="relative h-28 flex-shrink-0 bg-slate-900">
            <div
                class="absolute inset-0 bg-gradient-to-r from-slate-900 to-slate-800"
            ></div>

            <!-- Status Badge -->
            <div class="absolute top-4 right-4 z-10">
                <span
                    class="rounded-md px-2.5 py-1 text-xs font-semibold tracking-wide"
                    :class="
                        isUpcoming
                            ? 'border border-emerald-500/30 bg-emerald-500/20 text-emerald-400'
                            : 'border border-slate-500/30 bg-slate-500/20 text-slate-300 shadow-sm'
                    "
                >
                    {{ isUpcoming ? 'UPCOMING' : 'COMPLETED' }}
                </span>
            </div>

            <div
                class="absolute bottom-[-20px] left-5 flex items-center justify-center rounded-xl border border-slate-100 bg-white p-2 shadow-md transition-transform duration-300 group-hover:scale-110 dark:border-slate-800 dark:bg-slate-950"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#6D4AFF]/10 text-[#6D4AFF]"
                >
                    <Calendar class="h-5 w-5" />
                </div>
            </div>
        </div>

        <div
            class="relative flex w-full flex-1 flex-col overflow-hidden p-6 pt-8"
        >
            <h5
                class="mb-2 line-clamp-1 text-xl font-bold text-slate-900 transition-colors group-hover:text-[#6D4AFF] dark:text-slate-100"
            >
                {{ event.title }}
            </h5>
            <p
                class="mb-5 line-clamp-2 flex-1 text-sm text-slate-500 dark:text-slate-400"
            >
                {{ event.description }}
            </p>

            <div class="mt-auto w-full space-y-3">
                <div
                    class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-400"
                >
                    <Clock class="h-4 w-4 shrink-0 text-slate-400" />
                    <span class="truncate">
                        {{
                            new Date(event.start_time).toLocaleDateString(
                                'en-US',
                                {
                                    month: 'short',
                                    day: 'numeric',
                                    year: 'numeric',
                                },
                            )
                        }}
                        <span class="mx-1 text-slate-300 dark:text-slate-600"
                            >•</span
                        >
                        {{
                            new Date(event.end_time).toLocaleDateString(
                                'en-US',
                                {
                                    month: 'short',
                                    day: 'numeric',
                                    year: 'numeric',
                                },
                            )
                        }}
                    </span>
                </div>

                <div
                    class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-400"
                >
                    <MapPin class="h-4 w-4 shrink-0 text-slate-400" />
                    <span class="truncate">
                        {{ event.location || 'Location TBD' }}
                    </span>
                </div>

                <div
                    class="my-4 h-[1px] w-full bg-slate-100 dark:bg-slate-800"
                ></div>

                <!-- Features Badges -->
                <div class="flex flex-wrap items-center gap-4">
                    <div
                        class="flex items-center gap-1.5 text-xs font-medium text-slate-500"
                        title="Participants"
                    >
                        <Users
                            class="inline-block h-4 w-4 shrink-0 text-slate-400"
                        />
                        <span>{{ event.participants_count || 0 }} Registered</span>
                    </div>

                    <div
                        class="flex items-center gap-1.5 text-xs font-medium text-slate-500"
                        title="Sessions"
                    >
                        <Layers
                            class="inline-block h-4 w-4 shrink-0 text-slate-400"
                        />
                        <span>{{ event.sessions_count || 0 }} Sessions</span>
                    </div>

                    <div
                        class="flex items-center gap-1.5 text-xs font-medium text-slate-500"
                        title="Registration Type"
                    >
                        <Tags
                            class="inline-block h-4 w-4 shrink-0 text-slate-400"
                        />
                        <span class="capitalize">{{ event.registration_type ? event.registration_type.replace('_', ' ') : 'Internal' }}</span>
                    </div>

                    <div
                        v-if="event.has_evaluation_form"
                        class="flex items-center gap-1.5 text-xs font-medium text-emerald-600"
                        title="Evaluation Form Created"
                    >
                        <FileCheck
                            class="inline-block h-4 w-4 shrink-0 text-emerald-500"
                        />
                        <span>Eval Ready</span>
                    </div>
                    <div
                        v-else-if="event.evaluation_required"
                        class="flex items-center gap-1.5 text-xs font-medium text-rose-500"
                        title="Evaluation Required but not created"
                    >
                        <FileX
                            class="inline-block h-4 w-4 shrink-0 text-rose-400"
                        />
                        <span>No Eval</span>
                    </div>

                    <div
                        v-if="event.has_certificate_template"
                        class="flex items-center gap-1.5 text-xs font-medium text-[#6D4AFF]"
                        title="Certificate Template Created"
                    >
                        <Award
                            class="inline-block h-4 w-4 shrink-0 text-[#6D4AFF]"
                        />
                        <span>Cert Ready</span>
                    </div>
                    <div
                        v-else-if="event.certificate_enabled"
                        class="flex items-center gap-1.5 text-xs font-medium text-rose-500"
                        title="Certificate Enabled but not created"
                    >
                        <Award
                            class="inline-block h-4 w-4 shrink-0 text-rose-400 opacity-60"
                        />
                        <span>No Cert</span>
                    </div>
                </div>
                
                <div class="mt-3 flex items-center gap-2.5 text-xs text-slate-500 dark:text-slate-400" title="Registration Dates">
                    <CalendarPlus class="h-4 w-4 shrink-0 text-slate-400" />
                    <span class="truncate">
                        Reg: 
                        {{
                            event.registration_start_date ? new Date(event.registration_start_date).toLocaleDateString(
                                'en-US',
                                {
                                    month: 'short',
                                    day: 'numeric',
                                    hour: 'numeric',
                                    minute: '2-digit'
                                },
                            ) : 'N/A'
                        }}
                        -
                        {{
                            event.registration_end_date ? new Date(event.registration_end_date).toLocaleDateString(
                                'en-US',
                                {
                                    month: 'short',
                                    day: 'numeric',
                                    hour: 'numeric',
                                    minute: '2-digit'
                                },
                            ) : 'N/A'
                        }}
                    </span>
                </div>
            </div>

            <div class="mt-6 w-full">
                <button
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 transition-all duration-300 hover:border-transparent hover:bg-slate-900 hover:text-white active:scale-[0.98] dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                    @click="$emit('view', event)"
                >
                    Manage Event
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Calendar, Users, FileCheck, FileX, Award, MapPin, Clock, Layers, Tags, CalendarPlus } from '@lucide/vue';

// Define the type locally if not imported
interface Event {
    title: string;
    description: string;
    start_time: string;
    end_time: string;
    location?: string;
    participants_count?: number;
    sessions_count?: number;
    registration_type?: string;
    registration_start_date?: string;
    registration_end_date?: string;
    evaluation_required?: boolean;
    certificate_enabled?: boolean;
    has_evaluation_form?: boolean;
    has_certificate_template?: boolean;
    [key: string]: any;
}

const props = defineProps<{
    event: Event;
}>();

defineEmits(['view']);

const isUpcoming = computed(
    () => new Date(props.event.start_time) > new Date(),
);
</script>
