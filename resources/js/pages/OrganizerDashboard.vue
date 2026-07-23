<template>
    <Head title="Organizer Dashboard" />
    <div class="mx-auto max-w-7xl space-y-8 font-sans">
        <div
            class="relative flex flex-col justify-between gap-6 overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 p-8 text-white shadow-2xl md:flex-row md:items-center lg:p-10"
        >
            <div
                class="bg-gradient-radial absolute top-0 right-0 h-[500px] w-[500px] translate-x-1/3 -translate-y-1/2 rounded-full from-[#6D4AFF]/20 to-transparent blur-3xl"
            ></div>
            <div
                class="absolute bottom-[-100px] left-[-100px] h-[300px] w-[300px] rounded-full bg-slate-800/50 mix-blend-screen blur-3xl"
            ></div>

            <div class="relative z-10 flex-1 space-y-3">
                <div
                    class="mb-2 inline-flex items-center gap-2 rounded-full border border-slate-700/50 bg-slate-800/80 px-3 py-1 text-xs font-bold tracking-widest text-[#F59E0B] backdrop-blur-sm"
                >
                    <span
                        class="h-2 w-2 animate-pulse rounded-full bg-[#F59E0B]"
                    ></span>
                    ACTIVE ORGANIZER
                </div>
                <h1
                    class="font-serif text-3xl font-extrabold tracking-tight text-white drop-shadow-md lg:text-5xl"
                >
                    Welcome,
                    <span
                        class="bg-gradient-to-r from-[#6D4AFF] to-[#2563EB] bg-clip-text text-transparent"
                        >{{ auth.user.name }}</span
                    >
                </h1>
                <p class="max-w-2xl text-lg font-light text-slate-300">
                    Overview of your assigned events, scan activity, and
                    participant check-ins.
                </p>
            </div>

            <div
                class="relative z-10 flex shrink-0 items-center gap-4 rounded-2xl border border-white/20 bg-white/10 p-4 shadow-xl backdrop-blur-md"
            >
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-xl bg-[#6D4AFF] text-white shadow-[0_0_20px_rgba(109,74,255,0.4)]"
                >
                    <CalendarDays class="h-8 w-8" />
                </div>
                <div>
                    <p class="text-3xl font-bold">{{ stats.todaysEvents }}</p>
                </div>
            </div>
        </div>

        <div>
            <h2
                class="mb-6 flex items-center gap-2 font-serif text-2xl font-bold tracking-tight text-slate-900 drop-shadow-sm dark:text-slate-100"
            >
                <Activity class="h-6 w-6 text-[#F59E0B]" />
                Quick Access Dashboard
            </h2>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <Link
                    href="/organizer/scanner"
                    class="group relative flex min-h-[220px] cursor-pointer flex-col justify-between overflow-hidden rounded-2xl border border-slate-700 bg-gradient-to-br from-slate-900 to-slate-800 p-6 shadow-xl transition-all duration-300 active:scale-[0.98]"
                >
                    <div
                        class="absolute top-[-50px] right-[-50px] h-32 w-32 rounded-full bg-[#6D4AFF]/20 blur-2xl transition-colors duration-500 group-hover:bg-[#6D4AFF]/40"
                    ></div>
                    <div
                        class="relative z-10 mb-4 flex items-center justify-between"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/20 bg-white/10 text-emerald-400 shadow-inner backdrop-blur-md transition-all duration-500 group-hover:scale-110 group-hover:bg-emerald-500/80 group-hover:text-white"
                        >
                            <ScanLine class="h-7 w-7" />
                        </div>
                        <div
                            class="rounded-full border border-slate-700 bg-slate-800 p-2 text-slate-400 group-hover:text-emerald-400"
                        >
                            <svg
                                class="h-5 w-5 -rotate-45"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"
                                />
                            </svg>
                        </div>
                    </div>
                    <div class="relative z-10">
                        <h3
                            class="mb-2 font-serif text-xl font-bold tracking-tight text-white transition-colors group-hover:text-emerald-400"
                        >
                            Launch QR Scanner
                        </h3>
                        <p
                            class="text-sm leading-relaxed font-light text-slate-400"
                        >
                            Activate camera to instantly process incoming
                            attendee tickets.
                        </p>
                    </div>
                </Link>

                <Link
                    href="/organizer/events"
                    class="group relative flex min-h-[220px] cursor-pointer flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-xl active:scale-[0.98] dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="relative z-10 mb-4 flex items-center justify-between"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 text-slate-600 transition-all duration-500 group-hover:scale-110 group-hover:bg-[#6D4AFF] group-hover:text-white dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400"
                        >
                            <CalendarDays class="h-7 w-7" />
                        </div>
                    </div>
                    <div class="relative z-10 mt-auto">
                        <h3
                            class="mb-2 font-serif text-xl font-bold tracking-tight text-slate-900 transition-colors group-hover:text-[#6D4AFF] dark:text-slate-100"
                        >
                            Managed Events
                        </h3>
                        <p
                            class="text-sm leading-relaxed font-light text-slate-500 dark:text-slate-400"
                        >
                            View details and schedules for the events you are
                            assigned to.
                        </p>
                    </div>
                </Link>

                <Link
                    href="/live-attendance"
                    class="group relative flex min-h-[220px] cursor-pointer flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-xl active:scale-[0.98] dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="relative z-10 mb-4 flex items-center justify-between"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 text-slate-600 transition-all duration-500 group-hover:scale-110 group-hover:bg-indigo-500 group-hover:text-white dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400"
                        >
                            <Users class="h-7 w-7" />
                        </div>
                    </div>
                    <div class="relative z-10 mt-auto">
                        <h3
                            class="mb-2 font-serif text-xl font-bold tracking-tight text-slate-900 transition-colors group-hover:text-indigo-600 dark:text-slate-100"
                        >
                            Live Attendance
                        </h3>
                        <p
                            class="text-sm leading-relaxed font-light text-slate-500 dark:text-slate-400"
                        >
                            Monitor real-time check-ins and search the
                            participant roster.
                        </p>
                    </div>
                </Link>
            </div>
        </div>

        <div class="pt-4 pb-12 sm:pt-6">
            <div class="mb-6 flex items-center justify-between px-2">
                <h2
                    class="flex items-center gap-2 font-serif text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100"
                >
                    <Activity class="h-6 w-6 text-[#F59E0B]" />
                    Live Event Feed
                </h2>
                <div
                    class="flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1"
                >
                    <div
                        class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"
                    ></div>
                    <span
                        class="text-[10px] leading-none font-black tracking-widest text-emerald-600 uppercase"
                        >Happening Today</span
                    >
                </div>
            </div>

            <div
                class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    <div
                        v-if="liveEvents.length === 0"
                        class="flex flex-col items-center gap-4 p-16 text-center font-light text-slate-400 italic"
                    >
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 dark:bg-slate-800"
                        >
                            <CalendarDays class="h-8 w-8 opacity-20" />
                        </div>
                        <p>No events scheduled for today.</p>
                    </div>
                    <div
                        v-else
                        v-for="event in liveEvents"
                        :key="event.id"
                        class="group flex items-center justify-between p-6 transition-all duration-300 hover:bg-slate-50 dark:hover:bg-slate-800/50"
                    >
                        <div class="flex items-center gap-5">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-800 bg-slate-900 text-sm font-bold text-[#6D4AFF] shadow-lg transition-all group-hover:scale-110 group-hover:-rotate-3"
                            >
                                EV
                            </div>
                            <div>
                                <p
                                    class="text-lg leading-tight font-extrabold text-slate-900 dark:text-slate-100"
                                >
                                    {{ event.title }}
                                </p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span
                                        class="rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold tracking-tighter text-emerald-600 uppercase dark:bg-emerald-500/10 dark:text-emerald-400"
                                        >Current</span
                                    >
                                    <span
                                        class="px-1 text-xs text-slate-300 dark:text-slate-600"
                                        >•</span
                                    >
                                    <p
                                        class="text-[11px] font-bold tracking-widest text-slate-400 uppercase"
                                    >
                                        {{ event.organizer?.name || 'No Organizer' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-sm font-black tracking-tight text-slate-900 dark:text-slate-100"
                            >
                                {{
                                    new Date(
                                        event.start_time,
                                    ).toLocaleTimeString([], {
                                        hour: '2-digit',
                                        minute: '2-digit',
                                    })
                                }} - {{
                                    new Date(
                                        event.end_time,
                                    ).toLocaleTimeString([], {
                                        hour: '2-digit',
                                        minute: '2-digit',
                                    })
                                }}
                            </p>
                            <p
                                class="mt-1 text-[10px] font-bold tracking-widest text-[#6D4AFF] uppercase"
                            >
                                {{
                                    new Date(
                                        event.start_time,
                                    ).toLocaleDateString()
                                }}
                            </p>
                        </div>
                    </div>
                </div>
                <Link
                    v-if="liveEvents.length > 0"
                    href="/my-events"
                    class="group block w-full border-t border-slate-100 bg-slate-50/30 py-5 text-center text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase transition-all hover:bg-white hover:text-[#6D4AFF] dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800"
                >
                    <span class="transition-all group-hover:tracking-[0.25em]"
                        >View All Events</span
                    >
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage, Head } from '@inertiajs/vue3';
import { ScanLine, Users, CalendarDays, Activity } from '@lucide/vue';

const props = defineProps<{
    stats: {
        totalEvents: number;
        todaysEvents: number;
        totalScans: number;
    };
    liveEvents: any[];
}>();

const page = usePage();
const auth = computed(() => page.props.auth as any);
</script>
