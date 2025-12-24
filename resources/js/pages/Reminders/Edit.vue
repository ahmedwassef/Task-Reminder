<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import DatePicker from '@/components/DatePicker.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';

interface Category {
    id: number;
    name_ar: string;
    name_en: string;
    color: string;
}

interface Reminder {
    id: number;
    title: string;
    description?: string;
    due_date: string;
    priority: string;
    frequency_type: string;
    category_id?: number;
    notify_email: boolean;
    notify_sms: boolean;
    notify_whatsapp: boolean;
}

interface Props {
    reminder: Reminder;
    categories: Category[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.reminder.title,
    description: props.reminder.description || '',
    category_id: props.reminder.category_id || null,
    due_date: new Date(props.reminder.due_date) as Date | string | null,
    priority: props.reminder.priority,
    frequency_type: props.reminder.frequency_type,
    notify_email: props.reminder.notify_email,
    notify_sms: props.reminder.notify_sms,
    notify_whatsapp: props.reminder.notify_whatsapp,
});

const categoryOptions = computed(() => [
    { value: null, label: 'لا توجد فئة' },
    ...props.categories.map(cat => ({
        value: cat.id,
        label: cat.name_ar,
    })),
]);

const priorityOptions = [
    { value: 'low', label: 'منخفضة' },
    { value: 'medium', label: 'متوسطة' },
    { value: 'high', label: 'عالية' },
    { value: 'critical', label: 'حرجة' },
];

const frequencyOptions = [
    { value: 'once', label: 'مرة واحدة' },
    { value: 'daily', label: 'يومي' },
    { value: 'weekly', label: 'أسبوعي' },
    { value: 'monthly', label: 'شهري' },
    { value: 'yearly', label: 'سنوي' },
];

const submit = () => {
    let dueDateTime: string;
    if (form.due_date instanceof Date) {
        dueDateTime = form.due_date.toISOString();
    } else if (typeof form.due_date === 'string') {
        dueDateTime = form.due_date;
    } else {
        alert('يرجى اختيار تاريخ الاستحقاق');
        return;
    }

    form.transform((data) => ({
        ...data,
        due_date: dueDateTime,
    })).put(`/reminders/${props.reminder.id}`, {
        onSuccess: () => {
            router.visit(`/reminders/${props.reminder.id}`);
        },
    });
};
</script>

<template>
    <Head title="تعديل التذكير" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">تعديل التذكير</h1>
                <Button variant="outline" @click="router.visit(`/reminders/${reminder.id}`)">
                    إلغاء
                </Button>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>معلومات التذكير</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="space-y-2">
                            <Label for="title">العنوان *</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                required
                                placeholder="أدخل عنوان التذكير"
                            />
                            <div v-if="form.errors.title" class="text-sm text-red-600">
                                {{ form.errors.title }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">الوصف</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="أدخل وصف التذكير"
                            />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="category_id">الفئة</Label>
                                <SearchableSelect
                                    id="category_id"
                                    v-model="form.category_id"
                                    :options="categoryOptions"
                                    placeholder="اختر الفئة"
                                    search-placeholder="ابحث عن فئة..."
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="priority">الأولوية *</Label>
                                <SearchableSelect
                                    id="priority"
                                    v-model="form.priority"
                                    :options="priorityOptions"
                                    placeholder="اختر الأولوية"
                                    required
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="due_date">تاريخ ووقت الاستحقاق *</Label>
                            <DatePicker
                                id="due_date"
                                v-model="form.due_date"
                                :time-picker="true"
                                placeholder="اختر التاريخ والوقت"
                                required
                            />
                            <div v-if="form.errors.due_date" class="text-sm text-red-600">
                                {{ form.errors.due_date }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="frequency_type">نوع التكرار</Label>
                            <SearchableSelect
                                id="frequency_type"
                                v-model="form.frequency_type"
                                :options="frequencyOptions"
                                placeholder="اختر نوع التكرار"
                            />
                        </div>

                        <div class="space-y-4">
                            <Label>قنوات الإشعار</Label>
                            <div class="space-y-3">
                                <div class="flex items-center space-x-2 space-x-reverse">
                                    <Checkbox
                                        id="notify_email"
                                        v-model:checked="form.notify_email"
                                    />
                                    <Label for="notify_email" class="cursor-pointer">
                                        إشعار بالبريد الإلكتروني
                                    </Label>
                                </div>
                                <div class="flex items-center space-x-2 space-x-reverse">
                                    <Checkbox
                                        id="notify_sms"
                                        v-model:checked="form.notify_sms"
                                    />
                                    <Label for="notify_sms" class="cursor-pointer">
                                        إشعار برسالة نصية
                                    </Label>
                                </div>
                                <div class="flex items-center space-x-2 space-x-reverse">
                                    <Checkbox
                                        id="notify_whatsapp"
                                        v-model:checked="form.notify_whatsapp"
                                    />
                                    <Label for="notify_whatsapp" class="cursor-pointer">
                                        إشعار بالواتساب
                                    </Label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                @click="router.visit(`/reminders/${reminder.id}`)"
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
