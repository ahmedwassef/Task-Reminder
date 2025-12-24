<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

onMounted(() => {
    router.visit('/login');
});
</script>

<template>
    <Head title="تطبيق التذكيرات" />
    <div class="flex min-h-screen items-center justify-center">
        <div class="text-center">
            <p class="text-muted-foreground">جاري التوجيه...</p>
        </div>
    </div>
</template>
