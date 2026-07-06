<template>
    <div class="table-responsive rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
            <thead class="bg-slate-50 text-xs uppercase text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                <tr>
                    <th v-for="(h, i) in headers" :key="i" class="px-6 py-4 font-semibold">{{ h }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                <template v-if="data.length > 0">
                    <slot name="row" v-for="(row, i) in data" :key="i" :row="row" :index="i"></slot>
                </template>
                <template v-else>
                    <tr>
                        <td :colspan="headers.length" class="py-8 text-center text-slate-500">
                            No records found.
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    headers: string[];
    data: any[];
}>();
</script>
