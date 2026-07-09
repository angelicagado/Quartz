<template>
    <div class="mx-auto w-full space-y-8">
        <div class="group relative">
            <div
                class="relative flex aspect-video flex-col items-center justify-center overflow-hidden rounded-[2rem] border-2 border-slate-700 bg-slate-900 shadow-inner transition-all group-hover:border-[#d4af37]/30 sm:aspect-[16/9]"
            >
                <div
                    id="qr-reader"
                    class="absolute inset-0 h-full w-full [&>video]:object-cover"
                    v-show="mode === 'camera'"
                ></div>

                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-black/0 via-black/20 to-black/60"
                ></div>

                <template v-if="!lastScan && !error">
                    <div
                        class="absolute top-12 left-12 h-12 w-12 rounded-tl-xl border-t-2 border-l-2 border-[#d4af37] opacity-30"
                    ></div>
                    <div
                        class="absolute top-12 right-12 h-12 w-12 rounded-tr-xl border-t-2 border-r-2 border-[#d4af37] opacity-30"
                    ></div>
                    <div
                        class="absolute bottom-12 left-12 h-12 w-12 rounded-bl-xl border-b-2 border-l-2 border-[#d4af37] opacity-30"
                    ></div>
                    <div
                        class="absolute right-12 bottom-12 h-12 w-12 rounded-br-xl border-r-2 border-b-2 border-[#d4af37] opacity-30"
                    ></div>
                </template>

                <div
                    v-if="isScanning && mode === 'camera'"
                    class="absolute inset-0 flex items-center justify-center bg-[#d4af37]/5"
                >
                    <div
                        class="absolute top-0 h-0.5 w-full animate-[scan_2s_ease-in-out_infinite] bg-[#d4af37] shadow-[0_0_15px_#d4af37]"
                    ></div>
                </div>

                <div
                    class="relative z-10 flex flex-col items-center justify-center p-8"
                >
                    <div
                        v-if="lastScan"
                        class="flex animate-in flex-col items-center space-y-4 duration-300 fade-in zoom-in"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-500/20 shadow-[0_0_30px_-5px_rgb(16,185,129)]"
                        >
                            <CheckCircle class="h-10 w-10 text-emerald-400" />
                        </div>
                        <div class="text-center">
                            <p
                                class="text-xl font-bold tracking-tight text-white"
                            >
                                Scan Success
                            </p>
                            <p
                                class="mt-1 font-mono text-xs tracking-tighter text-emerald-400 opacity-70"
                            >
                                {{ lastScan }}
                            </p>
                        </div>
                    </div>
                    <div
                        v-else-if="error"
                        class="shake flex animate-in flex-col items-center space-y-4 italic"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full bg-rose-500/10"
                        >
                            <AlertCircle class="h-10 w-10 text-rose-500" />
                        </div>
                        <p
                            class="px-6 text-center text-sm font-medium text-rose-400"
                        >
                            {{ error }}
                        </p>
                        <button
                            @click="error = null"
                            class="rounded-full border border-white/20 bg-white/5 px-4 py-2 text-[10px] tracking-widest text-white uppercase transition-colors hover:bg-white/10"
                        >
                            Try Again
                        </button>
                    </div>
                    <div
                        v-else-if="isScanning && mode === 'file'"
                        class="flex flex-col items-center space-y-6"
                    >
                        <div
                            class="flex h-20 w-20 animate-spin items-center justify-center rounded-full bg-[#d4af37]/20"
                        >
                            <ScanLine class="h-10 w-10 text-[#d4af37]" />
                        </div>
                        <div class="text-center">
                            <p class="font-bold tracking-wide text-white">
                                Processing Image...
                            </p>
                            <p class="mt-1 text-xs font-light text-slate-500">
                                Decoding QR code data
                            </p>
                        </div>
                    </div>
                    <label
                        v-else-if="mode === 'file'"
                        class="group/upload relative flex cursor-pointer flex-col items-center space-y-6 p-12"
                    >
                        <input
                            type="file"
                            class="absolute inset-0 z-20 h-full w-full cursor-pointer opacity-0"
                            accept="image/*"
                            @change="handleFileChange"
                            ref="fileInputRef"
                        />
                        <div
                            class="flex h-24 w-24 items-center justify-center rounded-3xl border-2 border-dashed border-slate-700 bg-slate-800 transition-all group-hover/upload:border-[#d4af37] group-hover/upload:bg-slate-800/80"
                        >
                            <ImageIcon
                                class="h-10 w-10 text-slate-500 group-hover/upload:text-[#d4af37]"
                            />
                        </div>
                        <div class="text-center">
                            <p class="font-bold tracking-wide text-white">
                                Upload QR Image
                            </p>
                            <p class="mt-1 text-xs font-light text-slate-500">
                                PNG, JPG, or Screenshots
                            </p>
                        </div>
                    </label>
                    <div
                        v-else-if="!isScanning"
                        class="flex flex-col items-center space-y-4"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-800/50 text-slate-600"
                        >
                            <ScanLine class="h-10 w-10" />
                        </div>
                        <p class="text-sm font-light text-slate-500">
                            Camera Standby
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="flex items-start gap-4 rounded-2xl border border-slate-800 bg-slate-900/50 p-4"
        >
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-700 bg-slate-800"
            >
                <AlertCircle class="h-5 w-5 text-slate-500" />
            </div>
            <div>
                <h5 class="text-sm font-bold text-white">Scanning Protocol</h5>
                <p
                    class="mt-1 text-xs leading-relaxed font-light text-slate-500"
                >
                    Use the camera for real-time check-ins. If the camera is
                    unavailable on your device, use the upload feature to
                    process QR screenshots. Select the active event first.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onUnmounted, onMounted, nextTick } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';
