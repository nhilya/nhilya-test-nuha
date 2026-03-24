<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KanbanBoard from '@/pages/KanbanBoard.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted, onErrorCaptured } from 'vue';

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

const newTodo = ref('');
const todos = ref<Task[]>([]);
const lists = ref<TodoList[]>([]);
const filter = ref<'all' | 'active' | 'completed'>('all');
const loading = ref(false);
const page = usePage();

const viewMode = ref<'list' | 'kanban'>('list');

watch(viewMode, (newMode) => {
    console.log('🔄 View mode changed to:', newMode);
    if (typeof window !== 'undefined') {
        localStorage.setItem('viewMode', newMode);
    }
});

const selectedListId = ref<number | null>(null);

const form = useForm({
    title: '',
    is_completed: false,
    status: 'not start yet',
    user_id: 0,
    list_id: null as number | null,
});

const filteredTodos = computed(() => {
    switch (filter.value) {
        case 'active':
            return todos.value.filter(todo => !todo.is_completed);
        case 'completed':
            return todos.value.filter(todo => todo.is_completed);
        default:
            return todos.value;
    }
});

const activeCount = computed(() => todos.value.filter(todo => !todo.is_completed).length);
const completedCount = computed(() => todos.value.filter(todo => todo.is_completed).length);

// Fetch lists from API
const fetchLists = async () => {
    try {
        const response = await fetch('/lists', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                lists.value = data.lists;
            }
        }
    } catch (error) {
        console.error('Failed to fetch lists:', error);
    }
};

// Fetch tasks from API
const fetchTasks = async () => {
    console.log('📥 Fetching tasks...');
    loading.value = true;
    try {
        const response = await fetch('/tasks', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });

        if (response.status === 401) {
            console.log('❌ Unauthorized - redirecting to login');
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('✅ Tasks fetched successfully:', { count: data.tasks?.length, tasks: data.tasks });
        if (data.success) {
            todos.value = data.tasks;
        } else {
            console.error('API returned error:', data);
        }
    } catch (error) {
        console.error('❌ Failed to fetch tasks:', error);
        todos.value = [];
    } finally {
        loading.value = false;
    }
};

const addTodo = async () => {
    if (!newTodo.value.trim()) return;

    form.title = newTodo.value.trim();
    form.is_completed = false;
    form.status = 'not start yet';

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
                title: form.title,
                is_completed: form.is_completed,
                status: form.status,
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
            todos.value.unshift(data.task);
            newTodo.value = '';
        } else {
            console.error('Failed to add task:', data);
        }
    } catch (error) {
        console.error('Failed to add task:', error);
    }
};

const toggleTodo = async (id: number) => {
    const todo = todos.value.find(t => t.id === id);
    if (!todo) return;

    const newStatus = !todo.is_completed;

    try {
        const response = await fetch(`/tasks/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                title: todo.title,
                is_completed: newStatus,
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
            todo.is_completed = newStatus;
        } else {
            console.error('Failed to update task:', data);
        }
    } catch (error) {
        console.error('Failed to update task:', error);
    }
};

const deleteTodo = async (id: number) => {
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
            todos.value = todos.value.filter(todo => todo.id !== id);
        } else {
            console.error('Failed to delete task:', data);
        }
    } catch (error) {
        console.error('Failed to delete task:', error);
    }
};

const updateTaskStatus = async (id: number, newStatus: string) => {
    const todo = todos.value.find(t => t.id === id);
    if (!todo) return;

    try {
        const response = await fetch(`/tasks/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                title: todo.title,
                is_completed: newStatus === 'completed',
                status: newStatus,
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
            todo.status = newStatus;
            todo.is_completed = newStatus === 'completed';
        } else {
            console.error('Failed to update task status:', data);
        }
    } catch (error) {
        console.error('Failed to update task status:', error);
    }
};

const clearCompleted = async () => {
    const completedTasks = todos.value.filter(todo => todo.is_completed);
    for (const todo of completedTasks) {
        await deleteTodo(todo.id);
    }
};

