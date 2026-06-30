<template>
    <div class="bg-white p-4 rounded shadow">

        <p class="text-gray-500 text-sm sm:text-base">{{ title }}</p>

        <div v-if="loading"
            class="mt-3 h-8 w-24 rounded bg-gray-200 animate-pulse"
        />

        <p v-else
            class="text-3xl font-bold mt-2"
        >
            {{ value }}
        </p>
        
        </div>
</template>

<script setup>
    defineProps({
        title: String,
        value: [String, Number],
        loading: Boolean
    })
</script>