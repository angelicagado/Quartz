<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Award,
    CalendarDays,
    ChevronLeft,
    ClipboardCheck,
    Clock,
    Edit,
    FileText,
    Mail,
    MonitorPlay,
    QrCode,
    Trash2,
    Upload,
    UserCheck,
    UserMinus,
    Users,
} from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

interface Participant {
    id: number;
    user: { id: number; name: string; email: string };
    qr_code_path: string | null;
    has_attended: boolean;
    registered_at: string;
}

interface AttendanceRecord {
    id: number;
    participant: { user: { name: string; email: string } };
    scan_type: 'one_time' | 'am' | 'pm';
    scanned_at: string;
}

interface EvaluationForm {
    id: number;
    title: string;
    questions_count: number;
}

interface CertificateTemplate {
    id: number;
    name: string;
}

interface Event {
    id: number;
    title: string;
    description: string | null;
    start_time: string;
    end_time: string;
    registration_type: 'public' | 'static';
    attendance_type: 'one_time' | 'am_pm';
    status: string;
    evaluation_required: boolean;
    certificate_enabled: boolean;
    participants_count: number;
    attended_count: number;
    evaluations_count: number;
    certificates_count: number;
    participants: Participant[];
    attendances: AttendanceRecord[];
    evaluationForm: EvaluationForm | null;
    certificateTemplate: CertificateTemplate | null;
}

const props = defineProps<{
    event: Event;
}>();

defineOptions({
    layout: (props: { event?: Event }) => ({
        breadcrumbs: [
            { title: 'Events', href: '/events' },
            { title: props.event?.title ?? 'Event', href: '#' },
        ],
    }),
});

const activeTab = ref<'participants' | 'attendance' | 'evaluation' | 'certificate'>('participants');

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'short', month: 'long', day: 'numeric', year: 'numeric',
    });
}

