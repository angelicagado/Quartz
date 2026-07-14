<template>
    <div class="flex h-full flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div>
            <div class="mb-4">
                <h3 class="font-serif text-lg font-bold text-slate-900 dark:text-slate-100">
                    Evaluation Form
                </h3>
            </div>
            
            <div class="mb-6">
                <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    {{ hasExistingForm 
                        ? `This event currently has an active evaluation form with ${form.questions.length} question(s). Attendees will be prompted to provide feedback.` 
                        : 'No evaluation form has been created for this event yet. Create one to gather attendee feedback after the event.' 
                    }}
                </p>
            </div>
        </div>
        
        <div class="flex items-center justify-end border-t border-slate-100 pt-4 dark:border-slate-800">
            <Dialog>
                <DialogTrigger as-child>
                    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white shadow-lg transition-all hover:bg-slate-800 focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200">
                        {{ hasExistingForm ? 'Manage Form' : 'Create Form' }}
                    </button>
                </DialogTrigger>
                
                <DialogScrollContent class="sm:max-w-3xl">
                    <DialogHeader>
                        <DialogTitle>Evaluation Form Builder</DialogTitle>
                        <DialogDescription>
                            Create questions to gather feedback from attendees after the event.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-6 py-4">
                        <!-- Form Details -->
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Form Title
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    placeholder="e.g., Event Feedback Survey"
                                    class="w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-slate-900 focus:border-[#d4af37] focus:ring-[#d4af37] dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-100"
                                />
                                <div v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Form Description (Optional)
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="2"
                                    placeholder="Thank you for attending! Please fill out this short survey."
                                    class="w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-slate-900 focus:border-[#d4af37] focus:ring-[#d4af37] dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-100"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Questions List -->
                        <div class="mt-8 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-bold text-slate-800 dark:text-slate-200">Questions</h4>
                            </div>
                            
                            <div v-if="form.questions.length === 0" class="rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                                    <ClipboardList class="h-6 w-6 text-slate-400" />
                                </div>
                                <h4 class="mt-4 font-medium text-slate-900 dark:text-slate-100">
                                    No questions added yet
                                </h4>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                    Get started by adding questions to your evaluation form to gather feedback from attendees.
                                </p>
                            </div>

                            <div
                                v-for="(question, index) in form.questions"
                                :key="index"
                                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all hover:border-[#d4af37]/30 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-200 group-hover:bg-[#d4af37] transition-colors dark:bg-slate-700"></div>
                                <div class="p-6 pl-8">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="flex-1 space-y-4">
                                            <!-- Question Text -->
                                            <div>
                                                <label class="mb-1 block text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-slate-400">
                                                    Question {{ index + 1 }}
                                                </label>
                                                <input
                                                    v-model="question.question_text"
                                                    type="text"
                                                    placeholder="Enter your question"
                                                    class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 font-medium text-slate-900 focus:border-[#d4af37] focus:ring-[#d4af37] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                                />
                                                <div v-if="form.errors[`questions.${index}.question_text`]" class="mt-1 text-sm text-red-500">
                                                    {{ form.errors[`questions.${index}.question_text`] }}
                                                </div>
                                            </div>
                                            
                                            <!-- Question Type -->
                                            <div class="w-full sm:w-48">
                                                <select
                                                    v-model="question.question_type"
                                                    @change="handleTypeChange(question)"
                                                    class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-[#d4af37] focus:ring-[#d4af37] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                                >
                                                    <option value="text">Text Response</option>
                                                    <option value="rating">1-5 Rating</option>
                                                    <option value="options">Multiple Choice</option>
                                                </select>
                                            </div>

                                            <!-- Options (if Multiple Choice) -->
                                            <div v-if="question.question_type === 'options'" class="space-y-3 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-slate-400">Options</label>
                                                <div v-for="(option, optIndex) in question.options" :key="optIndex" class="flex items-center gap-2">
                                                    <input
                                                        v-model="question.options[optIndex]"
                                                        type="text"
                                                        placeholder="Option text"
                                                        class="flex-1 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm focus:border-[#d4af37] focus:ring-[#d4af37] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                                    />
                                                    <button
                                                        @click="removeOption(question, optIndex)"
                                                        class="rounded-lg p-2 text-slate-400 hover:bg-slate-200 hover:text-red-500 dark:hover:bg-slate-700"
                                                        title="Remove Option"
                                                    >
                                                        <X class="h-4 w-4" />
                                                    </button>
                                                </div>
                                                <button
                                                    @click="addOption(question)"
                                                    class="inline-flex items-center gap-1 text-sm font-medium text-[#d4af37] hover:text-[#b8952b]"
                                                >
                                                    <Plus class="h-4 w-4" /> Add Option
                                                </button>
                                            </div>
                                            <div v-if="form.errors[`questions.${index}.options`]" class="mt-1 text-sm text-red-500">
                                                {{ form.errors[`questions.${index}.options`] }}
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Question -->
                                        <button
                                            @click="removeQuestion(index)"
                                            class="rounded-xl p-3 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500 sm:self-start dark:hover:bg-red-500/10"
                                            title="Remove Question"
                                        >
                                            <Trash2 class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Question Button -->
                            <button
                                @click="addQuestion"
                                class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-200 py-6 text-slate-500 transition-colors hover:border-[#d4af37]/50 hover:bg-[#d4af37]/5 hover:text-[#d4af37] dark:border-slate-700 dark:hover:border-[#d4af37]/50 dark:hover:bg-[#d4af37]/10"
                            >
                                <Plus class="h-6 w-6" />
                                <span class="font-bold">Add Question</span>
                            </button>
                        </div>
                    </div>

                    <DialogFooter>
                        <div class="flex w-full justify-between sm:justify-end gap-3">
                            <button
                                v-if="hasExistingForm"
                                @click="deleteForm"
                                class="rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-bold text-red-600 shadow-sm transition-all hover:bg-red-50 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-500/30 dark:bg-slate-900 dark:text-red-500 dark:hover:bg-red-500/10"
                            >
                                Delete Form
                            </button>
                            <button
                                @click="saveForm"
                                :disabled="form.processing"
                                class="rounded-xl bg-slate-900 px-6 py-2 text-sm font-bold text-white shadow-lg transition-all hover:bg-slate-800 focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 disabled:opacity-70 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200"
                            >
                                {{ form.processing ? 'Saving...' : (hasExistingForm ? 'Update Form' : 'Save Form') }}
                            </button>
                        </div>
                    </DialogFooter>
                </DialogScrollContent>
            </Dialog>
        </div>
    </div>
</template>

<script setup>
import { Plus, ClipboardList, Trash2, X } from '@lucide/vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { store as storeEvaluationRoute } from '@/routes/events/evaluations';
import {
    Dialog,
    DialogScrollContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

const props = defineProps({
    event: {
        type: Object,
        required: true
    },
});

const hasExistingForm = computed(() => !!props.event.evaluation_form);

const form = useForm({
    title: props.event.evaluation_form?.title || `${props.event.title} Evaluation`,
    description: props.event.evaluation_form?.description || '',
    questions: props.event.evaluation_form?.questions ? 
        props.event.evaluation_form.questions.map(q => ({
            question_text: q.question_text,
            question_type: q.question_type,
            options: q.options || []
        })) : 
        [
            { question_text: '', question_type: 'text', options: [] }
        ]
});

const addQuestion = () => {
    form.questions.push({
        question_text: '',
        question_type: 'text',
        options: []
    });
};

const removeQuestion = (index) => {
    form.questions.splice(index, 1);
};

const handleTypeChange = (question) => {
    if (question.question_type === 'options' && (!question.options || question.options.length === 0)) {
        question.options = ['', ''];
    }
};

const addOption = (question) => {
    if (!question.options) {
        question.options = [];
    }
    question.options.push('');
};

const removeOption = (question, index) => {
    question.options.splice(index, 1);
};

const saveForm = () => {
    // If the storeEvaluationRoute exists, use it. Otherwise, construct a common fallback.
    const url = typeof storeEvaluationRoute !== 'undefined' 
        ? storeEvaluationRoute.url(props.event.id) 
        : `/events/${props.event.id}/evaluations`;
        
    form.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            // Success handler
        }
    });
};

const deleteForm = () => {
    if (!confirm('Are you sure you want to delete this evaluation form? All questions will be permanently removed.')) {
        return;
    }
    
    // We assume a DELETE endpoint exists
    router.delete(`/events/${props.event.id}/evaluations`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.questions = [];
        }
    });
};
</script>