import {
    ScanLine,
    CheckCircle,
    AlertCircle,
    Image as ImageIcon,
} from '@lucide/vue';

const props = defineProps<{
    mode: 'camera' | 'file';
}>();

const emit = defineEmits<{
    (e: 'scan', token: string): void;
    (e: 'update:isScanning', value: boolean): void;
}>();

const isScanning = ref(false);
const lastScan = ref<string | null>(null);
const error = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

let scanner: Html5Qrcode | null = null;

watch(isScanning, (val) => {
    emit('update:isScanning', val);
});

const cleanupScanner = async () => {
    if (scanner?.isScanning) {
        try {
            await scanner.stop();
        } catch (err) {
            console.error(err);
        }
    }
    if (scanner) {
        scanner.clear();
        scanner = null;
    }
};

const setupScanner = async () => {
    if (typeof window === 'undefined') return;
    await cleanupScanner();
    if (props.mode === 'camera') {
        await nextTick();
        scanner = new Html5Qrcode('qr-reader');
    }
};

watch(() => props.mode, async (newMode) => {
    isScanning.value = false;
    error.value = null;
    await setupScanner();
});

onMounted(setupScanner);

onUnmounted(async () => {
    await cleanupScanner();
});

const startCamera = async () => {
    if (!scanner) return;
    isScanning.value = true;
    error.value = null;
    try {
        await scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText) => {
                handleSuccess(decodedText);
                scanner?.stop().then(() => {
                    isScanning.value = false;
                });
            },
            () => {},
        );
    } catch (err) {
        error.value = 'Could not access camera. Please check permissions.';
        isScanning.value = false;
    }
};

const triggerFileUpload = () => {
    if (fileInputRef.value) {
        fileInputRef.value.click();
    }
};

const handleFileChange = async (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    isScanning.value = true;
    error.value = null;
    lastScan.value = null;

    const tempId = 'qr-file-reader-temp';
    let tempElem = document.getElementById(tempId);
    if (!tempElem) {
        tempElem = document.createElement('div');
        tempElem.id = tempId;
        Object.assign(tempElem.style, {
            position: 'absolute',
            top: '-9999px',
            left: '-9999px',
            width: '100px',
            height: '100px',
            overflow: 'hidden',
        });
        document.body.appendChild(tempElem);
    }

    try {
        const fileScanner = new Html5Qrcode(tempId);
        const decodedText = await fileScanner.scanFile(file, true);

        handleSuccess(decodedText);
        input.value = '';
        fileScanner.clear();
    } catch (err: any) {
        console.error('QR Scan Error:', err);
        const errorMsg = err?.toString() || '';
        if (errorMsg.includes('No barcode or QR code detected')) {
            error.value =
                'No QR code found. Try taking a clearer screenshot or zooming in on the QR code.';
        } else {
            error.value =
                "Could not process this image. Please ensure it's a valid QR code image.";
        }
    } finally {
        isScanning.value = false;
    }
};

const handleSuccess = (token: string) => {
    if (navigator.vibrate) navigator.vibrate(200);
    lastScan.value = token;
    emit('scan', token);
    setTimeout(() => {
        lastScan.value = null;
    }, 3000);
};

defineExpose({
    startCamera,
    triggerFileUpload,
});
</script>

<style scoped>
@keyframes scan {
    0% {
        top: 0;
    }
    100% {
        top: 100%;
    }
}
.animate-\[scan_2s_ease-in-out_infinite\] {
    animation: scan 2s ease-in-out infinite;
}
.shake {
    animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
}
@keyframes shake {
    10%,
    90% {
        transform: translate3d(-1px, 0, 0);
    }
    20%,
    80% {
        transform: translate3d(2px, 0, 0);
    }
    30%,
    50%,
    70% {
        transform: translate3d(-4px, 0, 0);
    }
    40%,
    60% {
        transform: translate3d(4px, 0, 0);
    }
}
</style>
