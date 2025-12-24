<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';

interface Props {
    stats: {
        total: number;
        pending: number;
        completedThisMonth: number;
        overdue: number;
    };
    todaysReminders: any[];
    thisWeeksReminders: any[];
    thisMonthsReminders: any[];
    criticalReminders: any[];
    recentNotifications: any[];
    recentCompleted: any[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'لوحة التحكم',
        href: dashboard().url,
    },
];

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
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="لوحة التحكم" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">إجمالي التذكيرات</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">التذكيرات المعلقة</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pending }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">المكتملة هذا الشهر</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.completedThisMonth }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">التذكيرات المتأخرة</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ stats.overdue }}</div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Today's Reminders -->
                <Card>
                    <CardHeader>
                        <CardTitle>تذكيرات اليوم</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="todaysReminders.length === 0" class="text-center text-sm text-muted-foreground py-4">
                            لا توجد تذكيرات اليوم
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="reminder in todaysReminders"
                                :key="reminder.id"
                                class="flex items-center justify-between p-3 border rounded-lg hover:bg-accent cursor-pointer"
                                @click="router.visit(`/reminders/${reminder.id}`)"
                            >
                                <div class="flex-1">
                                    <div class="font-medium">{{ reminder.title }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ formatDate(reminder.due_date) }}
                                    </div>
                                </div>
                                <Badge :class="getPriorityColor(reminder.priority)">
                                    {{ reminder.priority }}
                                </Badge>
                            </div>
                        </div>
                        <Button
                            v-if="todaysReminders.length > 0"
                            variant="outline"
                            class="w-full mt-4"
                            @click="router.visit('/reminders')"
                        >
                            عرض الكل
                        </Button>
                    </CardContent>
                </Card>

                <!-- Critical Reminders -->
                <Card>
                    <CardHeader>
                        <CardTitle>التذكيرات الحرجة</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="criticalReminders.length === 0" class="text-center text-sm text-muted-foreground py-4">
                            لا توجد تذكيرات حرجة
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="reminder in criticalReminders"
                                :key="reminder.id"
                                class="flex items-center justify-between p-3 border rounded-lg hover:bg-accent cursor-pointer"
                                @click="router.visit(`/reminders/${reminder.id}`)"
                            >
                                <div class="flex-1">
                                    <div class="font-medium">{{ reminder.title }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ formatDate(reminder.due_date) }}
                                    </div>
                                </div>
                                <Badge class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                    حرجة
                                </Badge>
                            </div>
                        </div>
                        <Button
                            v-if="criticalReminders.length > 0"
                            variant="outline"
                            class="w-full mt-4"
                            @click="router.visit('/reminders?priority=critical')"
                        >
                            عرض الكل
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>إجراءات سريعة</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-3">
                        <Button @click="router.visit('/reminders/create')">
                            إنشاء تذكير جديد
                        </Button>
                        <Button variant="outline" @click="router.visit('/reminders')">
                            عرض جميع التذكيرات
                        </Button>
                        <Button variant="outline" @click="router.visit('/reminders/calendar/view')">
                            عرض التقويم
                        </Button>
                        <Button variant="outline" @click="router.visit('/categories')">
                            إدارة الفئات
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
