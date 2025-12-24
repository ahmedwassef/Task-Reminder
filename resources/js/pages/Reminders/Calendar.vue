<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ref, computed } from 'vue';

interface CalendarReminder {
    id: number;
    title: string;
    start: string;
    priority: string;
    category?: string;
    color: string;
}

interface Props {
    reminders: CalendarReminder[];
}

const props = defineProps<Props>();

const currentDate = ref(new Date());
const currentMonth = computed(() => currentDate.value.getMonth());
const currentYear = computed(() => currentDate.value.getFullYear());

const monthNames = [
    'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
];

const daysOfWeek = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

const getDaysInMonth = (date: Date) => {
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    const days = [];
    
    // Add empty cells for days before the first day of the month
    for (let i = 0; i < startingDayOfWeek; i++) {
        days.push(null);
    }
    
    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        days.push(day);
    }
    
    return days;
};

const calendarDays = computed(() => getDaysInMonth(currentDate.value));

const getRemindersForDate = (day: number) => {
    if (!day) return [];
    
    const dateStr = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    return props.reminders.filter(r => r.start === dateStr);
};

const previousMonth = () => {
    currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1);
};

const goToToday = () => {
    currentDate.value = new Date();
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

const isToday = (day: number) => {
    if (!day) return false;
    const today = new Date();
    return (
        day === today.getDate() &&
        currentMonth.value === today.getMonth() &&
        currentYear.value === today.getFullYear()
    );
};
</script>

<template>
    <Head title="عرض التقويم" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">عرض التقويم</h1>
                <div class="flex gap-2">
                    <Button variant="outline" @click="router.visit('/reminders')">
                        عرض القائمة
                    </Button>
                    <Button @click="router.visit('/reminders/create')">
                        إضافة تذكير
                    </Button>
                </div>
            </div>

            <!-- Calendar Header -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Button variant="outline" @click="previousMonth">
                                ← السابق
                            </Button>
                            <h2 class="text-2xl font-bold">
                                {{ monthNames[currentMonth] }} {{ currentYear }}
                            </h2>
                            <Button variant="outline" @click="nextMonth">
                                التالي →
                            </Button>
                        </div>
                        <Button variant="outline" @click="goToToday">
                            اليوم
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Days of Week Header -->
                    <div class="grid grid-cols-7 gap-2 mb-2">
                        <div
                            v-for="day in daysOfWeek"
                            :key="day"
                            class="text-center font-semibold text-sm p-2"
                        >
                            {{ day }}
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-2">
                        <div
                            v-for="(day, index) in calendarDays"
                            :key="index"
                            class="min-h-[100px] border rounded-lg p-2"
                            :class="{
                                'bg-accent': isToday(day),
                                'border-2 border-primary': isToday(day),
                            }"
                        >
                            <div v-if="day" class="flex flex-col h-full">
                                <div class="font-semibold mb-1">{{ day }}</div>
                                <div class="flex-1 space-y-1 overflow-y-auto">
                                    <div
                                        v-for="reminder in getRemindersForDate(day)"
                                        :key="reminder.id"
                                        class="text-xs p-1 rounded cursor-pointer hover:opacity-80 transition-opacity"
                                        :style="{ backgroundColor: reminder.color + '40', borderLeft: `3px solid ${reminder.color}` }"
                                        @click="router.visit(`/reminders/${reminder.id}`)"
                                    >
                                        <div class="font-medium truncate">{{ reminder.title }}</div>
                                        <Badge
                                            :class="getPriorityColor(reminder.priority)"
                                            class="text-xs mt-1"
                                        >
                                            {{ reminder.priority === 'low' ? 'منخفضة' : reminder.priority === 'medium' ? 'متوسطة' : reminder.priority === 'high' ? 'عالية' : 'حرجة' }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Legend -->
            <Card>
                <CardHeader>
                    <CardTitle>مفتاح الألوان</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-blue-500"></div>
                            <span class="text-sm">منخفضة</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-yellow-500"></div>
                            <span class="text-sm">متوسطة</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-orange-500"></div>
                            <span class="text-sm">عالية</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-red-500"></div>
                            <span class="text-sm">حرجة</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

