<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manajemen Ujian</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="primary"
                            @click="router.visit(route('ujian.create'))"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Buat Ujian Baru
                        </Button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-12">
                <Card>
                    <div class="row">
                        <div class="col-md-3">
                            <Select
                                v-model="filters.periode_id"
                                label="Periode"
                                :options="periodeOptions"
                                placeholder="Semua Periode"
                                @change="applyFilters"
                            />
                        </div>
                        <div class="col-md-3">
                            <Input
                                v-model="filters.mata_pelajaran"
                                label="Mata Pelajaran"
                                placeholder="Cari mata pelajaran..."
                                @input="debounceFilter"
                            />
                        </div>
                        <div class="col-md-2">
                            <Select
                                v-model="filters.status"
                                label="Status"
                                :options="statusOptions"
                                placeholder="Semua Status"
                                @change="applyFilters"
                            />
                        </div>
                        <div class="col-md-2">
                            <Select
                                v-model="filters.jenis"
                                label="Jenis"
                                :options="jenisOptions"
                                placeholder="Semua Jenis"
                                @change="applyFilters"
                            />
                        </div>
                        <div class="col-md-2">
                            <label class="d-block">&nbsp;</label>
                            <Button
                                variant="secondary"
                                block
                                @click="resetFilters"
                            >
                                <i class="fas fa-redo mr-2"></i>
                                Reset
                            </Button>
                        </div>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-3">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ statistics.total || 0 }}</h3>
                        <p>Total Ujian</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ statistics.published || 0 }}</h3>
                        <p>Published</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ statistics.draft || 0 }}</h3>
                        <p>Draft</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-edit"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ statistics.archived || 0 }}</h3>
                        <p>Archived</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-archive"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <Card>
                    <DataTable
                        :data="ujians"
                        :columns="columns"
                        :loading="loading"
                        @search="handleSearch"
                    >
                        <template #actions>
                            <Button
                                variant="success"
                                size="sm"
                                @click="refreshData"
                            >
                                <i class="fas fa-sync mr-2"></i>
                                Refresh
                            </Button>
                            <Button
                                variant="info"
                                size="sm"
                                class="ml-2"
                                @click="exportData"
                            >
                                <i class="fas fa-download mr-2"></i>
                                Export
                            </Button>
                        </template>

                        <template #cell-judul="{ row }">
                            <div>
                                <strong>{{ row.judul }}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-book mr-1"></i>
                                    {{ row.mata_pelajaran }}
                                </small>
                            </div>
                        </template>

                        <template #cell-periode="{ row }">
                            <div>
                                <Badge variant="info" size="sm">
                                    {{ row.periode?.nama || '-' }}
                                </Badge>
                                <br>
                                <small class="text-muted">{{ row.periode?.tahun_ajaran }}</small>
                            </div>
                        </template>

                        <template #cell-info="{ row }">
                            <div class="text-sm">
                                <div>
                                    <i class="fas fa-question-circle text-primary mr-1"></i>
                                    {{ row.jumlah_soal }} soal
                                </div>
                                <div>
                                    <i class="fas fa-clock text-info mr-1"></i>
                                    {{ row.durasi }} menit
                                </div>
                                <div>
                                    <i class="fas fa-star text-warning mr-1"></i>
                                    {{ row.passing_grade }}%
                                </div>
                            </div>
                        </template>

                        <template #cell-jadwal="{ row }">
                            <div class="text-sm">
                                <div>
                                    <small class="text-muted">Mulai:</small>
                                    <br>
                                    {{ formatDateTime(row.tanggal_mulai) }}
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted">Selesai:</small>
                                    <br>
                                    {{ formatDateTime(row.tanggal_selesai) }}
                                </div>
                            </div>
                        </template>

                        <template #cell-peserta="{ row }">
                            <div class="text-center">
                                <h5 class="mb-0">{{ row.jumlah_peserta || 0 }}</h5>
                                <small class="text-muted">peserta</small>
                            </div>
                        </template>

                        <template #cell-status="{ row }">
                            <Badge :variant="getStatusVariant(row.status)">
                                {{ row.status }}
                            </Badge>
                        </template>

                        <template #actions="{ row }">
                            <Dropdown trigger-text="Aksi" variant="primary" size="sm">
                                <a class="dropdown-item" href="#" @click.prevent="viewDetail(row.id)">
                                    <i class="fas fa-eye mr-2"></i> Detail
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="editUjian(row.id)">
                                    <i class="fas fa-edit mr-2"></i> Edit
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="manageSoal(row.id)">
                                    <i class="fas fa-list mr-2"></i> Kelola Soal
                                </a>
                                <div class="dropdown-divider"></div>
                                <a 
                                    v-if="row.status === 'draft'"
                                    class="dropdown-item text-success" 
                                    href="#" 
                                    @click.prevent="publishUjian(row)"
                                >
                                    <i class="fas fa-check-circle mr-2"></i> Publish
                                </a>
                                <a 
                                    v-if="row.status === 'published'"
                                    class="dropdown-item text-warning" 
                                    href="#" 
                                    @click.prevent="unpublishUjian(row)"
                                >
                                    <i class="fas fa-times-circle mr-2"></i> Unpublish
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="duplicateUjian(row)">
                                    <i class="fas fa-copy mr-2"></i> Duplikat
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="viewResults(row.id)">
                                    <i class="fas fa-chart-bar mr-2"></i> Lihat Hasil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a 
                                    class="dropdown-item text-danger" 
                                    href="#" 
                                    @click.prevent="confirmDelete(row)"
                                >
                                    <i class="fas fa-trash mr-2"></i> Hapus
                                </a>
                            </Dropdown>
                        </template>
                    </DataTable>
                </Card>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal
            v-model:show="showDeleteModal"
            title="Konfirmasi Hapus"
            confirm-text="Ya, Hapus"
            confirm-variant="danger"
            :confirm-loading="deleting"
            @confirm="handleDelete"
        >
            <Alert variant="warning" icon>
                <strong>Perhatian!</strong> Tindakan ini tidak dapat dibatalkan.
            </Alert>
            <p>Apakah Anda yakin ingin menghapus ujian <strong>{{ selectedUjian?.judul }}</strong>?</p>
            <p class="text-muted mb-0">
                Semua data soal dan hasil ujian yang terkait juga akan terhapus.
            </p>
        </Modal>

        <!-- Publish Confirmation Modal -->
        <Modal
            v-model:show="showPublishModal"
            title="Konfirmasi Publish"
            confirm-text="Ya, Publish"
            confirm-variant="success"
            :confirm-loading="publishing"
            @confirm="handlePublish"
        >
            <Alert variant="info" icon>
                Ujian yang sudah dipublish akan dapat dilihat dan dikerjakan oleh siswa.
            </Alert>
            <p>Apakah Anda yakin ingin mempublish ujian <strong>{{ selectedUjian?.judul }}</strong>?</p>
            <div v-if="publishValidation.errors.length > 0" class="mt-3">
                <Alert variant="danger" icon>
                    <strong>Tidak dapat publish!</strong> Perbaiki masalah berikut:
                    <ul class="mb-0 mt-2">
                        <li v-for="(error, index) in publishValidation.errors" :key="index">
                            {{ error }}
                        </li>
                    </ul>
                </Alert>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, DataTable, Button, Badge, Dropdown, Modal, Alert, Input, Select, useToast } from '@/Components';

