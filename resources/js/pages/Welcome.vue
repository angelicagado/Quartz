<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { dashboard, login, register } from "@/routes";

const page = usePage();
const dashboardUrl = computed(() =>
  page.props.currentTeam ? dashboard(page.props.currentTeam.slug).url : "/"
);
</script>

<template>
  <Head title="Welcome">
    <link
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
  </Head>

  <div
    class="bg-surface-dim text-on-surface font-body-md antialiased overflow-x-hidden selection:bg-primary-container selection:text-on-primary-container min-h-screen"
  >
    <!-- TopNavBar -->
    <nav
      class="docked full-width top-0 sticky z-50 bg-surface-dim/80 backdrop-blur-xl border-b border-outline-variant/10 shadow-md"
    >
      <div
        class="max-w-container-max mx-auto flex justify-between items-center px-margin-desktop h-20 max-md:px-margin-mobile"
      >
        <div
          class="text-headline-md font-headline-md font-bold tracking-widest text-primary"
        >
          QUARTZ
        </div>
        <div class="hidden md:flex gap-gutter items-center text-body-md font-body-md">
          <a
            class="text-on-surface-variant hover:text-primary transition-colors duration-300"
            href="#"
            >Events</a
          >
          <!-- <a
            class="text-on-surface-variant hover:text-primary transition-colors duration-300"
            href="#"
            >QR Tracking</a
          > -->
          <a
            class="text-on-surface-variant hover:text-primary transition-colors duration-300"
            href="#"
            >Certificates</a
          >
          <!-- <a
            class="text-on-surface-variant hover:text-primary transition-colors duration-300"
            href="#"
            >Evaluations</a
          > -->
        </div>
        <div class="hidden md:flex gap-base items-center">
          <Link
            v-if="!page.props.auth?.user"
            class="text-body-md font-body-md text-on-surface-variant hover:text-primary transition-colors duration-300"
            :href="login().url"
            >Login</Link
          >
          <Link
            v-else
            class="text-body-md font-body-md text-on-surface-variant hover:text-primary transition-colors duration-300"
            :href="dashboardUrl"
            >Dashboard</Link
          >
        </div>
        <!-- Mobile Menu Toggle -->
        <div class="md:hidden">
          <span class="material-symbols-outlined text-primary cursor-pointer text-3xl"
            >menu</span
          >
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <header
      class="relative min-h-[90vh] flex items-center justify-center pt-20 pb-32 overflow-hidden"
    >
      <!-- Abstract Background -->
      <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-surface-dim/90 z-10"></div>
        <div
          class="absolute inset-0 bg-cover bg-center z-0 mix-blend-overlay opacity-30"
          style="
            background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDnAvVgd6dbED8-E0Z7ftyR8ljk65t055MwUF4QmrJH040Agal3Lla_YwK2Ek6mzHlvL9Yve6FaAgd9N8JqOTNfYfxbb1Tcw0BnFnzT3F9vtY9RkTPCeCryrHhjJ7HvajGU_WYvd3Oi-qSQO-D2__YDCUhrEUMw9hJjfqR7lhDmDcU-4YDIMVzPzALBhkwgJE0HIZcOtqB7DITm9tsNLV7Ut2W1vQ5rjyvea_EUZAf769hrdA437zJK');
          "
        ></div>
        <div
          class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/5 rounded-full blur-[100px] z-10 pointer-events-none"
        ></div>
        <div
          class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-secondary/5 rounded-full blur-[100px] z-10 pointer-events-none"
        ></div>
      </div>
      <div
        class="relative z-20 max-w-container-max mx-auto px-margin-desktop flex flex-col md:flex-row items-center gap-8 max-md:px-margin-mobile"
      >
        <div class="space-y-6">
          <h1
            class="text-display-lg font-display-lg text-on-surface max-w-4xl max-md:text-headline-lg-mobile max-md:font-headline-lg-mobile"
          >
            Digitizing <br /><span class="text-gradient-gold">Event Management</span>
          </h1>
          <p class="text-body-lg font-body-lg text-on-surface-variant max-w-2xl mx-auto">
            QUARTZ helps institutions digitize event registration, attendance monitoring,
            evaluations, and certificate distribution through an integrated, secure, and
            scalable platform.
          </p>
          <div class="mt-8 flex gap-6 max-md:flex-col">
            <button
              class="btn-primary px-8 py-4 rounded-lg font-label-sm text-label-sm uppercase tracking-widest flex items-center gap-2 justify-center"
            >
              Plan Your Event
              <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </button>
            <button
              class="px-8 py-4 rounded-lg font-label-sm text-label-sm uppercase tracking-widest border-[1.5px] border-outline text-on-surface hover:border-primary hover:text-primary transition-all duration-300 flex items-center gap-2 justify-center bg-surface-dim/50 backdrop-blur-sm"
            >
              <span class="material-symbols-outlined text-[18px]">play_circle</span>
              Watch Demo
            </button>
          </div>
        </div>
        <div>
          <img
            alt="Quartz Event Orchestration Logo"
            class="w-64 md:w-80 h-auto mb-8 drop-shadow-[0_0_30px_rgba(212,175,55,0.2)]"
            src="/images/quartzlogo.png"
          />
        </div>
      </div>
    </header>

    <!-- Features Bento Grid -->
    <section class="py-32 relative bg-surface-container-lowest">
      <div class="max-w-container-max mx-auto px-margin-desktop max-md:px-margin-mobile">
        <div class="mb-20 text-center">
          <h2
            class="text-headline-lg font-headline-lg text-on-surface mb-6 max-md:text-headline-md max-md:font-headline-md"
          >
            Orchestrate with <span class="text-primary-container">Authority</span>
          </h2>
          <p class="text-body-lg font-body-lg text-on-surface-variant max-w-2xl mx-auto">
            A seamless suite of tools designed for the modern event professional.
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
          <!-- Feature 1: QR Tracking (Large) -->
          <div
            class="glass-card rounded-xl p-10 md:col-span-8 flex flex-col md:flex-row gap-8 items-center group relative overflow-hidden transition-all duration-500 hover:border-primary/30"
          >
            <div
              class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-[80px] -mr-32 -mt-32 transition-all duration-500 group-hover:bg-primary/10"
            ></div>
            <div class="flex-1 z-10">
              <div
                class="w-12 h-12 rounded-lg bg-surface-container-high border border-outline-variant/30 flex items-center justify-center mb-6"
              >
                <span
                  class="material-symbols-outlined text-primary text-2xl"
                  style="font-variation-settings: 'FILL' 1"
                  >qr_code_scanner</span
                >
              </div>
              <h3 class="text-headline-md font-headline-md text-on-surface mb-4">
                QR Identification &amp; Tracking
              </h3>
              <p class="text-body-md font-body-md text-on-surface-variant mb-6">
                Provide attendees with secure, unique QR credentials. Enable seamless,
                contactless check-ins and monitor live attendance flows across multiple
                zones with our enterprise-grade scanning infrastructure.
              </p>
              <ul class="space-y-3 mb-8">
                <li class="flex items-center gap-3 text-body-md text-on-surface">
                  <span class="material-symbols-outlined text-primary text-sm"
                    >check_circle</span
                  >
                  Live Dashboard Updates
                </li>
                <li class="flex items-center gap-3 text-body-md text-on-surface">
                  <span class="material-symbols-outlined text-primary text-sm"
                    >check_circle</span
                  >
                  Multi-zone Access Control
                </li>
              </ul>
            </div>
            <div class="flex-1 w-full relative z-10">
              <img
                class="w-full h-auto rounded-lg shadow-[0_4px_24px_rgba(0,0,0,0.5)] border border-outline-variant/20 object-cover aspect-video"
                alt="A close-up of a sleek, modern smartphone held by a professional in a dark suit, scanning a glowing, metallic-styled QR code displayed on a high-end matte black tablet. The background is a blurred corporate lobby with warm gold and cool silver ambient lighting, reinforcing a premium, high-tech event registration setting."
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDnF9UTTw0I_9CWpJq8vOiCk7BYjwQVmZyGbIA26fLNqfftpN9fo_aJCo4pQRiKQwqbif10a-R2kUe89ij5dWyWpDFb9-9YeRi9wPiDG03vo-K3kEEGJ4qPYf8Kh9CzCeSye2sOVQHiAQzeUwRVMstFvZyzBmXyZfiVh-OwUhNTz9FpQ6pG6Rhbj4jYm9pRMJrkMleDEHzqKUTF58h55ZaIKKPhL5IM-u464-XZijkPBL-Bwi5VwCIR"
              />
            </div>
          </div>
          <!-- Feature 2: Automated Certificates (Small) -->
          <div
            class="glass-card rounded-xl p-8 md:col-span-4 flex flex-col justify-between group relative overflow-hidden transition-all duration-500 hover:border-primary/30"
          >
            <div
              class="absolute bottom-0 left-0 w-48 h-48 bg-secondary/10 rounded-full blur-[60px] -ml-24 -mb-24 transition-all duration-500 group-hover:bg-secondary/20"
            ></div>
            <div class="z-10">
              <div
                class="w-12 h-12 rounded-lg bg-surface-container-high border border-outline-variant/30 flex items-center justify-center mb-6"
              >
                <span
                  class="material-symbols-outlined text-secondary text-2xl"
                  style="font-variation-settings: 'FILL' 1"
                  >workspace_premium</span
                >
              </div>
              <h3 class="text-headline-md font-headline-md text-on-surface mb-4">
                Automated Certificates
              </h3>
              <p class="text-body-md font-body-md text-on-surface-variant mb-8">
                Instantly generate and distribute pristine, branded certificates of
                attendance or completion upon event conclusion. Elevate your event's
                lasting impression.
              </p>
            </div>
            <div
              class="relative w-full h-40 mt-auto rounded-lg overflow-hidden border border-outline-variant/20 z-10"
            >
              <img
                class="w-full h-full object-cover"
                alt="A beautifully designed, minimalist corporate certificate of completion displayed on a dark, glossy surface. The certificate features elegant serif typography and subtle silver foil accents. The lighting highlights the crisp edges of the paper against a moody, deep navy background, conveying exclusivity and professional achievement."
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuD5d4YV3QQ_NHDn-eRrAcASfMwRX-Ywf4gC1iAW3shPEWUDjdAsXVnwlmur4z3QMbpjhh03Xo35h8tASHUyOhhU7OAKwyzNdHAsUOe7qy-ySzuBx6icLp4GPSwzWQZ9zAI2S9QO9f4T-aqZ8E-PzehpyauYGK-ak_JHoPywhoRfzUpYAwRwDvNvwTpyYNx_0BZYPoCbL8RndS0yzFAovoTsc4jZIEFgmMUT-mz_E8ua5oISgSi5gM8c"
              />
            </div>
          </div>
          <!-- Feature 3: Analytics (Full Width) -->
          <div
            class="glass-card rounded-xl p-10 md:col-span-12 flex flex-col md:flex-row-reverse gap-12 items-center group relative overflow-hidden transition-all duration-500 hover:border-primary/30"
          >
            <div
              class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-primary-container/5 rounded-full blur-[120px] pointer-events-none transition-all duration-500 group-hover:bg-primary-container/10"
            ></div>
            <div class="flex-1 z-10">
              <div
                class="w-12 h-12 rounded-lg bg-surface-container-high border border-outline-variant/30 flex items-center justify-center mb-6"
              >
                <span
                  class="material-symbols-outlined text-primary-container text-2xl"
                  style="font-variation-settings: 'FILL' 1"
                  >monitoring</span
                >
              </div>
              <h3 class="text-headline-md font-headline-md text-on-surface mb-4">
                Post-Event Analytics
              </h3>
              <p class="text-body-md font-body-md text-on-surface-variant mb-6">
                Measure impact with precision. Our integrated evaluation builder captures
                attendee sentiment, while the analytics suite transforms raw check-in data
                into actionable insights for your next gathering.
              </p>
              <a
                class="inline-flex items-center gap-2 text-primary font-label-sm text-label-sm uppercase tracking-widest hover:text-primary-fixed transition-colors"
                href="#"
              >
                Explore Analytics
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
              </a>
            </div>
            <div class="flex-1 w-full relative z-10">
              <div
                class="bg-surface-dim rounded-lg border border-outline-variant/20 p-6 shadow-2xl relative overflow-hidden"
              >
                <!-- Abstract Chart UI -->
                <div class="flex justify-between items-end h-48 gap-4 mb-4">
                  <div
                    class="w-full bg-secondary-container/50 rounded-t-sm h-[40%] relative group"
                  >
                    <div
                      class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity"
                    ></div>
                  </div>
                  <div
                    class="w-full bg-secondary-container/50 rounded-t-sm h-[70%] relative group"
                  >
                    <div
                      class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity"
                    ></div>
                  </div>
                  <div
                    class="w-full bg-primary/80 rounded-t-sm h-[100%] relative shadow-[0_0_15px_rgba(212,175,55,0.4)]"
                  ></div>
                  <div
                    class="w-full bg-secondary-container/50 rounded-t-sm h-[85%] relative group"
                  >
                    <div
                      class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity"
                    ></div>
                  </div>
                  <div
                    class="w-full bg-secondary-container/50 rounded-t-sm h-[60%] relative group"
                  >
                    <div
                      class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity"
                    ></div>
                  </div>
                </div>
                <div
                  class="flex justify-between text-on-surface-variant text-label-sm font-label-sm"
                >
                  <span>Registration</span>
                  <span>Check-in</span>
                  <span>Keynote</span>
                  <span>Workshop</span>
                  <span>Networking</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-surface-dim border-t border-outline-variant/10">
      <div
        class="max-w-container-max mx-auto px-margin-desktop py-16 flex flex-col md:flex-row justify-between items-center gap-base max-md:px-margin-mobile"
      >
        <div
          class="text-headline-md font-headline-md text-primary font-bold tracking-widest"
        >
          QUARTZ
        </div>
        <div
          class="text-body-md font-body-md text-on-surface-variant text-center md:text-left"
        >
          © 2026 Quartz Event Management.
        </div>
        <div class="flex gap-6 text-label-sm font-label-sm">
          <a class="text-on-surface-variant hover:text-primary transition-colors" href="#"
            >Privacy Policy</a
          >
          <a class="text-on-surface-variant hover:text-primary transition-colors" href="#"
            >Terms of Service</a
          >
          <a class="text-on-surface-variant hover:text-primary transition-colors" href="#"
            >Contact Us</a
          >
          <a class="text-on-surface-variant hover:text-primary transition-colors" href="#"
            >Resources</a
          >
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.glass-card {
  background: linear-gradient(
    135deg,
    rgba(18, 27, 46, 0.8) 0%,
    rgba(14, 19, 30, 0.9) 100%
  );
  border: 1px solid rgba(194, 199, 205, 0.1);
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
  backdrop-filter: blur(16px);
}
.text-gradient-gold {
  background: linear-gradient(90deg, #d4af37 0%, #f2ca50 50%, #ffe088 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.btn-primary {
  background: linear-gradient(135deg, #d4af37 0%, #f2ca50 100%);
  color: #0e131e;
  transition: all 0.3s ease;
}
.btn-primary:hover {
  box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
  transform: translateY(-2px);
}
</style>
