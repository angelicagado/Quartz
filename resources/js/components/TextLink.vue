<script setup lang="ts">
import type { LinkComponentBaseProps, Method } from '@inertiajs/core';
import { Link } from '@inertiajs/vue3';
import { computed, useAttrs } from 'vue';
import { cn } from '@/lib/utils';

type Props = {
    href: LinkComponentBaseProps['href'];
    tabindex?: number;
    method?: Method;
    as?: string;
};

defineOptions({ inheritAttrs: false });
defineProps<Props>();

const attrs = useAttrs();
const linkClass = computed(() =>
    cn(
        'text-foreground decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500',
        attrs.class as string | undefined,
    ),
);
const restAttrs = computed(() => {
    const { class: _class, ...rest } = attrs;
    return rest;
});
</script>

<template>
    <Link
        :href="href"
        :tabindex="tabindex"
        :method="method"
        :as="as"
        :class="linkClass"
        v-bind="restAttrs"
    >
        <slot />
    </Link>
</template>
