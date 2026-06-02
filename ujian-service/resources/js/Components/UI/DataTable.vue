<template>
    <div class="datatable-container">
        <!-- Header with search and actions -->
        <div v-if="showHeader" class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div v-if="showSearch" class="input-group">
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="form-control"
                            :placeholder="searchPlaceholder"
                            @input="handleSearch"
                        />
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <slot name="actions" />
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover" :class="tableClasses">
                <thead>
                    <tr>
                        <th v-if="selectable" style="width: 40px;">
                            <input
                                type="checkbox"
                                :checked="allSelected"
                                @change="toggleSelectAll"
                            />
                        </th>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :style="column.width ? `width: ${column.width}` : null"
                            :class="{ 'sortable': column.sortable }"
                            @click="column.sortable ? handleSort(column.key) : null"
                        >
                            {{ column.label }}
                            <i
                                v-if="column.sortable"
                                :class="getSortIcon(column.key)"
                                class="ml-1"
                            ></i>
                        </th>
                        <th v-if="hasActions" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td :colspan="totalColumns" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                    <tr v-else-if="!loading && paginatedData.length === 0">
                        <td :colspan="totalColumns" class="text-center py-4 text-muted">
                            {{ emptyText }}
                        </td>
                    </tr>
                    <tr v-else v-for="(row, index) in paginatedData" :key="getRowKey(row, index)">
                        <td v-if="selectable">
                            <input
                                type="checkbox"
                                :checked="isSelected(row)"
                                @change="toggleSelect(row)"
                            />
                        </td>
                        <td v-for="column in columns" :key="column.key">
                            <slot :name="`cell-${column.key}`" :row="row" :value="getNestedValue(row, column.key)">
                                {{ formatValue(row, column) }}
                            </slot>
                        </td>
                        <td v-if="hasActions">
                            <slot name="actions" :row="row" :index="index" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer with pagination -->
        <div v-if="showFooter" class="card-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="dataTables_info">
                        Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ filteredData.length }} entries
                        <span v-if="selectedRows.length > 0">
                            ({{ selectedRows.length }} selected)
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <ul class="pagination pagination-sm m-0 float-right">
                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                            <a class="page-link" href="#" @click.prevent="goToPage(currentPage - 1)">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <li
                            v-for="page in visiblePages"
                            :key="page"
                            class="page-item"
                            :class="{ active: page === currentPage }"
                        >
                            <a class="page-link" href="#" @click.prevent="goToPage(page)">
                                {{ page }}
                            </a>
                        </li>
                        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                            <a class="page-link" href="#" @click.prevent="goToPage(currentPage + 1)">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    data: {
        type: Array,
        required: true
    },
    columns: {
        type: Array,
        required: true,
        validator: (value) => {
            return value.every(col => 'key' in col && 'label' in col);
        }
    },
    perPage: {
        type: Number,
        default: 10
    },
    loading: {
        type: Boolean,
        default: false
    },
    striped: {
        type: Boolean,
        default: true
    },
    bordered: {
        type: Boolean,
        default: false
    },
    hover: {
        type: Boolean,
        default: true
    },
    small: {
        type: Boolean,
        default: false
    },
    selectable: {
        type: Boolean,
        default: false
    },
    showHeader: {
        type: Boolean,
        default: true
    },
    showFooter: {
        type: Boolean,
        default: true
    },
    showSearch: {
        type: Boolean,
        default: true
    },
    searchPlaceholder: {
        type: String,
        default: 'Search...'
    },
    emptyText: {
        type: String,
        default: 'No data available'
    },
    rowKey: {
        type: String,
        default: 'id'
    }
});

const emit = defineEmits(['sort', 'search', 'select', 'page-change']);

const currentPage = ref(1);
const searchQuery = ref('');
const sortKey = ref(null);
const sortOrder = ref('asc');
const selectedRows = ref([]);

const hasActions = computed(() => !!props.$slots.actions);

const totalColumns = computed(() => {
    let count = props.columns.length;
    if (props.selectable) count++;
    if (hasActions.value) count++;
    return count;
});

