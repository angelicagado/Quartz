<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Award, CalendarDays, CheckCircle2, ChevronLeft, Star } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

interface QuestionOption {
    value: string;
    label: string;
}

interface Question {
    id: number | string;
    label: string;
    type: 'text' | 'rating' | 'options';
    required?: boolean;
    options?: QuestionOption[];
}

interface EvaluationForm {
    id: number;
    title: string;
    questions: Question[];
}

interface Event {
    id: number;
    title: string;
    start_time: string;
    end_time: string;
}

const props = defineProps<{
    event: Event;
    form: EvaluationForm;
    alreadySubmitted: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My Events', href: '/portal/events' },
            { title: 'Evaluation', href: '#' },
        ],
    },
});

// Build initial answers from questions
function buildInitialAnswers(): Record<string | number, string | number> {
    const answers: Record<string | number, string | number> = {};
    props.form?.questions?.forEach((q) => {
        answers[q.id] = q.type === 'rating' ? 0 : '';
    });
    return answers;
}

const evaluationForm = useForm({
    answers: buildInitialAnswers(),
});

function submitEvaluation() {
    evaluationForm.post(`/portal/events/${props.event.id}/evaluation`, {
        preserveScroll: true,
    });
}

// Star rating state
const hoverRating = ref<Record<string | number, number>>({});

function setRating(questionId: string | number, rating: number) {
    evaluationForm.answers[questionId] = rating;
}

function getRating(questionId: string | number): number {
    return Number(evaluationForm.answers[questionId]) || 0;
}

function setHover(questionId: string | number, rating: number) {
    hoverRating.value[questionId] = rating;
}

function clearHover(questionId: string | number) {
    delete hoverRating.value[questionId];
}

function getDisplayRating(questionId: string | number): number {
    return hoverRating.value[questionId] ?? getRating(questionId);
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'long', day: 'numeric', year: 'numeric',
    });
}
</script>

