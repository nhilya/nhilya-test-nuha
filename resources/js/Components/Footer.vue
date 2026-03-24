<template>
    <footer class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700 z-10">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                © {{ currentYear }} {{ appName }} by
                <a 
                :href="authorUrl"
                target="_blank"
                rel="noopener noreferrer" 
                class="text-blue-600 dark:text-blue-400 hover:underline"
                >
                {{ authorName }}
            </a>
            . Built with Laravel & Vue.js.
            </div>
        </div>
    </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const currentYear = computed(() => new Date().getFullYear());
const appName = "To-Do App";
const authorUrl = "https://nhilya.dev";
const authorName = "Nuha Ilya";
</script>