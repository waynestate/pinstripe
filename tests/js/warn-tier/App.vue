<script setup lang="ts">
const rawHtml = '<b>Hello</b>'
</script>

<template>
    <div class="box" id="main">
        <span v-html="rawHtml"></span>
    </div>
</template>
