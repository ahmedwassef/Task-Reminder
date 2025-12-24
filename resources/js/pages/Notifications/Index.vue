<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';

interface NotificationLog {
    id: number;
    channel: string;
    recipient: string;
    status: string;
    error_message?: string;
    sent_at?: string;
    created_at: string;
    reminder: {
        id: number;
        title: string;
    };
}

interface Props {
    logs: {
        data: NotificationLog[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        channel?: string;
        status?: string;
        search?: string;
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const selectedChannel = ref(props.filters.channel || '');
const selectedStatus = ref(props.filters.status || '');

const applyFilters = () => {
    router.get('/notifications', {
        search: search.value || undefined,
        channel: selectedChannel.value || undefined,
        status: selectedStatus.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resendForm = useForm({});

const resend = (logId: number) => {
    resendForm.post(`/notifications/${logId}/resend`, {
        preserveScroll: true,
    });
};

const getChannelLabel = (channel: string) => {
    const labels: Record<string, string> = {
        email: 'البريد الإلكتروني',
        sms: 'رسالة نصية',
        whatsapp: 'واتساب',
    };
    return labels[channel] || channel;
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        success: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
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
    <Head title="سجل الإشعارات" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">سجل الإشعارات</h1>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle>تصفية</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <Input
                            v-model="search"
                            placeholder="بحث..."
                            @keyup.enter="applyFilters"
                        />
                        <select
                            v-model="selectedChannel"
                            @change="applyFilters"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            <option value="">جميع القنوات</option>
                            <option value="email">البريد الإلكتروني</option>
                            <option value="sms">رسالة نصية</option>
                            <option value="whatsapp">واتساب</option>
                        </select>
                        <select
                            v-model="selectedStatus"
                            @change="applyFilters"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            <option value="">جميع الحالات</option>
                            <option value="success">نجح</option>
                            <option value="failed">فشل</option>
                            <option value="pending">قيد الانتظار</option>
                        </select>
                    </div>
                </CardContent>
            </Card>

            <!-- Logs List -->
            <div v-if="logs.data.length === 0" class="text-center py-12">
                <p class="text-muted-foreground">لا توجد إشعارات</p>
            </div>

            <div v-else class="space-y-4">
                <Card
                    v-for="log in logs.data"
                    :key="log.id"
                >
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="font-semibold">{{ log.reminder.title }}</h3>
                                    <Badge variant="outline">
                                        {{ getChannelLabel(log.channel) }}
                                    </Badge>
                                    <Badge :class="getStatusColor(log.status)">
                                        {{ log.status === 'success' ? 'نجح' : log.status === 'failed' ? 'فشل' : 'قيد الانتظار' }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground mb-1">
                                    المرسل إليه: {{ log.recipient }}
                                </p>
                                <p class="text-sm text-muted-foreground mb-1">
                                    تاريخ الإرسال: {{ log.sent_at ? formatDate(log.sent_at) : formatDate(log.created_at) }}
                                </p>
                                <p v-if="log.error_message" class="text-sm text-red-600 mt-2">
                                    خطأ: {{ log.error_message }}
                                </p>
                            </div>
                            <div v-if="log.status === 'failed'">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="resend(log.id)"
                                    :disabled="resendForm.processing"
                                >
                                    إعادة الإرسال
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pagination -->
            <div v-if="logs.last_page > 1" class="flex items-center justify-center gap-2">
                <Button
                    variant="outline"
                    :disabled="logs.current_page === 1"
                    @click="router.visit(`/notifications?page=${logs.current_page - 1}`, { preserveState: true })"
                >
                    السابق
                </Button>
                <span class="text-sm">
                    صفحة {{ logs.current_page }} من {{ logs.last_page }}
                </span>
                <Button
                    variant="outline"
                    :disabled="logs.current_page === logs.last_page"
                    @click="router.visit(`/notifications?page=${logs.current_page + 1}`, { preserveState: true })"
                >
                    التالي
                </Button>
            </div>
        </div>
    </AppLayout>
</template>

