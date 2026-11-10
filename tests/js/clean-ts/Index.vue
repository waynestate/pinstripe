<script setup lang="ts">
defineProps<{ greetings: Array<{ id: number; body: string }> }>();
</script>

<template>
  <ul>
    <li v-for="greeting in greetings" :key="greeting.id">{{ greeting.body }}</li>
  </ul>
</template>
