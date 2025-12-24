<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const form = useForm({
    name_ar: '',
    name_en: '',
    color: '#3B82F6',
    icon: '',
});

const colors = [
    '#3B82F6', // blue
    '#10B981', // green
    '#F59E0B', // amber
    '#EF4444', // red
    '#8B5CF6', // purple
    '#EC4899', // pink
    '#14B8A6', // teal
    '#6B7280', // gray
];

const submit = () => {
    form.post('/categories', {
        onSuccess: () => {
            router.visit('/categories');
        },
    });
};
</script>

<template>
    <Head title="إضافة فئة جديدة" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">إضافة فئة جديدة</h1>
                <Button variant="outline" @click="router.visit('/categories')">
                    إلغاء
                </Button>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>معلومات الفئة</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="space-y-2">
                            <Label for="name_ar">الاسم بالعربية *</Label>
                            <Input
                                id="name_ar"
                                v-model="form.name_ar"
                                required
                                placeholder="أدخل اسم الفئة بالعربية"
                            />
                            <div v-if="form.errors.name_ar" class="text-sm text-red-600">
                                {{ form.errors.name_ar }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="name_en">الاسم بالإنجليزية *</Label>
                            <Input
                                id="name_en"
                                v-model="form.name_en"
                                required
                                placeholder="أدخل اسم الفئة بالإنجليزية"
                            />
                            <div v-if="form.errors.name_en" class="text-sm text-red-600">
                                {{ form.errors.name_en }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="color">اللون *</Label>
                            <div class="flex gap-2 flex-wrap">
                                <button
                                    v-for="color in colors"
                                    :key="color"
                                    type="button"
                                    class="w-10 h-10 rounded-full border-2 transition-all hover:scale-110"
                                    :class="form.color === color ? 'border-foreground ring-2 ring-offset-2' : 'border-gray-300'"
                                    :style="{ backgroundColor: color }"
                                    @click="form.color = color"
                                ></button>
                            </div>
                            <Input
                                id="color"
                                v-model="form.color"
                                type="color"
                                class="mt-2"
                            />
                            <div v-if="form.errors.color" class="text-sm text-red-600">
                                {{ form.errors.color }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="icon">الأيقونة (اختياري)</Label>
                            <Input
                                id="icon"
                                v-model="form.icon"
                                placeholder="اسم الأيقونة"
                            />
                        </div>

                        <div class="flex justify-end gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                @click="router.visit('/categories')"
                            >
                                إلغاء
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'جاري الحفظ...' : 'حفظ' }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>

