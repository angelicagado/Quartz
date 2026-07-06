<template>
  <AdminLayout>
    <div class="mx-auto w-full max-w-7xl pb-10 font-['Outfit']">
      <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Reports & Analytics
          </h1>
          <p class="mt-1 text-slate-500 dark:text-slate-400">
            View attendance and evaluation insights.
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="flex h-64 items-center justify-center rounded-3xl border border-slate-100 bg-slate-50 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/50">
          <h4 class="text-slate-400 font-medium">
            [ Attendance Chart Placeholder ]
          </h4>
        </div>
        <div class="flex h-64 items-center justify-center rounded-3xl border border-slate-100 bg-slate-50 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/50">
          <h4 class="text-slate-400 font-medium">
            [ Evaluation Chart Placeholder ]
          </h4>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
</script>
