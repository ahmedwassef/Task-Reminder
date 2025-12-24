<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

interface Reminder {
    id: number;
    title: string;
    description?: string;
    due_date: string;
    priority: string;
    status: string;
    frequency_type: string;
    notify_email: boolean;
    notify_sms: boolean;
    notify_whatsapp: boolean;
    category?: {
        id: number;
        name_ar: string;
        color: string;
    };
}

interface Props {
    reminder: Reminder;
}

const props = defineProps<Props>();

const completeForm = useForm({});
const snoozeForm = useForm({
    snooze_until: '',
});

const complete = () => {
    completeForm.post(`/reminders/${props.reminder.id}/complete`, {
        preserveScroll: true,
    });
};

const snooze = () => {
    if (!snoozeForm.snooze_until) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        snoozeForm.snooze_until = tomorrow.toISOString().split('T')[0];
    }
    snoozeForm.post(`/reminders/${props.reminder.id}/snooze`, {
        preserveScroll: true,
    });
};

const duplicate = () => {
    router.post(`/reminders/${props.reminder.id}/duplicate`, {}, {
        onSuccess: () => {
            router.visit('/reminders');
        },
    });
};

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        low: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        high: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        critical: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return colors[priority] || colors.medium;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('ar-SA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="reminder.title" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="flex items-center justify-between">
                <Button variant="outline" @click="router.visit('/reminders')">
                    ← العودة
                </Button>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        @click="router.visit(`/reminders/${reminder.id}/edit`)"
                    >
                        تعديل
                    </Button>
                    <Button
                        v-if="reminder.status !== 'completed'"
                        @click="complete"
                        :disabled="completeForm.processing"
                    >
                        تحديد كمكتمل
                    </Button>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-2xl">{{ reminder.title }}</CardTitle>
                        <div class="flex gap-2">
                            <Badge :class="getPriorityColor(reminder.priority)">
                                {{ reminder.priority === 'low' ? 'منخفضة' : reminder.priority === 'medium' ? 'متوسطة' : reminder.priority === 'high' ? 'عالية' : 'حرجة' }}
                            </Badge>
                            <Badge
                                v-if="reminder.category"
                                :style="{ backgroundColor: reminder.category.color + '20', color: reminder.category.color }"
                            >
                                {{ reminder.category.name_ar }}
                            </Badge>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div v-if="reminder.description">
                        <h3 class="font-semibold mb-2">الوصف</h3>
                        <p class="text-muted-foreground whitespace-pre-wrap">{{ reminder.description }}</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <h3 class="font-semibold mb-2">تاريخ الاستحقاق</h3>
                            <p class="text-muted-foreground">{{ formatDate(reminder.due_date) }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold mb-2">نوع التكرار</h3>
                            <p class="text-muted-foreground">
                                {{ reminder.frequency_type === 'once' ? 'مرة واحدة' : reminder.frequency_type === 'daily' ? 'يومي' : reminder.frequency_type === 'weekly' ? 'أسبوعي' : reminder.frequency_type === 'monthly' ? 'شهري' : reminder.frequency_type === 'yearly' ? 'سنوي' : 'مخصص' }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold mb-2">قنوات الإشعار</h3>
                        <div class="flex gap-4">
                            <Badge v-if="reminder.notify_email" variant="outline">البريد الإلكتروني</Badge>
                            <Badge v-if="reminder.notify_sms" variant="outline">رسالة نصية</Badge>
                            <Badge v-if="reminder.notify_whatsapp" variant="outline">واتساب</Badge>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4 border-t">
                        <Button
                            variant="outline"
                            @click="snooze"
                            :disabled="snoozeForm.processing"
                        >
                            تأجيل
                        </Button>
                        <Button
                            variant="outline"
                            @click="duplicate"
                        >
                            تكرار
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