<template>
    <Head :title="`Evaluation — ${event.title}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center gap-4">
            <Link href="/portal/events">
                <Button variant="ghost" size="icon-sm">
                    <ChevronLeft class="size-4" />
                </Button>
            </Link>
            <div>
                <h1 class="font-serif text-2xl font-bold tracking-tight text-foreground">Event Evaluation</h1>
                <p class="mt-0.5 text-sm text-muted-foreground">{{ event.title }}</p>
            </div>
        </div>

        <div class="max-w-2xl w-full mx-auto">
            <!-- Event info banner -->
            <div class="rounded-xl bg-gradient-to-r from-violet-50 to-indigo-50 dark:from-violet-900/20 dark:to-indigo-900/20 border border-violet-200 dark:border-violet-800 p-4 mb-6 flex items-center gap-3">
                <CalendarDays class="size-5 text-violet-600 dark:text-violet-400 shrink-0" />
                <div>
                    <p class="font-medium text-foreground text-sm">{{ event.title }}</p>
                    <p class="text-xs text-muted-foreground">{{ formatDate(event.start_time) }}</p>
                </div>
            </div>

            <!-- Already Submitted State -->
            <div v-if="alreadySubmitted" class="flex flex-col items-center gap-6 py-12 text-center">
                <div class="relative">
                    <div class="flex size-24 items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-teal-500 shadow-lg shadow-green-200 dark:shadow-green-900/30">
                        <CheckCircle2 class="size-12 text-white" />
                    </div>
                    <div class="absolute -right-1 -top-1 flex size-8 items-center justify-center rounded-full bg-amber-400 shadow-sm">
                        <Award class="size-4 text-white" />
                    </div>
                </div>
                <div>
                    <h2 class="font-serif text-2xl font-bold text-foreground">Thank You!</h2>
                    <p class="mt-2 text-muted-foreground leading-relaxed max-w-sm">
                        Your evaluation has been submitted successfully. We appreciate your feedback!
                    </p>
                </div>
                <Link href="/portal/events">
                    <Button variant="outline">Back to Events</Button>
                </Link>
            </div>

            <!-- Evaluation Form -->
            <div v-else>
                <div class="rounded-xl border border-border bg-card shadow-xs overflow-hidden">
                    <div class="border-b border-border bg-muted/30 px-6 py-4">
                        <h2 class="font-serif font-semibold text-foreground">{{ form.title }}</h2>
                        <p class="text-xs text-muted-foreground mt-0.5">{{ form.questions.length }} question{{ form.questions.length !== 1 ? 's' : '' }}</p>
                    </div>

                    <form @submit.prevent="submitEvaluation" class="p-6">
                        <div class="space-y-8">
                            <div
                                v-for="(question, index) in form.questions"
                                :key="question.id"
                                class="group"
                            >
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 text-xs font-bold text-white">
                                        {{ index + 1 }}
                                    </span>
                                    <label :for="`q-${question.id}`" class="text-sm font-medium text-foreground leading-relaxed">
                                        {{ question.label }}
                                        <span v-if="question.required" class="ml-1 text-destructive">*</span>
                                    </label>
                                </div>

                                <!-- Text -->
                                <div v-if="question.type === 'text'" class="ml-9">
                                    <textarea
                                        :id="`q-${question.id}`"
                                        v-model="evaluationForm.answers[question.id]"
                                        rows="4"
                                        placeholder="Share your thoughts..."
                                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:border-ring transition-colors resize-none"
                                    />
                                </div>

                                <!-- Rating (5 Stars) -->
                                <div v-else-if="question.type === 'rating'" class="ml-9">
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            v-for="star in 5"
                                            :key="star"
                                            type="button"
                                            class="transition-transform hover:scale-110 focus:outline-none"
                                            @click="setRating(question.id, star)"
                                            @mouseenter="setHover(question.id, star)"
                                            @mouseleave="clearHover(question.id)"
                                        >
                                            <Star
                                                :class="[
                                                    'size-8 transition-colors duration-150',
                                                    star <= getDisplayRating(question.id)
                                                        ? 'fill-amber-400 text-amber-400'
                                                        : 'fill-transparent text-muted-foreground/30 hover:text-amber-300'
                                                ]"
                                            />
                                        </button>
                                        <span class="ml-2 text-sm text-muted-foreground">
                                            {{ getRating(question.id) > 0 ? `${getRating(question.id)} / 5` : 'Click to rate' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Options (Radio) -->
                                <div v-else-if="question.type === 'options'" class="ml-9 space-y-2.5">
                                    <label
                                        v-for="option in question.options"
                                        :key="option.value"
                                        :class="[
                                            'flex cursor-pointer items-center gap-3 rounded-lg border p-3.5 text-sm transition-all',
                                            evaluationForm.answers[question.id] === option.value
                                                ? 'border-violet-500 bg-violet-50 dark:bg-violet-900/20 font-medium text-foreground'
                                                : 'border-border hover:border-muted-foreground/30 hover:bg-muted/20 text-muted-foreground'
                                        ]"
                                    >
                                        <input
                                            type="radio"
                                            :name="`q-${question.id}`"
                                            :value="option.value"
                                            v-model="evaluationForm.answers[question.id]"
                                            class="accent-violet-600"
                                        />
                                        {{ option.label }}
                                    </label>
                                </div>

                                <!-- Error -->
                                <p
                                    v-if="(evaluationForm.errors as Record<string, string>)[`answers.${question.id}`]"
                                    class="mt-1 ml-9 text-xs text-destructive"
                                >
                                    {{ (evaluationForm.errors as Record<string, string>)[`answers.${question.id}`] }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-8 flex items-center justify-between border-t border-border pt-6">
                            <Link href="/portal/events">
                                <Button variant="ghost">Cancel</Button>
                            </Link>
                            <Button
                                type="submit"
                                :disabled="evaluationForm.processing"
                                class="bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white shadow-md min-w-[140px]"
                            >
                                {{ evaluationForm.processing ? 'Submitting...' : 'Submit Evaluation' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
