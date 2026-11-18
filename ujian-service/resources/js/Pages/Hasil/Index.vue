<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Hasil Ujian</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="success"
                            @click="exportData"
                        >
                            <i class="fas fa-download mr-2"></i>
                            Export Excel
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
                                v-model="filters.ujian_id"
                                label="Ujian"
                                :options="ujianOptions"
                                placeholder="Semua Ujian"
                                @change="applyFilters"
                            />
                        </div>
                        <div class="col-md-3">
                            <Select
                                v-model="filters.periode_id"
                                label="Periode"
                                :options="periodeOptions"
                                placeholder="Semua Periode"
                                @change="applyFilters"
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
                            <Input
                                v-model="filters.nama_siswa"
                                label="Nama Siswa"
                                placeholder="Cari siswa..."
                                @input="debounceFilter"
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

        <!-- Statistics -->
        <div class="row mb-3">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ statistics.total || 0 }}</h3>
                        <p>Total Peserta</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ statistics.selesai || 0 }}</h3>
                        <p>Selesai</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ statistics.sedang_mengerjakan || 0 }}</h3>
                        <p>Sedang Mengerjakan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ statistics.rata_rata || 0 }}</h3>
                        <p>Rata-rata Nilai</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <Card>
                    <DataTable
                        :data="results"
                        :columns="columns"
                        :loading="loading"
                        @search="handleSearch"
                    >
                        <template #header-actions>
                            <Button
                                variant="success"
                                size="sm"
                                @click="refreshData"
                            >
                                <i class="fas fa-sync mr-2"></i>
                                Refresh
                            </Button>
                        </template>

                        <template #cell-siswa="{ row }">
                            <div>
                                <strong>{{ row.siswa?.nama }}</strong>
                                <br>
                                <small class="text-muted">NIS: {{ row.siswa?.nis }}</small>
                            </div>
                        </template>

                        <template #cell-ujian="{ row }">
                            <div>
                                <strong>{{ row.ujian?.judul }}</strong>
                                <br>
                                <small class="text-muted">{{ row.ujian?.mata_pelajaran }}</small>
                            </div>
                        </template>

                        <template #cell-waktu="{ row }">
                            <div class="text-sm">
                                <div>
                                    <i class="fas fa-calendar text-primary mr-1"></i>
                                    {{ formatDate(row.tanggal_mulai) }}
                                </div>
                                <div>
                                    <i class="fas fa-clock text-info mr-1"></i>
                                    {{ formatTime(row.tanggal_mulai) }}
                                </div>
                                <div v-if="row.durasi_pengerjaan">
                                    <i class="fas fa-hourglass text-warning mr-1"></i>
                                    {{ formatDuration(row.durasi_pengerjaan) }}
                                </div>
                            </div>
                        </template>

                        <template #cell-progress="{ row }">
                            <div class="progress-info">
                                <div class="progress mb-1" style="height: 20px;">
                                    <div
                                        class="progress-bar"
                                        :class="getProgressClass(row.progress)"
                                        :style="{ width: row.progress + '%' }"
                                    >
                                        {{ row.progress }}%
                                    </div>
                                </div>
                                <small class="text-muted">
                                    {{ row.jumlah_dijawab }}/{{ row.jumlah_soal }} soal
                                </small>
                            </div>
                        </template>

                        <template #cell-nilai="{ row }">
                            <div v-if="row.nilai !== null" class="text-center">
                                <h4 class="mb-0" :class="getNilaiClass(row.nilai, row.ujian?.passing_grade)">
                                    {{ row.nilai }}
                                </h4>
                                <Badge :variant="row.nilai >= row.ujian?.passing_grade ? 'success' : 'danger'">
                                    {{ row.nilai >= row.ujian?.passing_grade ? 'LULUS' : 'TIDAK LULUS' }}
                                </Badge>
                            </div>
                            <div v-else class="text-center text-muted">
                                <i class="fas fa-minus"></i>
                            </div>
                        </template>

                        <template #cell-status="{ row }">
                            <Badge :variant="getStatusVariant(row.status)">
                                {{ getStatusLabel(row.status) }}
                            </Badge>
                        </template>

                        <template #actions="{ row }">
                            <Dropdown trigger-text="Aksi" variant="primary" size="sm">
                                <a class="dropdown-item" href="#" @click.prevent="viewDetail(row.id)">
                                    <i class="fas fa-eye mr-2"></i> Lihat Detail
                                </a>
                                <a 
                                    v-if="row.status === 'selesai' && row.has_essay"
                                    class="dropdown-item" 
                                    href="#" 
                                    @click.prevent="gradeEssay(row.id)"
                                >
                                    <i class="fas fa-pen mr-2"></i> Nilai Essay
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="downloadPDF(row.id)">
                                    <i class="fas fa-file-pdf mr-2"></i> Download PDF
                                </a>
                                <div class="dropdown-divider"></div>
                                <a 
                                    v-if="row.status === 'sedang_mengerjakan'"
                                    class="dropdown-item text-warning" 
                                    href="#" 
                                    @click.prevent="forceSubmit(row)"
                                >
                                    <i class="fas fa-stop-circle mr-2"></i> Force Submit
                                </a>
                                <a 
                                    class="dropdown-item text-danger" 
                                    href="#" 
                                    @click.prevent="confirmReset(row)"
                                >
                                    <i class="fas fa-redo mr-2"></i> Reset Ujian
                                </a>
                            </Dropdown>
                        </template>
                    </DataTable>
                </Card>
            </div>
        </div>

        <!-- Force Submit Modal -->
        <Modal
            v-model:show="showForceSubmitModal"
            title="Konfirmasi Force Submit"
            confirm-text="Ya, Submit"
            confirm-variant="warning"
            :confirm-loading="submitting"
            @confirm="handleForceSubmit"
        >
            <Alert variant="warning" icon>
                Siswa sedang mengerjakan ujian. Force submit akan menghentikan ujian dan menilai jawaban yang sudah dikerjakan.
            </Alert>
            <p>Apakah Anda yakin ingin force submit ujian <strong>{{ selectedResult?.siswa?.nama }}</strong>?</p>
        </Modal>

        <!-- Reset Modal -->
        <Modal
            v-model:show="showResetModal"
            title="Konfirmasi Reset"
            confirm-text="Ya, Reset"
            confirm-variant="danger"
            :confirm-loading="resetting"
            @confirm="handleReset"
        >
            <Alert variant="danger" icon>
                <strong>Perhatian!</strong> Tindakan ini tidak dapat dibatalkan.
            </Alert>
            <p>Reset ujian akan menghapus semua jawaban dan nilai. Siswa dapat mengerjakan ujian dari awal.</p>
            <p>Apakah Anda yakin ingin reset ujian <strong>{{ selectedResult?.siswa?.nama }}</strong>?</p>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, DataTable, Button, Badge, Dropdown, Modal, Alert, Input, Select, useToast } from '@/Components';