const tableClasses = computed(() => {
    const classes = [];
    if (props.striped) classes.push('table-striped');
    if (props.bordered) classes.push('table-bordered');
    if (props.hover) classes.push('table-hover');
    if (props.small) classes.push('table-sm');
    return classes.join(' ');
});

const filteredData = computed(() => {
    let data = [...props.data];
    
    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(row => {
            return props.columns.some(column => {
                const value = getNestedValue(row, column.key);
                return String(value).toLowerCase().includes(query);
            });
        });
    }
    
    // Sort
    if (sortKey.value) {
        data.sort((a, b) => {
            const aVal = getNestedValue(a, sortKey.value);
            const bVal = getNestedValue(b, sortKey.value);
            
            if (aVal === bVal) return 0;
            
            const comparison = aVal > bVal ? 1 : -1;
            return sortOrder.value === 'asc' ? comparison : -comparison;
        });
    }
    
    return data;
});

const totalPages = computed(() => {
    return Math.ceil(filteredData.value.length / props.perPage);
});

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * props.perPage;
    const end = start + props.perPage;
    return filteredData.value.slice(start, end);
});

const startIndex = computed(() => {
    return (currentPage.value - 1) * props.perPage;
});

const endIndex = computed(() => {
    return Math.min(startIndex.value + props.perPage, filteredData.value.length);
});

const visiblePages = computed(() => {
    const pages = [];
    const maxVisible = 5;
    let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
    let end = Math.min(totalPages.value, start + maxVisible - 1);
    
    if (end - start < maxVisible - 1) {
        start = Math.max(1, end - maxVisible + 1);
    }
    
    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    
    return pages;
});

const allSelected = computed(() => {
    return paginatedData.value.length > 0 && 
           paginatedData.value.every(row => isSelected(row));
});

const getNestedValue = (obj, path) => {
    return path.split('.').reduce((current, key) => current?.[key], obj);
};

const formatValue = (row, column) => {
    const value = getNestedValue(row, column.key);
    
    if (column.formatter && typeof column.formatter === 'function') {
        return column.formatter(value, row);
    }
    
    return value ?? '-';
};

const getRowKey = (row, index) => {
    return getNestedValue(row, props.rowKey) ?? index;
};

const handleSort = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
    
    emit('sort', { key: sortKey.value, order: sortOrder.value });
};

const getSortIcon = (key) => {
    if (sortKey.value !== key) {
        return 'fas fa-sort text-muted';
    }
    return sortOrder.value === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
};

const handleSearch = () => {
    currentPage.value = 1;
    emit('search', searchQuery.value);
};

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        emit('page-change', page);
    }
};

const isSelected = (row) => {
    const key = getRowKey(row);
    return selectedRows.value.some(selected => getRowKey(selected) === key);
};

const toggleSelect = (row) => {
    const key = getRowKey(row);
    const index = selectedRows.value.findIndex(selected => getRowKey(selected) === key);
    
    if (index > -1) {
        selectedRows.value.splice(index, 1);
    } else {
        selectedRows.value.push(row);
    }
    
    emit('select', selectedRows.value);
};

const toggleSelectAll = () => {
    if (allSelected.value) {
        // Deselect all on current page
        paginatedData.value.forEach(row => {
            const key = getRowKey(row);
            const index = selectedRows.value.findIndex(selected => getRowKey(selected) === key);
            if (index > -1) {
                selectedRows.value.splice(index, 1);
            }
        });
    } else {
        // Select all on current page
        paginatedData.value.forEach(row => {
            if (!isSelected(row)) {
                selectedRows.value.push(row);
            }
        });
    }
    
    emit('select', selectedRows.value);
};

// Reset page when data changes
watch(() => props.data, () => {
    currentPage.value = 1;
});
</script>

<style scoped>
.sortable {
    cursor: pointer;
    user-select: none;
}

.sortable:hover {
    background-color: rgba(0, 0, 0, 0.05);
}
</style>
