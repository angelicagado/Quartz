<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard, login, register } from '@/routes';

const page = usePage();
const dashboardUrl = computed(() =>
    page.props.currentTeam ? dashboard(page.props.currentTeam.slug).url : '/',
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

    <div class="min-h-screen overflow-x-hidden font-body-md text-foreground bg-background antialiased selection:bg-primary/20 selection:text-primary">
        <!-- TopNavBar -->
        <nav class="sticky top-0 z-50 border-b border-border bg-white/80 shadow-sm backdrop-blur-xl">
            <div class="mx-auto flex h-20 max-w-container-max items-center justify-between px-margin-desktop max-md:px-margin-mobile">
                <div class="font-headline-md text-headline-md font-bold tracking-widest text-primary">
                    QUARTZ
                </div>
                <div class="hidden items-center gap-gutter font-body-md text-body-md md:flex">
                    <Link class="text-muted-foreground transition-colors duration-300 hover:text-primary" href="/events">Events</Link>
                    <a class="text-muted-foreground transition-colors duration-300 hover:text-primary" href="#">Certificates</a>
                </div>
                <div class="hidden items-center gap-base md:flex">
                    <Link
                        v-if="!page.props.auth?.user"
                        class="font-body-md text-body-md text-muted-foreground transition-colors duration-300 hover:text-primary"
                        :href="login().url"
                    >Login</Link>
                    <Link
                        v-else
                        class="font-body-md text-body-md text-muted-foreground transition-colors duration-300 hover:text-primary"
                        :href="dashboardUrl"
                    >Dashboard</Link>
                </div>
                <!-- Mobile Menu Toggle -->
                <div class="md:hidden">
                    <span class="material-symbols-outlined cursor-pointer text-3xl text-primary">menu</span>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="relative flex min-h-[90vh] items-center justify-center overflow-hidden pt-20 pb-32">
            <!-- Abstract Background -->
            <div class="absolute inset-0 z-0">
                <div class="absolute inset-0 z-10 bg-white/90"></div>
                <div
                    class="absolute inset-0 z-0 bg-cover bg-center opacity-10 mix-blend-overlay"
                    style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDnAvVgd6dbED8-E0Z7ftyR8ljk65t055MwUF4QmrJH040Agal3Lla_YwK2Ek6mzHlvL9Yve6FaAgd9N8JqOTNfYfxbb1Tcw0BnFnzT3F9vtY9RkTPCeCryrHhjJ7HvajGU_WYvd3Oi-qSQO-D2__YDCUhrEUMw9hJjfqR7lhDmDcU-4YDIMVzPzALBhkwgJE0HIZcOtqB7DITm9tsNLV7Ut2W1vQ5rjyvea_EUZAf769hrdA437zJK');"
                ></div>
                <div class="pointer-events-none absolute top-1/4 left-1/4 z-10 h-96 w-96 rounded-full bg-primary/20 blur-[120px]"></div>
                <div class="pointer-events-none absolute right-1/4 bottom-1/4 z-10 h-96 w-96 rounded-full bg-secondary/20 blur-[120px]"></div>
            </div>
            <div class="relative z-20 mx-auto flex max-w-container-max flex-col items-center gap-8 px-margin-desktop max-md:px-margin-mobile md:flex-row">
                <div class="space-y-6">
                    <h1 class="max-w-4xl font-display-lg text-display-lg text-foreground max-md:font-headline-lg-mobile max-md:text-headline-lg-mobile">
                        Digitizing <br /><span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Event Management</span>
                    </h1>
                    <p class="mx-auto max-w-2xl font-body-lg text-body-lg text-muted-foreground">
                        QUARTZ helps institutions digitize event registration, attendance monitoring, evaluations, and certificate distribution through an integrated, secure, and scalable platform.
                    </p>
                    <div class="mt-8 flex gap-6 max-md:flex-col">
                        <button class="flex items-center justify-center gap-2 rounded-lg bg-primary px-8 py-4 font-label-sm text-label-sm tracking-widest text-primary-foreground uppercase shadow-lg shadow-primary/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-primary/40 hover:bg-primary/90">
                            Plan Your Event
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </button>
                        <button class="flex items-center justify-center gap-2 rounded-lg border-[1.5px] border-border bg-white/50 px-8 py-4 font-label-sm text-label-sm tracking-widest text-foreground uppercase backdrop-blur-sm transition-all duration-300 hover:border-primary hover:text-primary">
                            <span class="material-symbols-outlined text-[18px]">play_circle</span>
                            Watch Demo
                        </button>
                    </div>
                </div>
                <div>
                    <img
                        alt="Quartz Event Orchestration Logo"
                        class="mb-8 h-auto w-64 md:w-80 drop-shadow-2xl"
                        src="/images/quartzlogo.png"
                    />
                </div>
            </div>
        </header>

        <!-- Features Bento Grid -->
        <section class="relative bg-slate-50 py-32">
            <div class="mx-auto max-w-container-max px-margin-desktop max-md:px-margin-mobile">
                <div class="mb-20 text-center">
                    <h2 class="mb-6 font-headline-lg text-headline-lg text-foreground max-md:font-headline-md max-md:text-headline-md">
                        Orchestrate with
                        <span class="text-accent">Authority</span>
                    </h2>
                    <p class="mx-auto max-w-2xl font-body-lg text-body-lg text-muted-foreground">
                        A seamless suite of tools designed for the modern event professional.
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-gutter md:grid-cols-12">
                    <!-- Feature 1: QR Tracking (Large) -->
                    <div class="group relative flex flex-col items-center gap-8 overflow-hidden rounded-2xl bg-white border border-border shadow-sm p-10 transition-all duration-500 hover:shadow-xl hover:border-primary/30 md:col-span-8 md:flex-row">
                        <div class="absolute top-0 right-0 -mt-32 -mr-32 h-64 w-64 rounded-full bg-primary/10 blur-[80px] transition-all duration-500 group-hover:bg-primary/20"></div>
                        <div class="z-10 flex-1">
                            <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg border border-primary/20 bg-primary/5">
                                <span class="material-symbols-outlined text-2xl text-primary" style="font-variation-settings: 'FILL' 1">qr_code_scanner</span>
                            </div>
                            <h3 class="mb-4 font-headline-md font-serif text-headline-md text-foreground">
                                QR Identification &amp; Tracking
                            </h3>
                            <p class="mb-6 font-body-md text-body-md text-muted-foreground">
                                Provide attendees with secure, unique QR credentials. Enable seamless, contactless check-ins and monitor live attendance flows across multiple zones with our enterprise-grade scanning infrastructure.
                            </p>
                            <ul class="mb-8 space-y-3">
                                <li class="flex items-center gap-3 text-body-md text-foreground">
                                    <span class="material-symbols-outlined text-sm text-primary">check_circle</span>
                                    Live Dashboard Updates
                                </li>
                                <li class="flex items-center gap-3 text-body-md text-foreground">
                                    <span class="material-symbols-outlined text-sm text-primary">check_circle</span>
                                    Multi-zone Access Control
                                </li>
                            </ul>
                        </div>
                        <div class="relative z-10 w-full flex-1">
                            <img
                                class="aspect-video h-auto w-full rounded-lg border border-border object-cover shadow-lg"
                                alt="QR Scanner"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDnF9UTTw0I_9CWpJq8vOiCk7BYjwQVmZyGbIA26fLNqfftpN9fo_aJCo4pQRiKQwqbif10a-R2kUe89ij5dWyWpDFb9-9YeRi9wPiDG03vo-K3kEEGJ4qPYf8Kh9CzCeSye2sOVQHiAQzeUwRVMstFvZyzBmXyZfiVh-OwUhNTz9FpQ6pG6Rhbj4jYm9pRMJrkMleDEHzqKUTF58h55ZaIKKPhL5IM-u464-XZijkPBL-Bwi5VwCIR"
                            />
                        </div>
                    </div>
                    <!-- Feature 2: Automated Certificates (Small) -->
                    <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl bg-white border border-border shadow-sm p-8 transition-all duration-500 hover:shadow-xl hover:border-secondary/30 md:col-span-4">
                        <div class="absolute bottom-0 left-0 -mb-24 -ml-24 h-48 w-48 rounded-full bg-secondary/10 blur-[60px] transition-all duration-500 group-hover:bg-secondary/20"></div>
                        <div class="z-10">
                            <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg border border-secondary/20 bg-secondary/5">
                                <span class="material-symbols-outlined text-2xl text-secondary" style="font-variation-settings: 'FILL' 1">workspace_premium</span>
                            </div>
                            <h3 class="mb-4 font-headline-md font-serif text-headline-md text-foreground">
                                Automated Certificates
                            </h3>
                            <p class="mb-8 font-body-md text-body-md text-muted-foreground">
                                Instantly generate and distribute pristine, branded certificates of attendance or completion upon event conclusion. Elevate your event's lasting impression.
                            </p>
                        </div>
                        <div class="relative z-10 mt-auto h-40 w-full overflow-hidden rounded-lg border border-border shadow-sm">
                            <img
                                class="h-full w-full object-cover"
                                alt="Certificate"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuD5d4YV3QQ_NHDn-eRrAcASfMwRX-Ywf4gC1iAW3shPEWUDjdAsXVnwlmur4z3QMbpjhh03Xo35h8tASHUyOhhU7OAKwyzNdHAsUOe7qy-ySzuBx6icLp4GPSwzWQZ9zAI2S9QO9f4T-aqZ8E-PzehpyauYGK-ak_JHoPywhoRfzUpYAwRwDvNvwTpyYNx_0BZYPoCbL8RndS0yzFAovoTsc4jZIEFgmMUT-mz_E8ua5oISgSi5gM8c"
                            />
                        </div>
                    </div>
                    <!-- Feature 3: Analytics (Full Width) -->
                    <div class="group relative flex flex-col items-center gap-12 overflow-hidden rounded-2xl bg-white border border-border shadow-sm p-10 transition-all duration-500 hover:shadow-xl hover:border-accent/30 md:col-span-12 md:flex-row-reverse">
                        <div class="pointer-events-none absolute top-1/2 left-1/2 h-[800px] w-[800px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-accent/5 blur-[120px] transition-all duration-500 group-hover:bg-accent/10"></div>
                        <div class="z-10 flex-1">
                            <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg border border-accent/20 bg-accent/5">
                                <span class="material-symbols-outlined text-2xl text-accent" style="font-variation-settings: 'FILL' 1">monitoring</span>
                            </div>
                            <h3 class="mb-4 font-headline-md font-serif text-headline-md text-foreground">
                                Post-Event Analytics
                            </h3>
                            <p class="mb-6 font-body-md text-body-md text-muted-foreground">
                                Measure impact with precision. Our integrated evaluation builder captures attendee sentiment, while the analytics suite transforms raw check-in data into actionable insights for your next gathering.
                            </p>
                            <a class="inline-flex items-center gap-2 font-label-sm text-label-sm tracking-widest text-accent uppercase transition-colors hover:text-accent/80" href="#">
                                Explore Analytics
                                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                            </a>
                        </div>
                        <div class="relative z-10 w-full flex-1">
                            <div class="relative overflow-hidden rounded-xl border border-border bg-slate-50 p-6 shadow-md">
                                <!-- Abstract Chart UI -->
                                <div class="mb-4 flex h-48 items-end justify-between gap-4">
                                    <div class="group relative h-[40%] w-full rounded-t-sm bg-secondary/20">
                                        <div class="absolute inset-0 bg-secondary/40 opacity-0 transition-opacity group-hover:opacity-100"></div>
                                    </div>
                                    <div class="group relative h-[70%] w-full rounded-t-sm bg-primary/20">
                                        <div class="absolute inset-0 bg-primary/40 opacity-0 transition-opacity group-hover:opacity-100"></div>
                                    </div>
                                    <div class="relative h-[100%] w-full rounded-t-sm bg-accent shadow-[0_0_15px_rgba(245,158,11,0.4)]"></div>
                                    <div class="group relative h-[85%] w-full rounded-t-sm bg-primary/20">
                                        <div class="absolute inset-0 bg-primary/40 opacity-0 transition-opacity group-hover:opacity-100"></div>
                                    </div>
                                    <div class="group relative h-[60%] w-full rounded-t-sm bg-secondary/20">
                                        <div class="absolute inset-0 bg-secondary/40 opacity-0 transition-opacity group-hover:opacity-100"></div>
                                    </div>
                                </div>
                                <div class="flex justify-between font-label-sm text-label-sm text-muted-foreground">
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
        <footer class="border-t border-border bg-white">
            <div class="mx-auto flex max-w-container-max flex-col items-center justify-between gap-base px-margin-desktop py-16 max-md:px-margin-mobile md:flex-row">
                <div class="font-headline-md text-headline-md font-bold tracking-widest text-primary">
                    QUARTZ
                </div>
                <div class="text-center font-body-md text-body-md text-muted-foreground md:text-left">
                    © 2026 Quartz Event Management.
                </div>
                <div class="flex gap-6 font-label-sm text-label-sm">
                    <a class="text-muted-foreground transition-colors hover:text-primary" href="#">Privacy Policy</a>
                    <a class="text-muted-foreground transition-colors hover:text-primary" href="#">Terms of Service</a>
                    <a class="text-muted-foreground transition-colors hover:text-primary" href="#">Contact Us</a>
                    <a class="text-muted-foreground transition-colors hover:text-primary" href="#">Resources</a>
                </div>
            </div>
        </footer>
    </div>
</template>
