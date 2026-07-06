<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-slate-50 p-4 font-['Outfit'] sm:p-6 dark:bg-slate-950">
    <Head :title="`Register for ${event.title}`" />

    <div class="w-full max-w-lg">
      <div class="mb-8 flex items-center justify-center gap-2">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-[#C5A059] dark:bg-slate-800">
          <div class="text-xl font-bold">Q</div>
        </div>
        <span class="text-2xl font-black tracking-tighter text-slate-900 uppercase italic dark:text-slate-100">
          QUARTZ
        </span>
      </div>

      <div class="overflow-hidden rounded-[2.5rem] border border-slate-100 bg-white shadow-2xl shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
        <div class="relative overflow-hidden bg-slate-900 p-8 text-white">
          <div class="absolute top-0 right-0 -mt-16 -mr-16 h-32 w-32 rounded-full bg-[#C5A059]/10 blur-3xl"></div>
          <div class="relative z-10">
            <span class="mb-4 inline-block rounded-full bg-[#C5A059]/20 px-3 py-1 text-[10px] font-bold tracking-widest text-[#C5A059] uppercase">
              Event Registration
            </span>
            <h1 class="mb-4 text-2xl leading-tight font-bold">
              {{ event.title }}
            </h1>

            <div class="space-y-2">
              <div class="flex items-center gap-2 text-sm text-slate-400">
                <Calendar class="h-4 w-4 text-[#C5A059]" />
                {{ new Date(event.start_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
              </div>
              <div v-if="event.location" class="flex items-center gap-2 text-sm text-slate-400">
                <MapPin class="h-4 w-4 text-[#C5A059]" />
                {{ event.location }}
              </div>
            </div>
          </div>
        </div>

        <div class="p-8">
          <div class="mb-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
              Join this event
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              Please provide your details to secure your spot.
            </p>
          </div>

          <form @submit.prevent="submit" class="space-y-6">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                Full Name
              </label>
              <div class="relative">
                <User class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                  type="text"
                  v-model="form.name"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pr-4 pl-12 transition-all outline-none focus:border-[#C5A059] focus:bg-white focus:ring-4 focus:ring-[#C5A059]/5 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-[#C5A059]"
                  placeholder="e.g. John Doe"
                  required
                />
              </div>
              <p v-if="form.errors.name" class="mt-1.5 ml-1 text-xs text-rose-500">
                {{ form.errors.name }}
              </p>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                Email Address
              </label>
              <div class="relative">
                <Mail class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                  type="email"
                  v-model="form.email"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pr-4 pl-12 transition-all outline-none focus:border-[#C5A059] focus:bg-white focus:ring-4 focus:ring-[#C5A059]/5 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-[#C5A059]"
                  placeholder="your@email.com"
                  required
                />
              </div>
              <p v-if="form.errors.email" class="mt-1.5 ml-1 text-xs text-rose-500">
                {{ form.errors.email }}
              </p>
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 py-4 font-bold text-white shadow-xl shadow-slate-900/10 transition-all hover:-translate-y-0.5 hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:shadow-none dark:hover:bg-slate-200"
            >
              {{ form.processing ? 'Processing...' : 'Complete Registration' }}
              <ArrowRight class="h-5 w-5" />
            </button>
          </form>

          <p class="mt-8 text-center text-[10px] font-bold tracking-widest text-slate-400 uppercase">
            By registering, you agree to receive event-related emails and your individual attendee QR code.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import { Calendar, MapPin, User, Mail, ArrowRight } from '@lucide/vue';

interface Event {
    id: number;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    location: string;
    registration_token: string;
}

const props = defineProps<{
    event: Event;
}>();

const form = useForm({
    name: '',
    email: '',
});

const submit = () => {
    form.post(`/portal/events/${props.event.id}/register`);
};
</script>
