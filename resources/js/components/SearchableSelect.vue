<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Button } from '@/components/ui/button';

interface Option {
    value: string | number | null;
    label: string;
    disabled?: boolean;
}

interface Props {
    modelValue: string | number | null;
    options: Option[];
    placeholder?: string;
    searchPlaceholder?: string;
    required?: boolean;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'اختر...',
    searchPlaceholder: 'ابحث...',
    required: false,
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
}>();

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref<HTMLElement | null>(null);

const selectedOption = computed(() => {
    return props.options.find(opt => opt.value === props.modelValue);
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const query = searchQuery.value.toLowerCase();
    return props.options.filter(opt =>
        opt.label.toLowerCase().includes(query)
    );
});

const selectOption = (option: Option) => {
    if (option.disabled) return;
    emit('update:modelValue', option.value);
    isOpen.value = false;
    searchQuery.value = '';
};

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
    }
};

const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        isOpen.value = false;
        searchQuery.value = '';
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="relative w-full">
        <button
            type="button"
            :disabled="disabled"
            :class="[
                'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                isOpen && 'ring-2 ring-ring',
            ]"
            @click="toggleDropdown"
        >
            <span :class="selectedOption ? 'text-foreground' : 'text-muted-foreground'">
                {{ selectedOption ? selectedOption.label : placeholder }}
            </span>
            <svg
                class="h-4 w-4 opacity-50 transition-transform"
                :class="isOpen && 'rotate-180'"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </button>

        <div
            v-if="isOpen"
            class="absolute z-50 mt-1 w-full rounded-md border bg-popover shadow-md"
            style="max-height: 300px; overflow-y: auto;"
        >
            <div class="p-2 border-b">
                <input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="searchPlaceholder"
                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    @click.stop
                />
            </div>
            <div class="p-1">
                <button
                    v-if="!required"
                    type="button"
                    class="w-full text-right px-2 py-1.5 text-sm hover:bg-accent hover:text-accent-foreground rounded-sm"
                    @click="selectOption({ value: null, label: placeholder })"
                >
                    {{ placeholder }}
                </button>
                <button
                    v-for="option in filteredOptions"
                    :key="String(option.value)"
                    type="button"
                    :disabled="option.disabled"
                    :class="[
                        'w-full text-right px-2 py-1.5 text-sm rounded-sm transition-colors',
                        option.value === modelValue
                            ? 'bg-accent text-accent-foreground font-medium'
                            : 'hover:bg-accent hover:text-accent-foreground',
                        option.disabled && 'opacity-50 cursor-not-allowed',
                    ]"
                    @click="selectOption(option)"
                >
                    {{ option.label }}
                </button>
                <div
                    v-if="filteredOptions.length === 0"
                    class="px-2 py-1.5 text-sm text-muted-foreground text-center"
                >
                    لا توجد نتائج
                </div>
            </div>
        </div>
    </div>
</template>

