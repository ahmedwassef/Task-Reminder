<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Category {
    id: number;
    name_ar: string;
    name_en: string;
    color: string;
    icon?: string;
    is_system: boolean;
    user_id?: number;
}

interface Props {
    categories: Category[];
}

const props = defineProps<Props>();

const deleteForm = useForm({});

const deleteCategory = (categoryId: number) => {
    if (confirm('هل أنت متأكد من حذف هذه الفئة؟')) {
        deleteForm.delete(`/categories/${categoryId}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="إدارة الفئات" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">إدارة الفئات</h1>
                <Button @click="router.visit('/categories/create')">
                    إضافة فئة جديدة
                </Button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="category in categories"
                    :key="category.id"
                    class="hover:shadow-lg transition-shadow"
                >
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>{{ category.name_ar }}</CardTitle>
                            <Badge
                                :style="{ backgroundColor: category.color + '20', color: category.color }"
                            >
                                {{ category.name_en }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2 mb-4">
                            <div
                                class="w-8 h-8 rounded-full"
                                :style="{ backgroundColor: category.color }"
                            ></div>
                            <span class="text-sm text-muted-foreground">
                                {{ category.is_system ? 'فئة نظامية' : 'فئة مخصصة' }}
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <Button
                                v-if="!category.is_system"
                                variant="outline"
                                size="sm"
                                @click="router.visit(`/categories/${category.id}/edit`)"
                            >
                                تعديل
                            </Button>
                            <Button
                                v-if="!category.is_system"
                                variant="outline"
                                size="sm"
                                @click="deleteCategory(category.id)"
                                :disabled="deleteForm.processing"
                            >
                                حذف
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="categories.length === 0" class="text-center py-12">
                <p class="text-muted-foreground">لا توجد فئات</p>
                <Button class="mt-4" @click="router.visit('/categories/create')">
                    إضافة فئة جديدة
                </Button>
            </div>
        </div>
    </AppLayout>
</template>

