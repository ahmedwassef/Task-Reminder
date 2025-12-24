<script setup lang="ts">
import { computed } from 'vue';
import { DatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

interface Props {
    modelValue: string | Date | null;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    timePicker?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'اختر التاريخ',
    required: false,
    disabled: false,
    timePicker: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | Date | null];
}>();

const dateValue = computed({
    get: () => {
        if (!props.modelValue) return null;
        if (props.modelValue instanceof Date) return props.modelValue;
        try {
            return new Date(props.modelValue);
        } catch {
            return null;
        }
    },
    set: (value: Date | null) => {
        if (!value) {
            emit('update:modelValue', null);
            return;
        }
        if (props.timePicker) {
            emit('update:modelValue', value.toISOString());
        } else {
            const dateStr = value.toISOString().split('T')[0];
            emit('update:modelValue', dateStr);
        }
    },
});

const format = (date: Date) => {
    if (!date) return '';
    return date.toLocaleDateString('ar-SA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        ...(props.timePicker && {
            hour: '2-digit',
            minute: '2-digit',
        }),
    });
};

const arabicLocale = {
    months: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
    monthsShort: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
    weekdays: ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
    weekdaysShort: ['أحد', 'إثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'],
    weekdaysMin: ['ح', 'ن', 'ث', 'ر', 'خ', 'ج', 'س'],
};
</script>

<template>
    <div class="datepicker-wrapper">
        <DatePicker
            v-model="dateValue"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :enable-time-picker="timePicker"
            :locale="arabicLocale"
            :format="format"
            :enable-utc="false"
            :auto-apply="true"
            :teleport="true"
            class="w-full"
            input-class-name="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        />
    </div>
</template>

<style scoped>
.datepicker-wrapper :deep(.dp__input_wrap) {
    width: 100%;
}

.datepicker-wrapper :deep(.dp__input) {
    width: 100%;
    direction: rtl;
    text-align: right;
}

.datepicker-wrapper :deep(.dp__menu) {
    direction: rtl;
}

.datepicker-wrapper :deep(.dp__calendar_header_item) {
    font-family: 'Cairo', 'Tajawal', sans-serif;
}

.datepicker-wrapper :deep(.dp__month_year_wrap) {
    font-family: 'Cairo', 'Tajawal', sans-serif;
}
</style>
