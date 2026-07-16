<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Award, Download } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import ParticipantLayout from '@/layouts/ParticipantLayout.vue';
import ProfileRail from '@/components/portal/ProfileRail.vue';
import { ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';

interface CertificateItem {
    id: number;
    certificate_number: string;
    issue_date: string;
    event: {
        id: number;
        title: string;
        start_time: string;
    };
    download_url: string;
}

defineProps<{
    certificates: CertificateItem[];
}>();

defineOptions({ layout: ParticipantLayout });

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}

const isProcessing = ref<Record<number, boolean>>({});

async function downloadCertificate(certificateId: number, url: string, filename: string) {
    if (isProcessing.value[certificateId]) return;
    isProcessing.value[certificateId] = true;
    
    try {
        const response = await axios.get(url, { responseType: 'blob' });
        
        if (response.data.type && response.data.type.includes('text/html')) {
             throw new Error("Failed to load certificate.");
        }
        
        const blobUrl = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = blobUrl;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        
        toast.success("Certificate downloaded successfully.");
    } catch (error) {
        toast.error("We couldn't download your certificate. Please try again later.");
    } finally {
        isProcessing.value[certificateId] = false;
    }
}
</script>

<template>
    <Head title="My Certificates" />

    <div class="grid gap-6 p-4 sm:p-6 lg:grid-cols-[240px_minmax(0,1fr)] lg:gap-20">
        <ProfileRail />
        <div class="flex min-w-0 flex-1 flex-col gap-6">
        <!-- Header -->
        <div>
            <div class="mb-1 flex items-center gap-2">
                <div
                    class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 shadow-sm"
                >
                    <Award class="size-4 text-white" />
                </div>
                <span
                    class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                    >Certificates</span
                >
            </div>
            <h1
                class="font-serif text-3xl font-bold tracking-tight text-foreground"
            >
                Your Certificates
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Download the certificates you have earned.
            </p>
        </div>

        <!-- Certificates -->
        <div v-if="certificates.length > 0" class="flex flex-col gap-4">
            <div
                v-for="certificate in certificates"
                :key="certificate.id"
                class="flex flex-col gap-4 rounded-2xl border border-border bg-card p-5 shadow-xs sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-sm"
                    >
                        <Award class="size-6 text-white" />
                    </div>
                    <div class="min-w-0">
                        <h3
                            class="truncate font-serif font-semibold text-foreground"
                        >
                            {{ certificate.event.title }}
                        </h3>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            {{ certificate.certificate_number }} · Issued
                            {{ formatDate(certificate.issue_date) }}
                        </p>
                    </div>
                </div>

                <Button
                    @click="downloadCertificate(certificate.id, certificate.download_url, `certificate_${certificate.certificate_number}.png`)"
                    :disabled="isProcessing[certificate.id]"
                    size="sm"
                    class="shrink-0 bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:from-amber-600 hover:to-orange-600"
                >
                    <span v-if="isProcessing[certificate.id]" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent mr-2"></span>
                    <Download v-else class="size-4 mr-1" />
                    Download
                </Button>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center gap-4 py-20"
        >
            <div
                class="flex size-20 items-center justify-center rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30"
            >
                <Award class="size-10 text-amber-500 dark:text-amber-400" />
            </div>
            <div class="text-center">
                <p class="text-xl font-semibold text-foreground">
                    No certificates yet
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Attend events and complete evaluations to earn certificates.
                </p>
            </div>
            <Link href="/portal/my-events">
                <Button
                    class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white hover:from-violet-700 hover:to-indigo-700"
                >
                    View My Events
                </Button>
            </Link>
        </div>
        </div>
    </div>
</template>