onMounted(() => {
    // Initialize viewMode from localStorage
    if (typeof window !== 'undefined') {
        const savedViewMode = localStorage.getItem('viewMode') as 'list' | 'kanban' | null;
        if (savedViewMode === 'list' || savedViewMode === 'kanban') {
            viewMode.value = savedViewMode;
        }
    }

    // Check if user is authenticated
    if (!page.props.auth?.user) {
        console.log('❌ User not authenticated');
        window.location.href = '/login';
        return;
    }
    console.log('✅ Dashboard mounted, fetching tasks...');
    fetchLists();
    fetchTasks();
});

onErrorCaptured((error) => {
    console.error('❌ KanbanBoard error:', error);
    return false;
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout :view-mode="viewMode" @update:view-mode="viewMode = $event">
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                {{ viewMode === 'list' ? 'Your To-Do List' : 'Kanban Board' }}
            </h2>
        </template>

        <!-- List View -->
        <div v-if="viewMode === 'list'" class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Loading State -->
                <div v-if="loading" class="p-8 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Loading tasks...</p>
                </div>

                <template v-else>
                    <!-- Welcome Message -->
                    <div class="mb-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h3 class="text-lg font-medium mb-2">
                                    Hello, {{ page.props.auth?.user?.name || 'User' }}! 👋
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Stay organized and productive with your personal todo list.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Add Todo Form -->
                    <div class="mb-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <form @submit.prevent="addTodo" class="flex gap-3">
                                    <input
                                        v-model="newTodo"
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

                <!-- Todo List -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Filter Tabs -->
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex">
                            <button
                                @click="filter = 'all'"
                                :class="[
                                    'px-4 py-3 text-sm font-medium border-b-2 transition-colors',
                                    filter === 'all'
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                                ]"
                            >
                                All ({{ todos.length }})
                            </button>
                            <button
                                @click="filter = 'active'"
                                :class="[
                                    'px-4 py-3 text-sm font-medium border-b-2 transition-colors',
                                    filter === 'active'
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                                ]"
                            >
                                Active ({{ activeCount }})
                            </button>
                            <button
                                @click="filter = 'completed'"
                                :class="[
                                    'px-4 py-3 text-sm font-medium border-b-2 transition-colors',
                                    filter === 'completed'
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                                ]"
                            >
                                Completed ({{ completedCount }})
                            </button>
                        </nav>
                    </div>

                    <!-- Todo Items -->
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Loading State -->
                        <div v-if="loading" class="p-8 text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Loading tasks...</p>
                        </div>

                        <!-- Todo Items -->
                        <div
                            v-else-if="filteredTodos.length > 0"
                            v-for="todo in filteredTodos"
                            :key="todo.id"
                            class="p-4 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <input
                                :id="'todo-' + todo.id"
                                type="checkbox"
                                :checked="todo.is_completed"
                                @change="toggleTodo(todo.id)"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <label
                                :for="'todo-' + todo.id"
                                :class="[
                                    'flex-1 text-sm cursor-pointer transition-all',
                                    todo.is_completed
                                        ? 'line-through text-gray-500 dark:text-gray-400'
                                        : 'text-gray-900 dark:text-gray-100'
                                ]"
                            >
                                {{ todo.title }}
                            </label>
                            <span
                                v-if="todo.list_id"
                                class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300"
                            >
                                {{ lists.find(l => l.id === todo.list_id)?.name }}
                            </span>
                            <button
                                @click="deleteTodo(todo.id)"
                                class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                title="Delete task"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="text-lg font-medium mb-1">No tasks found</p>
                            <p class="text-sm">
                                {{ filter === 'all' ? 'Add your first task above!' : `No ${filter} tasks.` }}
                            </p>
                        </div>
                    </div>

                    <!-- Footer with Clear Completed -->
                    <div v-if="completedCount > 0" class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                        <button
                            @click="clearCompleted"
                            class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium"
                        >
                            Clear completed tasks ({{ completedCount }})
                        </button>
                    </div>
                </div>
                </template>
            </div>
        </div>

        <!-- Kanban Board View -->
        <KanbanBoard v-else :todos="todos" :lists="lists" @refetch="fetchTasks" />
    </AuthenticatedLayout>
</template>
