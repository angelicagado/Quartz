<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-white p-4 font-['Outfit'] sm:p-6 dark:bg-slate-950">
    <Head title="Registration Successful" />

    <div class="flex w-full max-w-2xl flex-col items-center bg-white dark:bg-slate-950">
      <div class="mb-6 flex h-20 w-20 animate-in items-center justify-center rounded-full bg-emerald-100 text-emerald-600 duration-700 zoom-in dark:bg-emerald-500/10 dark:text-emerald-400">
        <CheckCircle class="h-10 w-10" />
      </div>

      <h1 class="mb-2 text-center text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">
        Registration Confirmed!
      </h1>
      <p class="mb-10 text-center text-lg text-slate-500 dark:text-slate-400">
        You're all set! Below is your individual entry pass for the event.
      </p>

      <div class="grid w-full items-stretch gap-8 md:grid-cols-2">
        <div class="group relative flex flex-col items-center justify-center overflow-hidden rounded-[2.5rem] bg-slate-900 p-8 shadow-2xl dark:border dark:border-slate-800">
          <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#C5A059]/10 to-transparent"></div>

          <div
            id="qr-code-to-download"
            class="relative z-10 rounded-3xl bg-white p-4 shadow-lg transition-transform duration-500 group-hover:scale-105"
          >
            <qrcode-vue :value="participant.qr_token" :size="200" level="H" />
          </div>

          <button
            @click="downloadQR"
            class="mt-8 flex items-center gap-2 text-sm font-bold tracking-widest text-white/80 uppercase transition-all hover:gap-3 hover:text-white"
          >
            <Download class="h-4 w-4 text-[#C5A059]" />
            Save Ticket to Device
          </button>
        </div>

        <div class="flex h-full flex-col rounded-[2.5rem] border border-slate-100 bg-slate-50 p-8 dark:border-slate-800 dark:bg-slate-900">
          <div class="flex-1 space-y-6">
            <div>
              <label class="mb-2 block text-[10px] font-bold tracking-[0.2em] text-slate-400 uppercase">
                Attendee
              </label>
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 transition-colors group-hover:text-[#C5A059] dark:border-slate-700 dark:bg-slate-800">
                  <User class="h-5 w-5" />
                </div>
                <div>
                  <div class="font-bold text-slate-900 dark:text-slate-100">
                    {{ participant.name || participant.user?.name || 'Guest Attendee' }}
                  </div>
                  <div class="text-xs text-slate-500 italic dark:text-slate-400">
                    {{ participant.email || participant.user?.email }}
                  </div>
                </div>
              </div>
            </div>

            <div>
              <label class="mb-2 block text-[10px] font-bold tracking-[0.2em] text-slate-400 uppercase">
                Event Information
              </label>
              <div class="space-y-3">
                <div class="flex items-center gap-3 font-medium text-slate-600 dark:text-slate-300">
                  <ArrowRight class="h-4 w-4 text-[#C5A059]" />
                  <span>{{ participant.event.title }}</span>
                </div>
                <div class="flex items-center gap-3 text-slate-600 dark:text-slate-300">
                  <Calendar class="h-4 w-4 text-[#C5A059]" />
                  <span class="text-sm">
                    {{ new Date(participant.event.start_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) }}
                  </span>
                </div>
                <div v-if="participant.event.location" class="flex items-center gap-3 text-slate-600 dark:text-slate-300">
                  <MapPin class="h-4 w-4 text-[#C5A059]" />
                  <span class="text-sm">
                    {{ participant.event.location }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mx-auto mt-12 max-w-md text-center">
        <p class="text-sm font-light text-slate-400 italic">
          Please present this QR code at the event entrance for attendance verification. A copy of this ticket has been sent to your email.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CheckCircle, Download, Calendar, MapPin, ArrowRight, User } from '@lucide/vue';
import QrcodeVue from 'qrcode.vue';

interface Participant {
    id: number;
    qr_token: string;
    name?: string;
    email?: string;
    user?: {
        name: string;
        email: string;
    };
    event: {
        title: string;
        start_date: string;
        location: string;
    };
}

const props = defineProps<{
    participant: Participant;
}>();

const downloadQR = () => {
    const qrContainer = document.getElementById('qr-code-to-download');
    const svg = qrContainer?.querySelector('svg');
    if (!svg) {
        alert('Could not find the QR code. Please try again or take a screenshot.');
        return;
    }

    const svgData = new XMLSerializer().serializeToString(svg);
    const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(svgBlob);

    const img = new Image();
    img.onload = () => {
        const canvas = document.createElement('canvas');
        const scaleFactor = 2;
        canvas.width = (svg.clientWidth || 200) * scaleFactor;
        canvas.height = (svg.clientHeight || 200) * scaleFactor;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

        try {
            const pngFile = canvas.toDataURL('image/png');
            const downloadLink = document.createElement('a');
            const attendeeName = (props.participant.name || props.participant.user?.name || 'Attendee').replace(/\s+/g, '_');
            downloadLink.download = `Ticket_${attendeeName}.png`;
            downloadLink.href = pngFile;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        } finally {
            URL.revokeObjectURL(url);
        }
    };
    img.src = url;
};
</script>
