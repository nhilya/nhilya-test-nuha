<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <Header :view-mode="viewMode" @change-view="changeView" />

            <!-- Page Heading -->
            <header
                class="bg-white shadow dark:bg-gray-800"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="pb-16">
                <slot />
            </main>
        </div>

        <Footer />
    </div>
</template>

<script setup lang="ts">
import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';
import { computed } from 'vue';

const props = defineProps<{
    viewMode?: 'list' | 'kanban';
}>();

const emit = defineEmits<{
    'update:viewMode': [mode: 'list' | 'kanban'];
}>();

const viewMode = computed({
    get: () => props.viewMode || 'list',
    set: (value: 'list' | 'kanban') => {
        if (typeof window !== 'undefined') {
            localStorage.setItem('viewMode', value);
        }
        emit('update:viewMode', value);
    }
});

const changeView = (mode: 'list' | 'kanban') => {
    console.log('🔄 AuthenticatedLayout.changeView called with:', mode);
    viewMode.value = mode;
};
</script>
