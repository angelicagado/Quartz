import { ref, onMounted, onUnmounted } from 'vue';

const MOBILE_BREAKPOINT = 768;

export function useIsMobile() {
    const isMobile = ref(false);
    let mql: MediaQueryList | undefined;

    const onChange = (e: MediaQueryListEvent) => {
        isMobile.value = e.matches;
    };

    onMounted(() => {
        if (typeof window !== 'undefined') {
            mql = window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT - 1}px)`);
            isMobile.value = mql.matches;
            mql.addEventListener('change', onChange);
        }
    });

    onUnmounted(() => {
        if (mql) {
            mql.removeEventListener('change', onChange);
        }
    });

    return isMobile;
}