const props = defineProps({
    ujians: {
        type: Array,
        required: true
    },
    periodes: {
        type: Array,
        default: () => []
    },
    statistics: {
        type: Object,
        default: () => ({})
    }
});

const toast = useToast();
const loading = ref(false);
const showDeleteModal = ref(false);
const showPublishModal = ref(false);
const selectedUjian = ref(null);
const deleting = ref(false);
const publishing = ref(false);
const publishValidation = reactive({
    errors: []
});

const filters = reactive({
    periode_id: '',
    mata_pelajaran: '',
    status: '',
    jenis: ''
});

const periodeOptions = computed(() => {
    return props.periodes.map(p => ({
        value: p.id,
        label: `${p.nama} (${p.tahun_ajaran})`
    }));
});

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'archived', label: 'Archived' }
];

const jenisOptions = [
    { value: 'uts', label: 'UTS' },
    { value: 'uas', label: 'UAS' },
    { value: 'quiz', label: 'Quiz' },
    { value: 'tugas', label: 'Tugas' }
];

const columns = [
    { key: 'judul', label: 'Judul Ujian', sortable: true },
    { key: 'periode', label: 'Periode', sortable: false },
    { key: 'info', label: 'Info', sortable: false },
    { key: 'jadwal', label: 'Jadwal', sortable: false },
    { key: 'peserta', label: 'Peserta', sortable: true },
    { key: 'status', label: 'Status', sortable: true }
];

