<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Bank Soal</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="primary"
                            @click="router.visit(route('bank-soal.create'))"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Soal
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
                                v-model="filters.kategori"
                                label="Kategori"
                                :options="kategoriOptions"
                                placeholder="Semua Kategori"
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
                                v-model="filters.tipe"
                                label="Tipe Soal"
                                :options="tipeSoalOptions"
                                placeholder="Semua Tipe"
                                @change="applyFilters"
                            />
                        </div>
                        <div class="col-md-2">
                            <Select
                                v-model="filters.tingkat_kesulitan"
                                label="Tingkat Kesulitan"
                                :options="tingkatOptions"
                                placeholder="Semua Tingkat"
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

        <!-- Statistics -->
        <div class="row mb-3">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ statistics.total || 0 }}</h3>
                        <p>Total Soal</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ statistics.pilihan_ganda || 0 }}</h3>
                        <p>Pilihan Ganda</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ statistics.essay || 0 }}</h3>
                        <p>Essay</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-pen"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ statistics.benar_salah || 0 }}</h3>
                        <p>Benar/Salah</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="row mb-3">
            <div class="col-12">
                <Card>
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-folder mr-2"></i>
                            Kategori
                        </h5>
                    </template>
                    <div class="kategori-list">
                        <Button
                            v-for="kategori in categories"
                            :key="kategori.id"
                            :variant="filters.kategori === kategori.id ? 'primary' : 'outline-primary'"
                            size="sm"
                            class="mr-2 mb-2"
                            @click="filterByKategori(kategori.id)"
                        >
                            {{ kategori.nama }}
                            <Badge variant="light" class="ml-2">{{ kategori.jumlah_soal }}</Badge>
                        </Button>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <Card>
                    <DataTable
                        :data="soals"
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

                        <template #cell-pertanyaan="{ row }">
                            <div>
                                <div class="soal-preview" v-html="truncate(row.pertanyaan, 100)"></div>
                                <div class="mt-1">
                                    <Badge :variant="getTipeSoalVariant(row.tipe)" size="sm">
                                        {{ getTipeSoalLabel(row.tipe) }}
                                    </Badge>
                                    <Badge v-if="row.kategori" variant="secondary" size="sm" class="ml-1">
                                        {{ row.kategori.nama }}
                                    </Badge>
                                </div>
                            </div>
                        </template>

                        <template #cell-mata_pelajaran="{ row }">
                            <div>
                                <strong>{{ row.mata_pelajaran }}</strong>
                                <br>
                                <small class="text-muted">{{ row.topik || '-' }}</small>
                            </div>
                        </template>

                        <template #cell-tingkat_kesulitan="{ row }">
                            <Badge :variant="getTingkatVariant(row.tingkat_kesulitan)">
                                {{ getTingkatLabel(row.tingkat_kesulitan) }}
                            </Badge>
                        </template>

                        <template #cell-bobot="{ row }">
                            <div class="text-center">
                                <i class="fas fa-star text-warning mr-1"></i>
                                <strong>{{ row.bobot }}</strong>
                            </div>
                        </template>

                        <template #cell-penggunaan="{ row }">
                            <div class="text-center">
                                <Badge variant="info">
                                    {{ row.jumlah_penggunaan || 0 }}x
                                </Badge>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <Dropdown trigger-text="Aksi" variant="primary" size="sm">
                                <a class="dropdown-item" href="#" @click.prevent="viewDetail(row)">
                                    <i class="fas fa-eye mr-2"></i> Preview
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="editSoal(row.id)">
                                    <i class="fas fa-edit mr-2"></i> Edit
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="duplicateSoal(row)">
                                    <i class="fas fa-copy mr-2"></i> Duplikat
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" @click.prevent="viewUsage(row)">
                                    <i class="fas fa-chart-bar mr-2"></i> Lihat Penggunaan
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

        <!-- Preview Modal -->
        <Modal
            v-model:show="showPreviewModal"
            title="Preview Soal"
            size="lg"
            :show-footer="false"
        >
            <div v-if="previewSoal" class="soal-preview-full">
                <div class="mb-3">
                    <Badge :variant="getTipeSoalVariant(previewSoal.tipe)">
                        {{ getTipeSoalLabel(previewSoal.tipe) }}
                    </Badge>
                    <Badge :variant="getTingkatVariant(previewSoal.tingkat_kesulitan)" class="ml-2">
                        {{ getTingkatLabel(previewSoal.tingkat_kesulitan) }}
                    </Badge>
                    <Badge variant="warning" class="ml-2">
                        {{ previewSoal.bobot }} poin
                    </Badge>
                </div>

                <div class="info-section mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Mata Pelajaran:</strong> {{ previewSoal.mata_pelajaran }}
                        </div>
                        <div class="col-md-6">
                            <strong>Topik:</strong> {{ previewSoal.topik || '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Kategori:</strong> {{ previewSoal.kategori?.nama || '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Digunakan:</strong> {{ previewSoal.jumlah_penggunaan || 0 }}x
                        </div>
                    </div>
                </div>

                <div class="soal-pertanyaan mb-3" v-html="previewSoal.pertanyaan"></div>

                <div v-if="previewSoal.media_url" class="soal-media mb-3">
                    <img
                        v-if="isImage(previewSoal.media_url)"
                        :src="previewSoal.media_url"
                        alt="Media"
                        class="img-fluid"
                    >
                </div>

                <div v-if="previewSoal.tipe === 'pilihan_ganda'" class="soal-opsi">
                    <h6>Opsi Jawaban:</h6>
                    <div
                        v-for="(opsi, index) in previewSoal.opsi"
                        :key="index"
                        class="opsi-item"
                        :class="{ 'correct': opsi.is_correct }"
                    >
                        <span class="opsi-label">{{ String.fromCharCode(65 + index) }}.</span>
                        <span class="opsi-text">{{ opsi.text }}</span>
                        <i v-if="opsi.is_correct" class="fas fa-check-circle text-success ml-2"></i>
                    </div>
                </div>

                <div v-if="previewSoal.tipe === 'benar_salah'" class="soal-opsi">
                    <h6>Jawaban yang Benar:</h6>
                    <Badge :variant="previewSoal.jawaban_benar ? 'success' : 'danger'" size="lg">
                        {{ previewSoal.jawaban_benar ? 'BENAR' : 'SALAH' }}
                    </Badge>
                </div>

                <div v-if="previewSoal.pembahasan" class="soal-pembahasan mt-3">
                    <h6>Pembahasan:</h6>
                    <p class="text-muted">{{ previewSoal.pembahasan }}</p>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation -->
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
            <p>Apakah Anda yakin ingin menghapus soal ini dari bank soal?</p>
            <p v-if="selectedSoal?.jumlah_penggunaan > 0" class="text-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Soal ini sedang digunakan di {{ selectedSoal.jumlah_penggunaan }} ujian.
            </p>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, DataTable, Button, Badge, Dropdown, Modal, Alert, Input, Select, useToast } from '@/Components';

const props = defineProps({
    soals: {
        type: Array,
        required: true
    },
    categories: {
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
const showPreviewModal = ref(false);
const showDeleteModal = ref(false);
const previewSoal = ref(null);
const selectedSoal = ref(null);
const deleting = ref(false);

const filters = reactive({
    kategori: '',
    mata_pelajaran: '',
    tipe: '',
    tingkat_kesulitan: ''
});

const kategoriOptions = props.categories.map(k => ({
    value: k.id,
    label: k.nama
}));

const tipeSoalOptions = [
    { value: 'pilihan_ganda', label: 'Pilihan Ganda' },
    { value: 'benar_salah', label: 'Benar/Salah' },
    { value: 'essay', label: 'Essay' }
];

const tingkatOptions = [
    { value: 'mudah', label: 'Mudah' },
    { value: 'sedang', label: 'Sedang' },
    { value: 'sulit', label: 'Sulit' }
];

const columns = [
    { key: 'pertanyaan', label: 'Pertanyaan', sortable: false },
    { key: 'mata_pelajaran', label: 'Mata Pelajaran', sortable: true },
    { key: 'tingkat_kesulitan', label: 'Tingkat', sortable: true },
    { key: 'bobot', label: 'Bobot', sortable: true },
    { key: 'penggunaan', label: 'Penggunaan', sortable: true }
];

const getTipeSoalVariant = (tipe) => {
    const variants = {
        'pilihan_ganda': 'primary',
        'benar_salah': 'info',
        'essay': 'warning'
    };
    return variants[tipe] || 'secondary';
};

const getTipeSoalLabel = (tipe) => {
    const labels = {
        'pilihan_ganda': 'Pilihan Ganda',
        'benar_salah': 'Benar/Salah',
        'essay': 'Essay'
    };
    return labels[tipe] || tipe;
};

const getTingkatVariant = (tingkat) => {
    const variants = {
        'mudah': 'success',
        'sedang': 'warning',
        'sulit': 'danger'
    };
    return variants[tingkat] || 'secondary';
};

const getTingkatLabel = (tingkat) => {
    const labels = {
        'mudah': 'Mudah',
        'sedang': 'Sedang',
        'sulit': 'Sulit'
    };
    return labels[tingkat] || tingkat;
};

const truncate = (text, length) => {
    if (!text) return '';
    const stripped = text.replace(/<[^>]*>/g, '');
    return stripped.length > length ? stripped.substring(0, length) + '...' : stripped;
};

const isImage = (url) => {
    return /\.(jpg|jpeg|png|gif|webp)$/i.test(url);
};

let filterTimeout = null;
const debounceFilter = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    router.get(route('bank-soal.index'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    filters.kategori = '';
    filters.mata_pelajaran = '';
    filters.tipe = '';
    filters.tingkat_kesulitan = '';
    applyFilters();
};

const filterByKategori = (kategoriId) => {
    filters.kategori = filters.kategori === kategoriId ? '' : kategoriId;
    applyFilters();
};

const handleSearch = (query) => {
    filters.search = query;
    applyFilters();
};

const refreshData = () => {
    router.reload({ only: ['soals', 'statistics'] });
};

const viewDetail = (soal) => {
    previewSoal.value = soal;
    showPreviewModal.value = true;
};

const editSoal = (id) => {
    router.visit(route('bank-soal.edit', id));
};

const duplicateSoal = (soal) => {
    if (!confirm(`Duplikat soal ini?`)) return;

    router.post(route('bank-soal.duplicate', soal.id), {}, {
        onSuccess: () => {
            toast.success('Soal berhasil diduplikat');
        },
        onError: () => {
            toast.error('Gagal duplikat soal');
        }
    });
};

const viewUsage = (soal) => {
    router.visit(route('bank-soal.usage', soal.id));
};

const confirmDelete = (soal) => {
    selectedSoal.value = soal;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    deleting.value = true;

    router.delete(route('bank-soal.destroy', selectedSoal.value.id), {
        onSuccess: () => {
            toast.success('Soal berhasil dihapus');
            showDeleteModal.value = false;
            selectedSoal.value = null;
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal menghapus soal');
        },
        onFinish: () => {
            deleting.value = false;
        }
    });
};

const exportData = () => {
    const params = new URLSearchParams(filters);
    window.location.href = route('bank-soal.export') + '?' + params.toString();
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

.kategori-list {
    display: flex;
    flex-wrap: wrap;
}

.soal-preview {
    line-height: 1.6;
}

.soal-preview-full .soal-pertanyaan {
    font-size: 1.1rem;
    line-height: 1.8;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}

.info-section {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}

.soal-opsi {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.opsi-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border-radius: 0.25rem;
    border: 2px solid transparent;
}

.opsi-item.correct {
    background: #d4edda;
    border-color: #28a745;
}

.opsi-label {
    font-weight: 600;
    margin-right: 0.75rem;
}

.opsi-text {
    flex: 1;
}

.soal-pembahasan {
    padding: 1rem;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 0.25rem;
}
</style>