function formatDateTime(dateStr: string) {
    return new Date(dateStr).toLocaleString('en-US', {
        month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}

function removeParticipant(participantId: number) {
    if (confirm('Remove this participant from the event?')) {
        router.delete(`/events/${props.event.id}/participants/${participantId}`);
    }
}

function resendQr(participantId: number) {
    router.post(`/events/${props.event.id}/participants/${participantId}/resend-qr`);
}

const scanTypeConfig: Record<string, { label: string; classes: string }> = {
    one_time: { label: 'Check-In', classes: 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300' },
    am: { label: 'AM', classes: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' },
    pm: { label: 'PM', classes: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' },
};

const statusConfig: Record<string, { label: string; classes: string }> = {
    upcoming: { label: 'Upcoming', classes: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800' },
    ongoing: { label: 'Ongoing', classes: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 border-green-200 dark:border-green-800' },
    completed: { label: 'Completed', classes: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-200 dark:border-gray-700' },
    cancelled: { label: 'Cancelled', classes: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 border-red-200 dark:border-red-800' },
};
</script>

<template>
    <Head :title="event.title" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <Link href="/events">
                    <Button variant="ghost" size="icon-sm" class="mt-1">
                        <ChevronLeft class="size-4" />
                    </Button>
                </Link>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ event.title }}</h1>
                        <span
                            v-if="statusConfig[event.status]"
                            :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium', statusConfig[event.status].classes]"
                        >
                            {{ statusConfig[event.status].label }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800 capitalize">
                            {{ event.registration_type }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800">
                            {{ event.attendance_type === 'am_pm' ? 'AM/PM' : 'One-Time' }}
                        </span>
                    </div>
                    <div class="mt-1 flex items-center gap-4 text-sm text-muted-foreground">
                        <span class="flex items-center gap-1.5">
                            <CalendarDays class="size-3.5" />
                            {{ formatDate(event.start_time) }}
                        </span>
                        <span>→</span>
                        <span>{{ formatDate(event.end_time) }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <Link :href="`/events/${event.id}/monitor`">
                    <Button variant="outline" size="sm" class="gap-2">
                        <MonitorPlay class="size-4" />
                        Monitor
                    </Button>
                </Link>
                <Link :href="`/events/${event.id}/edit`">
                    <Button variant="outline" size="sm" class="gap-2">
                        <Edit class="size-4" />
                        Edit
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-border bg-card p-4 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/30">
                        <Users class="size-5 text-violet-600 dark:text-violet-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ event.participants_count }}</p>
                        <p class="text-xs text-muted-foreground">Participants</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-border bg-card p-4 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                        <UserCheck class="size-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ event.attended_count }}</p>
                        <p class="text-xs text-muted-foreground">Attended</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-border bg-card p-4 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        <ClipboardCheck class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ event.evaluations_count }}</p>
                        <p class="text-xs text-muted-foreground">Evaluations</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-border bg-card p-4 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <Award class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ event.certificates_count }}</p>
                        <p class="text-xs text-muted-foreground">Certificates</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs + Content -->
        <div class="rounded-xl border border-border bg-card shadow-xs overflow-hidden flex-1">
            <!-- Tab Navigation -->
            <div class="flex items-center gap-0 border-b border-border overflow-x-auto">
                <button
                    v-for="tab in [
                        { key: 'participants', label: 'Participants', icon: Users },
                        { key: 'attendance', label: 'Attendance Log', icon: Clock },
                        { key: 'evaluation', label: 'Evaluation', icon: ClipboardCheck },
                        { key: 'certificate', label: 'Certificate', icon: Award },
                    ]"
                    :key="tab.key"
                    @click="activeTab = tab.key as typeof activeTab"
                    :class="[
                        'flex items-center gap-2 whitespace-nowrap border-b-2 px-5 py-3.5 text-sm font-medium transition-colors',
                        activeTab === tab.key
                            ? 'border-violet-600 text-violet-600 dark:text-violet-400 dark:border-violet-400'
                            : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted-foreground/30'
                    ]"
                >
                    <component :is="tab.icon" class="size-4" />
                    {{ tab.label }}
                </button>

                <!-- Upload button for static events -->
                <div v-if="event.registration_type === 'static'" class="ml-auto pr-4">
                    <Link :href="`/events/${event.id}/participants/upload`">
                        <Button size="sm" variant="outline" class="gap-2">
                            <Upload class="size-4" />
                            Upload Participants
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Participants Tab -->
            <div v-if="activeTab === 'participants'" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/30">
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Email</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">QR Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Attendance</th>
                            <th class="px-4 py-3 text-right font-semibold text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <template v-if="event.participants.length > 0">
                            <tr
                                v-for="participant in event.participants"
                                :key="participant.id"
                                class="group hover:bg-muted/30 transition-colors"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex size-8 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 text-xs font-semibold text-white">
                                            {{ participant.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="font-medium text-foreground">{{ participant.user.name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ participant.user.email }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium',
                                            participant.qr_code_path
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300'
                                        ]"
                                    >
                                        <QrCode class="size-3" />
                                        {{ participant.qr_code_path ? 'Generated' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium',
                                            participant.has_attended
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                                        ]"
                                    >
                                        <UserCheck class="size-3" />
                                        {{ participant.has_attended ? 'Attended' : 'Absent' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            class="opacity-0 group-hover:opacity-100 transition-opacity"
                                            title="Resend QR"
                                            @click="resendQr(participant.id)"
                                        >
                                            <Mail class="size-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            class="opacity-0 group-hover:opacity-100 transition-opacity text-destructive hover:text-destructive hover:bg-destructive/10"
                                            title="Remove"
                                            @click="removeParticipant(participant.id)"
                                        >
                                            <UserMinus class="size-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="5" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex size-14 items-center justify-center rounded-2xl bg-muted">
                                        <Users class="size-7 text-muted-foreground/50" />
                                    </div>
                                    <p class="font-medium text-muted-foreground">No participants yet</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Attendance Log Tab -->
            <div v-else-if="activeTab === 'attendance'" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/30">
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Scan Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <template v-if="event.attendances.length > 0">
                            <tr
                                v-for="record in event.attendances"
                                :key="record.id"
                                class="hover:bg-muted/30 transition-colors"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex size-8 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-teal-600 text-xs font-semibold text-white">
                                            {{ record.participant.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-foreground">{{ record.participant.user.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ record.participant.user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        v-if="scanTypeConfig[record.scan_type]"
                                        :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', scanTypeConfig[record.scan_type].classes]"
                                    >
                                        {{ scanTypeConfig[record.scan_type].label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ formatDateTime(record.scanned_at) }}</td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="3" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex size-14 items-center justify-center rounded-2xl bg-muted">
                                        <Clock class="size-7 text-muted-foreground/50" />
                                    </div>
                                    <p class="font-medium text-muted-foreground">No attendance records yet</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Evaluation Tab -->
            <div v-else-if="activeTab === 'evaluation'" class="p-6">
                <div v-if="event.evaluationForm" class="flex items-start justify-between rounded-xl border border-border bg-muted/20 p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-sm">
                            <ClipboardCheck class="size-6 text-white" />
                        </div>
                        <div>
                            <p class="font-semibold text-foreground">{{ event.evaluationForm.title }}</p>
                            <p class="text-sm text-muted-foreground mt-0.5">{{ event.evaluationForm.questions_count }} questions</p>
                        </div>
                    </div>
                    <Link :href="`/events/${event.id}/evaluation/edit`">
                        <Button variant="outline" size="sm" class="gap-2">
                            <Edit class="size-4" />
                            Edit Form
                        </Button>
                    </Link>
                </div>
                <div v-else class="flex flex-col items-center gap-4 py-12">
                    <div class="flex size-16 items-center justify-center rounded-2xl bg-muted">
                        <ClipboardCheck class="size-8 text-muted-foreground/50" />
                    </div>
                    <div class="text-center">
                        <p class="font-semibold text-foreground">No evaluation form</p>
                        <p class="mt-1 text-sm text-muted-foreground">Create an evaluation form for participants to complete after the event.</p>
                    </div>
                    <Link :href="`/events/${event.id}/evaluation/create`">
                        <Button class="gap-2">
                            <FileText class="size-4" />
                            Create Evaluation Form
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Certificate Tab -->
            <div v-else-if="activeTab === 'certificate'" class="p-6">
                <div v-if="event.certificateTemplate" class="flex items-start justify-between rounded-xl border border-border bg-muted/20 p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-sm">
                            <Award class="size-6 text-white" />
                        </div>
                        <div>
                            <p class="font-semibold text-foreground">{{ event.certificateTemplate.name }}</p>
                            <p class="text-sm text-muted-foreground mt-0.5">Certificate template configured</p>
                        </div>
                    </div>
                    <Link :href="`/events/${event.id}/certificate/edit`">
                        <Button variant="outline" size="sm" class="gap-2">
                            <Edit class="size-4" />
                            Edit Template
                        </Button>
                    </Link>
                </div>
                <div v-else class="flex flex-col items-center gap-4 py-12">
                    <div class="flex size-16 items-center justify-center rounded-2xl bg-muted">
                        <Award class="size-8 text-muted-foreground/50" />
                    </div>
                    <div class="text-center">
                        <p class="font-semibold text-foreground">No certificate template</p>
                        <p class="mt-1 text-sm text-muted-foreground">Set up a certificate template to automatically issue certificates to attendees.</p>
                    </div>
                    <Link :href="`/events/${event.id}/certificate/create`">
                        <Button class="gap-2 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white">
                            <Award class="size-4" />
                            Create Certificate Template
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