const props = defineProps({
    results: {
        type: Array,
        required: true
    },
    ujians: {
        type: Array,
        default: () => []
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
const showForceSubmitModal = ref(false);
const showResetModal = ref(false);
const selectedResult = ref(null);
const submitting = ref(false);
const resetting = ref(false);

const filters = reactive({
    ujian_id: '',
    periode_id: '',
    status: '',
    nama_siswa: ''
});

const ujianOptions = computed(() => {
    return props.ujians.map(u => ({
        value: u.id,
        label: `${u.judul} - ${u.mata_pelajaran}`
    }));
});

const periodeOptions = computed(() => {
    return props.periodes.map(p => ({
        value: p.id,
        label: `${p.nama} (${p.tahun_ajaran})`
    }));
});

const statusOptions = [
    { value: 'belum_mulai', label: 'Belum Mulai' },
    { value: 'sedang_mengerjakan', label: 'Sedang Mengerjakan' },
    { value: 'selesai', label: 'Selesai' }
];

const columns = [
    { key: 'siswa', label: 'Siswa', sortable: true },
    { key: 'ujian', label: 'Ujian', sortable: false },
    { key: 'waktu', label: 'Waktu', sortable: false },
    { key: 'progress', label: 'Progress', sortable: false },
    { key: 'nilai', label: 'Nilai', sortable: true },
    { key: 'status', label: 'Status', sortable: true }
];

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const formatTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatDuration = (minutes) => {
    if (!minutes) return '-';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return hours > 0 ? `${hours}j ${mins}m` : `${mins}m`;
};

const getProgressClass = (progress) => {
    if (progress >= 100) return 'bg-success';
    if (progress >= 50) return 'bg-info';
    if (progress >= 25) return 'bg-warning';
    return 'bg-danger';
};

const getNilaiClass = (nilai, passingGrade) => {
    if (nilai >= passingGrade) return 'text-success';
    return 'text-danger';
};

const getStatusVariant = (status) => {
    const variants = {
        'belum_mulai': 'secondary',
        'sedang_mengerjakan': 'warning',
        'selesai': 'success'
    };
    return variants[status] || 'secondary';
};

const getStatusLabel = (status) => {
    const labels = {
        'belum_mulai': 'Belum Mulai',
        'sedang_mengerjakan': 'Sedang Mengerjakan',
        'selesai': 'Selesai'
    };
    return labels[status] || status;
};

let filterTimeout = null;
const debounceFilter = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    router.get(route('hasil.index'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    filters.ujian_id = '';
    filters.periode_id = '';
    filters.status = '';
    filters.nama_siswa = '';
    applyFilters();
};

const handleSearch = (query) => {
    filters.search = query;
    applyFilters();
};

const refreshData = () => {
    router.reload({ only: ['results', 'statistics'] });
};

const viewDetail = (id) => {
    router.visit(route('hasil.show', id));
};

const gradeEssay = (id) => {
    router.visit(route('hasil.grade', id));
};

const downloadPDF = (id) => {
    window.location.href = route('hasil.pdf', id);
};

const forceSubmit = (result) => {
    selectedResult.value = result;
    showForceSubmitModal.value = true;
};

const handleForceSubmit = () => {
    submitting.value = true;

    router.post(route('hasil.force-submit', selectedResult.value.id), {}, {
        onSuccess: () => {
            toast.success('Ujian berhasil di-submit');
            showForceSubmitModal.value = false;
            selectedResult.value = null;
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal submit ujian');
        },
        onFinish: () => {
            submitting.value = false;
        }
    });
};

const confirmReset = (result) => {
    selectedResult.value = result;
    showResetModal.value = true;
};

const handleReset = () => {
    resetting.value = true;

    router.post(route('hasil.reset', selectedResult.value.id), {}, {
        onSuccess: () => {
            toast.success('Ujian berhasil direset');
            showResetModal.value = false;
            selectedResult.value = null;
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal reset ujian');
        },
        onFinish: () => {
            resetting.value = false;
        }
    });
};

const exportData = () => {
    const params = new URLSearchParams(filters);
    window.location.href = route('hasil.export') + '?' + params.toString();
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

.progress-info {
    min-width: 150px;
}
</style>
