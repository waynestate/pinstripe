<script setup lang="ts">
async function save(): Promise<void> {}

function onClick(): void {
    save()
}
</script>

<template>
    <button type="button" @click="onClick">Save</button>
</template>