const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusVariant = (status) => {
    const variants = {
        'draft': 'warning',
        'published': 'success',
        'archived': 'secondary'
    };
    return variants[status] || 'secondary';
};

let filterTimeout = null;
const debounceFilter = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    router.get(route('ujian.index'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    filters.periode_id = '';
    filters.mata_pelajaran = '';
    filters.status = '';
    filters.jenis = '';
    applyFilters();
};

const handleSearch = (query) => {
    filters.search = query;
    applyFilters();
};

const refreshData = () => {
    router.reload({ only: ['ujians', 'statistics'] });
};

const viewDetail = (id) => {
    router.visit(route('ujian.show', id));
};

const editUjian = (id) => {
    router.visit(route('ujian.edit', id));
};

const manageSoal = (id) => {
    router.visit(route('ujian.soal', id));
};

const viewResults = (id) => {
    router.visit(route('ujian.results', id));
};

const publishUjian = async (ujian) => {
    selectedUjian.value = ujian;
    
    // Validate before showing modal
    try {
        const response = await axios.get(route('ujian.validate', ujian.id));
        publishValidation.errors = response.data.errors || [];
        showPublishModal.value = true;
    } catch (error) {
        toast.error('Gagal memvalidasi ujian');
    }
};

const handlePublish = () => {
    if (publishValidation.errors.length > 0) {
        return;
    }

    publishing.value = true;

    router.post(route('ujian.publish', selectedUjian.value.id), {}, {
        onSuccess: () => {
            toast.success('Ujian berhasil dipublish');
            showPublishModal.value = false;
            selectedUjian.value = null;
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal publish ujian');
        },
        onFinish: () => {
            publishing.value = false;
        }
    });
};

const unpublishUjian = (ujian) => {
    if (!confirm(`Unpublish ujian "${ujian.judul}"?`)) return;

    router.post(route('ujian.unpublish', ujian.id), {}, {
        onSuccess: () => {
            toast.success('Ujian berhasil di-unpublish');
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal unpublish ujian');
        }
    });
};

const duplicateUjian = (ujian) => {
    if (!confirm(`Duplikat ujian "${ujian.judul}"?`)) return;

    router.post(route('ujian.duplicate', ujian.id), {}, {
        onSuccess: () => {
            toast.success('Ujian berhasil diduplikat');
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal duplikat ujian');
        }
    });
};

const confirmDelete = (ujian) => {
    selectedUjian.value = ujian;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    deleting.value = true;

    router.delete(route('ujian.destroy', selectedUjian.value.id), {
        onSuccess: () => {
            toast.success('Ujian berhasil dihapus');
            showDeleteModal.value = false;
            selectedUjian.value = null;
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal menghapus ujian');
        },
        onFinish: () => {
            deleting.value = false;
        }
    });
};

const exportData = () => {
    const params = new URLSearchParams(filters);
    window.location.href = route('ujian.export') + '?' + params.toString();
};
</script>

<style scoped>
.small-box {
    border-radius: 0.5rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    display: block;
    margin-bottom: 20px;
    position: relative;
}

.small-box > .inner {
    padding: 10px;
}

.small-box h3 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0 0 10px;
    padding: 0;
    white-space: nowrap;
}

.small-box p {
    font-size: 1rem;
    margin: 0;
}

.small-box .icon {
    color: rgba(0,0,0,.15);
    z-index: 0;
}

.small-box .icon > i {
    font-size: 70px;
    position: absolute;
    right: 15px;
    top: 15px;
    transition: transform .3s linear;
}

.small-box:hover .icon > i {
    transform: scale(1.1);
}

.text-sm {
    font-size: 0.875rem;
}

.text-sm > div {
    margin-bottom: 0.25rem;
}
</style>
