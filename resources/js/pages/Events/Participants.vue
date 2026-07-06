<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Eye, Plus, QrCode, Search, Trash2, Upload, UserMinus, Users, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Participant {
    id: number;
    user: { id: number; name: string; email: string };
    qr_code_path: string | null;
    has_attended: boolean;
    registered_at: string;
}

interface PaginatedParticipants {
    data: Participant[];
    links: { url: string | null; label: string; active: boolean }[];
    meta: {
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
    };
}

interface Event {
    id: number;
    title: string;
    registration_type: 'public' | 'static';
}

const props = defineProps<{
    event: Event;
    participants: PaginatedParticipants;
    filters?: { search?: string };
}>();

defineOptions({
    layout: (props: { event?: Event }) => ({
        breadcrumbs: [
            { title: 'Events', href: '/events' },
            { title: props.event?.title ?? 'Event', href: `/events/${props.event?.id}` },
            { title: 'Participants', href: '#' },
        ],
    }),
});

const search = ref(props.filters?.search ?? '');
const showAddModal = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(`/events/${props.event.id}/participants`, { search: search.value }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const addForm = useForm({
    email: '',
});

function addParticipant() {
    addForm.post(`/events/${props.event.id}/participants`, {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
        },
        preserveScroll: true,
    });
}

function removeParticipant(participantId: number) {
    if (confirm('Remove this participant?')) {
        router.delete(`/events/${props.event.id}/participants/${participantId}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head :title="`Participants — ${event.title}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <Link :href="`/events/${event.id}`">
                    <Button variant="ghost" size="icon-sm" class="mt-1">
                        <ChevronLeft class="size-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">Participants</h1>
                    <p class="mt-0.5 text-sm text-muted-foreground">{{ event.title }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <Link v-if="event.registration_type === 'static'" :href="`/events/${event.id}/participants/upload`">
                    <Button variant="outline" size="sm" class="gap-2">
                        <Upload class="size-4" />
                        Upload CSV
                    </Button>
                </Link>
                <Button size="sm" class="gap-2 bg-gradient-to-r from-violet-600 to-indigo-600 text-white" @click="showAddModal = true">
                    <Plus class="size-4" />
                    Add Participant
                </Button>
            </div>
        </div>

        <!-- Search -->
        <div class="relative max-w-sm">
            <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
            <Input v-model="search" placeholder="Search participants..." class="pl-9" />
        </div>

        <!-- Table -->
        <div class="rounded-xl border border-border bg-card shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/40">
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Email</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-muted-foreground">QR Code</th>
                            <th class="px-4 py-3 text-right font-semibold text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <template v-if="participants.data.length > 0">
                            <tr
                                v-for="participant in participants.data"
                                :key="participant.id"
                                class="group hover:bg-muted/30 transition-colors"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex size-8 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 text-xs font-bold text-white">
                                            {{ participant.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="font-medium text-foreground">{{ participant.user.name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ participant.user.email }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                            participant.has_attended
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                                        ]"
                                    >
                                        {{ participant.has_attended ? 'Attended' : 'Registered' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="participant.qr_code_path" class="flex items-center gap-2">
                                        <img
                                            :src="`/storage/${participant.qr_code_path}`"
                                            alt="QR Code"
                                            class="size-10 rounded border border-border object-cover"
                                        />
                                        <span class="text-xs text-green-600 dark:text-green-400 font-medium">Ready</span>
                                    </div>
                                    <span v-else class="inline-flex items-center gap-1 text-xs text-muted-foreground">
                                        <QrCode class="size-3" />
                                        Pending
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link v-if="participant.qr_code_path" :href="`/events/${event.id}/participants/${participant.id}/qr`" target="_blank">
                                            <Button variant="ghost" size="icon-sm" class="opacity-0 group-hover:opacity-100 transition-opacity" title="View QR">
                                                <Eye class="size-4" />
                                            </Button>
                                        </Link>
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
                                    <p class="font-medium text-muted-foreground">
                                        {{ search ? 'No participants match your search.' : 'No participants yet.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="participants.meta && participants.meta.last_page > 1" class="flex items-center justify-between border-t border-border px-4 py-3">
                <p class="text-sm text-muted-foreground">
                    Showing {{ participants.meta.from }}–{{ participants.meta.to }} of {{ participants.meta.total }}
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in participants.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url" preserve-state>
                            <Button variant="ghost" size="sm" :class="link.active ? 'bg-primary text-primary-foreground' : ''" v-html="link.label" />
                        </Link>
                        <Button v-else variant="ghost" size="sm" disabled v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Participant Modal -->
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="showAddModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="showAddModal = false"
            >
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showAddModal = false" />
                <div class="relative z-10 w-full max-w-md rounded-2xl border border-border bg-card shadow-2xl">
                    <div class="flex items-center justify-between border-b border-border px-6 py-4">
                        <h2 class="text-lg font-semibold text-foreground">Add Participant</h2>
                        <Button variant="ghost" size="icon-sm" @click="showAddModal = false">
                            <X class="size-4" />
                        </Button>
                    </div>
                    <form @submit.prevent="addParticipant" class="p-6 space-y-4">
                        <div class="grid gap-2">
                            <Label for="email">Email Address</Label>
                            <Input
                                id="email"
                                v-model="addForm.email"
                                type="email"
                                placeholder="participant@email.com"
                                autofocus
                                :class="{ 'border-destructive': addForm.errors.email }"
                            />
                            <p v-if="addForm.errors.email" class="text-xs text-destructive">{{ addForm.errors.email }}</p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <Button type="button" variant="outline" @click="showAddModal = false">Cancel</Button>
                            <Button type="submit" :disabled="addForm.processing" class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white">
                                {{ addForm.processing ? 'Adding...' : 'Add Participant' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
