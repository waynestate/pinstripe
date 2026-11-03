<script setup>
const items = ['a', 'b', 'c']
</script>

<template>
    <ul>
        <li v-for="item in items">{{ item }}</li>
    </ul>
</template>
