<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

interface Category {
    id: number;
    name_ar: string;
    name_en: string;
    color: string;
    icon?: string;
    is_system: boolean;
}

interface Props {
    category: Category;
}

const props = defineProps<Props>();
</script>

<template>
    <Head :title="category.name_ar" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="flex items-center justify-between">
                <Button variant="outline" @click="router.visit('/categories')">
                    ← العودة
                </Button>
                <Button
                    v-if="!category.is_system"
                    variant="outline"
                    @click="router.visit(`/categories/${category.id}/edit`)"
                >
                    تعديل
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-2xl">{{ category.name_ar }}</CardTitle>
                        <Badge
                            :style="{ backgroundColor: category.color + '20', color: category.color }"
                        >
                            {{ category.name_en }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-16 h-16 rounded-full"
                            :style="{ backgroundColor: category.color }"
                        ></div>
                        <div>
                            <p class="text-sm text-muted-foreground">الاسم بالعربية</p>
                            <p class="font-semibold">{{ category.name_ar }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground mb-1">الاسم بالإنجليزية</p>
                        <p class="font-semibold">{{ category.name_en }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground mb-1">اللون</p>
                        <div class="flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded"
                                :style="{ backgroundColor: category.color }"
                            ></div>
                            <span class="font-mono">{{ category.color }}</span>
                        </div>
                    </div>

                    <div v-if="category.icon">
                        <p class="text-sm text-muted-foreground mb-1">الأيقونة</p>
                        <p class="font-semibold">{{ category.icon }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground mb-1">النوع</p>
                        <Badge variant="outline">
                            {{ category.is_system ? 'فئة نظامية' : 'فئة مخصصة' }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

