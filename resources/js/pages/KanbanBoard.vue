<template>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Add Task Form -->
                <div class="mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <form @submit.prevent="addTask" class="flex gap-3">
                                <input
                                    v-model="newTask"
                                    type="text"
                                    placeholder="What needs to be done?"
                                    class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-4"
                                    required
                                >
                                <!-- <select
                                    v-model="selectedListId"
                                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option :value="null">No list</option>
                                    <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                                </select> -->
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                                >
                                    Add Task
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Kanban Board -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Not Started Column -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-gray-400 rounded-full mr-2"></span>
                            Not Started Yet
                            <span class="ml-auto text-sm text-gray-500">({{ tasks.notStarted.length }})</span>
                        </h3>
                        <VueDraggable
                            v-model="tasks.notStarted"
                            :animation="150"
                            group="tasks"
                            class="space-y-3 min-h-[200px]"
                            @add="onAdd($event, 'not start yet', tasks.notStarted)"
                        >
                            <div
                                v-for="task in tasks.notStarted"
                                :key="task.id"
                                class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 cursor-move hover:shadow-md transition-shadow"
                            >
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">{{ task.title }}</h4>
                                <span
                                    v-if="task.list_id"
                                    class="inline-block mb-2 text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300"
                                >
                                    {{ lists.find(l => l.id === task.list_id)?.name }}
                                </span>
                                <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                                    <span>{{ formatDate(task.created_at) }}</span>
                                    <button
                                        @click="deleteTask(task.id)"
                                        class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                        title="Delete task"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </VueDraggable>
                    </div>

                    <!-- Pending Column -->
                    <div class="bg-yellow-50 dark:bg-gray-800 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-yellow-400 rounded-full mr-2"></span>
                            Pending
                            <span class="ml-auto text-sm text-gray-500">({{ tasks.pending.length }})</span>
                        </h3>
                        <VueDraggable
                            v-model="tasks.pending"
                            :animation="150"
                            group="tasks"
                            class="space-y-3 min-h-[200px]"
                            @add="onAdd($event, 'pending', tasks.pending)"
                        >
                            <div
                                v-for="task in tasks.pending"
                                :key="task.id"
                                class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 cursor-move hover:shadow-md transition-shadow"
                            >
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">{{ task.title }}</h4>
                                <span
                                    v-if="task.list_id"
                                    class="inline-block mb-2 text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300"
                                >
                                    {{ lists.find(l => l.id === task.list_id)?.name }}
                                </span>
                                <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                                    <span>{{ formatDate(task.created_at) }}</span>
                                    <button
                                        @click="deleteTask(task.id)"
                                        class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                        title="Delete task"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </VueDraggable>
                    </div>

                    <!-- Completed Column -->
                    <div class="bg-green-50 dark:bg-gray-800 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-green-400 rounded-full mr-2"></span>
                            Completed
                            <span class="ml-auto text-sm text-gray-500">({{ tasks.completed.length }})</span>
                        </h3>
                        <VueDraggable
                            v-model="tasks.completed"
                            :animation="150"
                            group="tasks"
                            class="space-y-3 min-h-[200px]"
                            @add="onAdd($event, 'completed', tasks.completed)"
                        >
                            <div
                                v-for="task in tasks.completed"
                                :key="task.id"
                                class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 cursor-move hover:shadow-md transition-shadow"
                            >
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 line-through mb-1">{{ task.title }}</h4>
                                <span
                                    v-if="task.list_id"
                                    class="inline-block mb-2 text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300"
                                >
                                    {{ lists.find(l => l.id === task.list_id)?.name }}
                                </span>
                                <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                                    <span>{{ formatDate(task.created_at) }}</span>
                                    <button
                                        @click="deleteTask(task.id)"
                                        class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                        title="Delete task"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </VueDraggable>
                    </div>
                </div>
            </div>
        </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';

interface Task {
    id: number;
    title: string;
    is_completed: boolean;
    status: string;
    user_id: number;
    list_id: number | null;
    created_at: string;
    updated_at: string;
}

interface TodoList {
    id: number;
    name: string;
    user_id: number;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{ todos: Task[]; lists: TodoList[] }>();
const emit = defineEmits<{ refetch: [] }>();

const newTask = ref('');
const selectedListId = ref<number | null>(null);
const tasks = ref({
    notStarted: [] as Task[],
    pending: [] as Task[],
    completed: [] as Task[]
});

watch(() => props.todos, (newTodos) => {
    tasks.value = {
        notStarted: newTodos.filter(t => t.status === 'not start yet'),
        pending: newTodos.filter(t => t.status === 'pending'),
        completed: newTodos.filter(t => t.status === 'completed'),
    };
}, { immediate: true });

const addTask = async () => {
    if (!newTask.value.trim()) return;

    try {
        const response = await fetch('/tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                title: newTask.value.trim(),
                is_completed: false,
                status: 'not start yet',
                list_id: selectedListId.value,
            }),
        });

        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
            newTask.value = '';
            emit('refetch');
        } else {
            console.error('Failed to add task:', data);
        }
    } catch (error) {
        console.error('Failed to add task:', error);
    }
};

const deleteTask = async (id: number) => {
    try {
        const response = await fetch(`/tasks/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });

        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
            emit('refetch');
        } else {
            console.error('Failed to delete task:', data);
        }
    } catch (error) {
        console.error('Failed to delete task:', error);
    }
};

const onAdd = async (event: any, targetStatus: string, targetArray: Task[]) => {
    const task = targetArray[event.newIndex];
    if (!task || task.status === targetStatus) return;

    try {
        const response = await fetch(`/tasks/${task.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                title: task.title,
                is_completed: targetStatus === 'completed',
                status: targetStatus,
            }),
        });

        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();
        if (data.success) {
            task.status = targetStatus;
            task.is_completed = targetStatus === 'completed';
            emit('refetch');
        } else {
            emit('refetch');
        }
    } catch (error) {
        console.error('Failed to update task status:', error);
        emit('refetch');
    }
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

</script>
