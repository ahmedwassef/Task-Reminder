<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import SearchableSelect from '@/components/SearchableSelect.vue';

interface Reminder {
    id: number;
    title: string;
    description?: string;
    due_date: string;
    priority: string;
    status: string;
    category?: {
        id: number;
        name_ar: string;
        color: string;
    };
}

interface Category {
    id: number;
    name_ar: string;
    name_en: string;
    color: string;
}

interface Props {
    reminders: {
        data: Reminder[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    categories: Category[];
    filters: {
        category_id?: number;
        priority?: string;
        status?: string;
        search?: string;
        date_from?: string;
        date_to?: string;
        sort_by?: string;
        sort_order?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category_id?.toString() || null);
const selectedPriority = ref(props.filters.priority || null);
const selectedStatus = ref(props.filters.status || null);

const categoryOptions = computed(() => [
    { value: null, label: 'جميع الفئات' },
    ...props.categories.map(cat => ({
        value: cat.id.toString(),
        label: cat.name_ar,
    })),
]);

const priorityOptions = [
    { value: null, label: 'جميع الأولويات' },
    { value: 'low', label: 'منخفضة' },
    { value: 'medium', label: 'متوسطة' },
    { value: 'high', label: 'عالية' },
    { value: 'critical', label: 'حرجة' },
];

const statusOptions = [
    { value: null, label: 'جميع الحالات' },
    { value: 'pending', label: 'معلق' },
    { value: 'completed', label: 'مكتمل' },
    { value: 'overdue', label: 'متأخر' },
];

const applyFilters = () => {
    router.get('/reminders', {
        search: search.value || undefined,
        category_id: selectedCategory.value || undefined,
        priority: selectedPriority.value || undefined,
        status: selectedStatus.value || undefined,
    }, {
        preserveState: true,
        replace: true,
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

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return colors[status] || colors.pending;
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
    <Head title="التذكيرات" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">التذكيرات</h1>
                <Button @click="router.visit('/reminders/create')">
                    إنشاء تذكير جديد
                </Button>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle>تصفية</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4">
                        <Input
                            v-model="search"
                            placeholder="بحث..."
                            @keyup.enter="applyFilters"
                        />
                        <SearchableSelect
                            v-model="selectedCategory"
                            :options="categoryOptions"
                            placeholder="جميع الفئات"
                            search-placeholder="ابحث عن فئة..."
                            @update:model-value="applyFilters"
                        />
                        <SearchableSelect
                            v-model="selectedPriority"
                            :options="priorityOptions"
                            placeholder="جميع الأولويات"
                            @update:model-value="applyFilters"
                        />
                        <SearchableSelect
                            v-model="selectedStatus"
                            :options="statusOptions"
                            placeholder="جميع الحالات"
                            @update:model-value="applyFilters"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Reminders List -->
            <div v-if="reminders.data.length === 0" class="text-center py-12">
                <p class="text-muted-foreground">لا توجد تذكيرات</p>
            </div>

            <div v-else class="space-y-4">
                <Card
                    v-for="reminder in reminders.data"
                    :key="reminder.id"
                    class="cursor-pointer hover:bg-accent transition-colors"
                    @click="router.visit(`/reminders/${reminder.id}`)"
                >
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold">{{ reminder.title }}</h3>
                                    <Badge :class="getPriorityColor(reminder.priority)">
                                        {{ reminder.priority === 'low' ? 'منخفضة' : reminder.priority === 'medium' ? 'متوسطة' : reminder.priority === 'high' ? 'عالية' : 'حرجة' }}
                                    </Badge>
                                    <Badge :class="getStatusColor(reminder.status)">
                                        {{ reminder.status === 'pending' ? 'معلق' : reminder.status === 'completed' ? 'مكتمل' : 'متأخر' }}
                                    </Badge>
                                    <Badge
                                        v-if="reminder.category"
                                        :style="{ backgroundColor: reminder.category.color + '20', color: reminder.category.color }"
                                    >
                                        {{ reminder.category.name_ar }}
                                    </Badge>
                                </div>
                                <p v-if="reminder.description" class="text-sm text-muted-foreground mb-2">
                                    {{ reminder.description }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    تاريخ الاستحقاق: {{ formatDate(reminder.due_date) }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click.stop="router.visit(`/reminders/${reminder.id}/edit`)"
                                >
                                    تعديل
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pagination -->
            <div v-if="reminders.last_page > 1" class="flex items-center justify-center gap-2">
                <Button
                    variant="outline"
                    :disabled="reminders.current_page === 1"
                    @click="router.visit(`/reminders?page=${reminders.current_page - 1}`, { preserveState: true })"
                >
                    السابق
                </Button>
                <span class="text-sm">
                    صفحة {{ reminders.current_page }} من {{ reminders.last_page }}
                </span>
                <Button
                    variant="outline"
                    :disabled="reminders.current_page === reminders.last_page"
                    @click="router.visit(`/reminders?page=${reminders.current_page + 1}`, { preserveState: true })"
                >
                    التالي
                </Button>
            </div>
        </div>
    </AppLayout>
</template>

