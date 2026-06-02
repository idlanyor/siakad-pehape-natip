<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Kelola Soal: {{ ujian.judul }}</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="secondary"
                            @click="router.visit(route('ujian.show', ujian.id))"
                        >
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </Button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Action Bar -->
        <div class="row mb-3">
            <div class="col-12">
                <Card>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <Badge variant="info" size="lg">
                                {{ soals.length }} Soal
                            </Badge>
                            <Badge variant="warning" size="lg" class="ml-2">
                                Total: {{ totalBobot }} Poin
                            </Badge>
                        </div>
                        <div>
                            <Button
                                variant="success"
                                @click="showImportModal = true"
                            >
                                <i class="fas fa-file-import mr-2"></i>
                                Import dari Bank Soal
                            </Button>
                            <Button
                                variant="primary"
                                class="ml-2"
                                @click="openCreateModal"
                            >
                                <i class="fas fa-plus mr-2"></i>
                                Tambah Soal Baru
                            </Button>
                        </div>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Soal List -->
        <div class="row">
            <div class="col-12">
                <Card>
                    <template #header>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list mr-2"></i>
                                Daftar Soal
                            </h5>
                            <div>
                                <Button
                                    variant="info"
                                    size="sm"
                                    @click="toggleReorderMode"
                                >
                                    <i class="fas fa-sort mr-2"></i>
                                    {{ reorderMode ? 'Selesai Reorder' : 'Reorder Soal' }}
                                </Button>
                            </div>
                        </div>
                    </template>

                    <div v-if="soals.length > 0" class="soal-list">
                        <draggable
                            v-model="soals"
                            :disabled="!reorderMode"
                            item-key="id"
                            @end="handleReorder"
                        >
                            <template #item="{ element: soal, index }">
                                <div
                                    class="soal-item"
                                    :class="{ 'reorder-mode': reorderMode }"
                                >
                                    <div class="soal-header">
                                        <div class="soal-number">
                                            <i v-if="reorderMode" class="fas fa-grip-vertical text-muted mr-2"></i>
                                            <Badge variant="primary" size="lg">{{ index + 1 }}</Badge>
                                        </div>
                                        <div class="soal-meta">
                                            <Badge :variant="getTipeSoalVariant(soal.tipe)">
                                                {{ getTipeSoalLabel(soal.tipe) }}
                                            </Badge>
                                            <span class="ml-2">
                                                <i class="fas fa-star text-warning mr-1"></i>
                                                {{ soal.bobot }} poin
                                            </span>
                                        </div>
                                        <div v-if="!reorderMode" class="soal-actions ml-auto">
                                            <Button
                                                variant="info"
                                                size="sm"
                                                @click="viewSoal(soal)"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </Button>
                                            <Button
                                                variant="primary"
                                                size="sm"
                                                class="ml-1"
                                                @click="editSoal(soal)"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </Button>
                                            <Button
                                                variant="secondary"
                                                size="sm"
                                                class="ml-1"
                                                @click="duplicateSoal(soal)"
                                            >
                                                <i class="fas fa-copy"></i>
                                            </Button>
                                            <Button
                                                variant="danger"
                                                size="sm"
                                                class="ml-1"
                                                @click="confirmDeleteSoal(soal)"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </Button>
                                        </div>
                                    </div>
                                    <div class="soal-content">
                                        <div class="soal-pertanyaan" v-html="truncate(soal.pertanyaan, 200)"></div>
                                        
                                        <!-- Quick Preview -->
                                        <div v-if="soal.tipe === 'pilihan_ganda' && soal.opsi" class="soal-preview mt-2">
                                            <small class="text-muted">
                                                {{ soal.opsi.length }} opsi jawaban
                                            </small>
                                        </div>
                                        <div v-if="soal.media" class="soal-preview mt-2">
                                            <Badge variant="info" size="sm">
                                                <i class="fas fa-image mr-1"></i>
                                                Ada media
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </div>
                    <div v-else class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">Belum ada soal untuk ujian ini</p>
                        <Button
                            variant="primary"
                            @click="openCreateModal"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Soal Pertama
                        </Button>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal
            v-model:show="showSoalModal"
            :title="editMode ? 'Edit Soal' : 'Tambah Soal Baru'"
            size="xl"
            :confirm-text="editMode ? 'Update' : 'Simpan'"
            :confirm-loading="form.processing"
            @confirm="handleSaveSoal"
        >
            <Tabs v-model="activeTab">
                <TabPane name="pertanyaan" label="Pertanyaan">
                    <div class="row">
                        <div class="col-md-12">
                            <Select
                                v-model="form.data.tipe"
                                label="Tipe Soal"
                                :options="tipeSoalOptions"
                                required
                            />
                        </div>

                        <div class="col-md-12">
                            <Textarea
                                v-model="form.data.pertanyaan"
                                label="Pertanyaan"
                                placeholder="Tulis pertanyaan soal..."
                                :rows="6"
                                :error="form.errors.pertanyaan"
                                required
                            />
                        </div>

                        <div class="col-md-6">
                            <Input
                                v-model.number="form.data.bobot"
                                type="number"
                                label="Bobot Nilai"
                                placeholder="10"
                                :error="form.errors.bobot"
                                required
                                min="1"
                            />
                        </div>

                        <div class="col-md-6">
                            <Input
                                v-model.number="form.data.urutan"
                                type="number"
                                label="Urutan"
                                placeholder="1"
                                :error="form.errors.urutan"
                                min="1"
                            />
                        </div>
                    </div>
                </TabPane>

                <TabPane name="jawaban" label="Jawaban">
                    <!-- Pilihan Ganda -->
                    <div v-if="form.data.tipe === 'pilihan_ganda'">
                        <div class="mb-3">
                            <Button
                                variant="success"
                                size="sm"
                                @click="addOpsi"
                            >
                                <i class="fas fa-plus mr-2"></i>
                                Tambah Opsi
                            </Button>
                        </div>

                        <div
                            v-for="(opsi, index) in form.data.opsi"
                            :key="index"
                            class="opsi-input-group mb-3"
                        >
                            <div class="d-flex align-items-start gap-2">
                                <div class="opsi-label">{{ String.fromCharCode(65 + index) }}.</div>
                                <div class="flex-grow-1">
                                    <Textarea
                                        v-model="opsi.text"
                                        placeholder="Tulis opsi jawaban..."
                                        :rows="2"
                                    />
                                </div>
                                <div class="opsi-actions">
                                    <Checkbox
                                        v-model="opsi.is_correct"
                                        label="Benar"
                                        @change="handleCorrectChange(index)"
                                    />
                                    <Button
                                        variant="danger"
                                        size="sm"
                                        class="ml-2"
                                        @click="removeOpsi(index)"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Benar/Salah -->
                    <div v-if="form.data.tipe === 'benar_salah'">
                        <div class="form-group">
                            <label>Jawaban yang Benar</label>
                            <div>
                                <Radio
                                    v-model="form.data.jawaban_benar"
                                    :value="true"
                                    label="Benar"
                                    name="jawaban_benar"
                                />
                                <Radio
                                    v-model="form.data.jawaban_benar"
                                    :value="false"
                                    label="Salah"
                                    name="jawaban_benar"
                                    class="ml-3"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Essay -->
                    <div v-if="form.data.tipe === 'essay'">
                        <Alert variant="info" icon>
                            Soal essay akan dinilai secara manual oleh guru.
                            Anda dapat memberikan panduan penilaian di tab Pembahasan.
                        </Alert>
                    </div>
                </TabPane>

                <TabPane name="pembahasan" label="Pembahasan">
                    <Textarea
                        v-model="form.data.pembahasan"
                        label="Pembahasan (Opsional)"
                        placeholder="Tulis pembahasan atau penjelasan jawaban..."
                        :rows="8"
                    />
                </TabPane>

                <TabPane name="media" label="Media">
                    <div class="media-upload">
                        <Alert variant="info" icon class="mb-3">
                            Upload gambar, audio, atau video untuk melengkapi soal.
                            Maksimal ukuran file: 5MB
                        </Alert>

                        <div v-if="form.data.media_url" class="current-media mb-3">
                            <h6>Media Saat Ini:</h6>
                            <img
                                v-if="isImage(form.data.media_url)"
                                :src="form.data.media_url"
                                alt="Media"
                                class="img-fluid"
                                style="max-height: 300px;"
                            >
                            <div v-else class="media-info">
                                <i class="fas fa-file fa-3x text-muted"></i>
                                <p class="mt-2">{{ form.data.media_name }}</p>
                            </div>
                            <Button
                                variant="danger"
                                size="sm"
                                class="mt-2"
                                @click="removeMedia"
                            >
                                <i class="fas fa-trash mr-2"></i>
                                Hapus Media
                            </Button>
                        </div>

                        <input
                            ref="mediaInput"
                            type="file"
                            accept="image/*,audio/*,video/*"
                            class="d-none"
                            @change="handleMediaUpload"
                        >
                        <Button
                            variant="primary"
                            @click="$refs.mediaInput.click()"
                        >
                            <i class="fas fa-upload mr-2"></i>
                            {{ form.data.media_url ? 'Ganti Media' : 'Upload Media' }}
                        </Button>
                    </div>
                </TabPane>
            </Tabs>
        </Modal>

        <!-- Import from Bank Modal -->
        <Modal
            v-model:show="showImportModal"
            title="Import dari Bank Soal"
            size="lg"
            confirm-text="Import Soal Terpilih"
            :confirm-loading="importing"
            @confirm="handleImport"
        >
            <div class="mb-3">
                <Input
                    v-model="importSearch"
                    placeholder="Cari soal..."
                    @input="searchBankSoal"
                >
                    <template #prepend>
                        <i class="fas fa-search"></i>
                    </template>
                </Input>
            </div>

            <div class="bank-soal-list">
                <div
                    v-for="soal in bankSoals"
                    :key="soal.id"
                    class="bank-soal-item"
                >
                    <Checkbox
                        v-model="selectedBankSoals"
                        :value="soal.id"
                    />
                    <div class="bank-soal-content">
                        <div class="d-flex justify-content-between">
                            <Badge :variant="getTipeSoalVariant(soal.tipe)">
                                {{ getTipeSoalLabel(soal.tipe) }}
                            </Badge>
                            <span class="text-muted">{{ soal.bobot }} poin</span>
                        </div>
                        <div class="mt-2" v-html="truncate(soal.pertanyaan, 150)"></div>
                    </div>
                </div>
            </div>

            <div v-if="selectedBankSoals.length > 0" class="mt-3">
                <Alert variant="success">
                    <strong>{{ selectedBankSoals.length }}</strong> soal dipilih
                </Alert>
            </div>
        </Modal>

        <!-- View Modal -->
        <Modal
            v-model:show="showViewModal"
            title="Preview Soal"
            size="lg"
            :show-footer="false"
        >
            <div v-if="viewingSoal" class="soal-preview-full">
                <div class="mb-3">
                    <Badge :variant="getTipeSoalVariant(viewingSoal.tipe)">
                        {{ getTipeSoalLabel(viewingSoal.tipe) }}
                    </Badge>
                    <Badge variant="warning" class="ml-2">
                        {{ viewingSoal.bobot }} poin
                    </Badge>
                </div>

                <div class="soal-pertanyaan mb-3" v-html="viewingSoal.pertanyaan"></div>

                <div v-if="viewingSoal.media_url" class="soal-media mb-3">
                    <img
                        v-if="isImage(viewingSoal.media_url)"
                        :src="viewingSoal.media_url"
                        alt="Media"
                        class="img-fluid"
                    >
                </div>

                <div v-if="viewingSoal.tipe === 'pilihan_ganda'" class="soal-opsi">
                    <h6>Opsi Jawaban:</h6>
                    <div
                        v-for="(opsi, index) in viewingSoal.opsi"
                        :key="index"
                        class="opsi-item"
                        :class="{ 'correct': opsi.is_correct }"
                    >
                        <span class="opsi-label">{{ String.fromCharCode(65 + index) }}.</span>
                        <span class="opsi-text">{{ opsi.text }}</span>
                        <i v-if="opsi.is_correct" class="fas fa-check-circle text-success ml-2"></i>
                    </div>
                </div>

                <div v-if="viewingSoal.tipe === 'benar_salah'" class="soal-opsi">
                    <h6>Jawaban yang Benar:</h6>
                    <Badge :variant="viewingSoal.jawaban_benar ? 'success' : 'danger'" size="lg">
                        {{ viewingSoal.jawaban_benar ? 'BENAR' : 'SALAH' }}
                    </Badge>
                </div>

                <div v-if="viewingSoal.pembahasan" class="soal-pembahasan mt-3">
                    <h6>Pembahasan:</h6>
                    <p class="text-muted">{{ viewingSoal.pembahasan }}</p>
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
            @confirm="handleDeleteSoal"
        >
            <Alert variant="warning" icon>
                Apakah Anda yakin ingin menghapus soal ini?
            </Alert>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    Card, Button, Badge, Modal, Alert, Input, Textarea, Select, 
    Checkbox, Radio, Tabs, TabPane, useForm, useToast 
} from '@/Components';

const props = defineProps({
    ujian: {
        type: Object,
        required: true
    },
    soals: {
        type: Array,
        default: () => []
    },
    bankSoals: {
        type: Array,
        default: () => []
    }
});

const toast = useToast();
const soals = ref([...props.soals]);
const reorderMode = ref(false);
const showSoalModal = ref(false);
const showImportModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const editMode = ref(false);
const activeTab = ref('pertanyaan');
const viewingSoal = ref(null);
const deletingSoal = ref(null);
const deleting = ref(false);
const importing = ref(false);
const importSearch = ref('');
const selectedBankSoals = ref([]);
const mediaInput = ref(null);

const tipeSoalOptions = [
    { value: 'pilihan_ganda', label: 'Pilihan Ganda' },
    { value: 'benar_salah', label: 'Benar/Salah' },
    { value: 'essay', label: 'Essay' }
];

const form = useForm({
    tipe: 'pilihan_ganda',
    pertanyaan: '',
    bobot: 10,
    urutan: soals.value.length + 1,
    opsi: [
        { text: '', is_correct: false },
        { text: '', is_correct: false },
        { text: '', is_correct: false },
        { text: '', is_correct: false }
    ],
    jawaban_benar: true,
    pembahasan: '',
    media_url: '',
    media_name: ''
});

const totalBobot = computed(() => {
    return soals.value.reduce((sum, soal) => sum + (soal.bobot || 0), 0);
});

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

const truncate = (text, length) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};

const isImage = (url) => {
    return /\.(jpg|jpeg|png|gif|webp)$/i.test(url);
};

const toggleReorderMode = () => {
    reorderMode.value = !reorderMode.value;
};

const handleReorder = () => {
    const order = soals.value.map((soal, index) => ({
        id: soal.id,
        urutan: index + 1
    }));

    router.post(route('ujian.soal.reorder', props.ujian.id), { order }, {
        onSuccess: () => {
            toast.success('Urutan soal berhasil diupdate');
        },
        onError: () => {
            toast.error('Gagal update urutan soal');
        }
    });
};

const openCreateModal = () => {
    editMode.value = false;
    form.reset();
    form.data.urutan = soals.value.length + 1;
    showSoalModal.value = true;
};

const editSoal = (soal) => {
    editMode.value = true;
    form.data = { ...soal };
    showSoalModal.value = true;
};

const viewSoal = (soal) => {
    viewingSoal.value = soal;
    showViewModal.value = true;
};

const duplicateSoal = (soal) => {
    router.post(route('ujian.soal.duplicate', [props.ujian.id, soal.id]), {}, {
        onSuccess: () => {
            toast.success('Soal berhasil diduplikat');
        },
        onError: () => {
            toast.error('Gagal duplikat soal');
        }
    });
};

const confirmDeleteSoal = (soal) => {
    deletingSoal.value = soal;
    showDeleteModal.value = true;
};

const handleDeleteSoal = () => {
    deleting.value = true;

    router.delete(route('ujian.soal.destroy', [props.ujian.id, deletingSoal.value.id]), {
        onSuccess: () => {
            toast.success('Soal berhasil dihapus');
            showDeleteModal.value = false;
            deletingSoal.value = null;
        },
        onError: () => {
            toast.error('Gagal menghapus soal');
        },
        onFinish: () => {
            deleting.value = false;
        }
    });
};

const handleSaveSoal = () => {
    const url = editMode.value
        ? route('ujian.soal.update', [props.ujian.id, form.data.id])
        : route('ujian.soal.store', props.ujian.id);

    const method = editMode.value ? 'put' : 'post';

    form[method](url, {
        onSuccess: () => {
            toast.success(`Soal berhasil ${editMode.value ? 'diupdate' : 'ditambahkan'}`);
            showSoalModal.value = false;
        },
        onError: () => {
            toast.error('Terjadi kesalahan. Periksa form Anda.');
        }
    });
};

const addOpsi = () => {
    form.data.opsi.push({ text: '', is_correct: false });
};

const removeOpsi = (index) => {
    if (form.data.opsi.length > 2) {
        form.data.opsi.splice(index, 1);
    } else {
        toast.error('Minimal harus ada 2 opsi');
    }
};

const handleCorrectChange = (index) => {
    // Only one correct answer for multiple choice
    form.data.opsi.forEach((opsi, i) => {
        if (i !== index) {
            opsi.is_correct = false;
        }
    });
};

const handleMediaUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        toast.error('Ukuran file maksimal 5MB');
        return;
    }

    // Upload logic here
    const formData = new FormData();
    formData.append('media', file);

    axios.post(route('ujian.soal.upload-media', props.ujian.id), formData)
        .then(response => {
            form.data.media_url = response.data.url;
            form.data.media_name = file.name;
            toast.success('Media berhasil diupload');
        })
        .catch(() => {
            toast.error('Gagal upload media');
        });
};

const removeMedia = () => {
    form.data.media_url = '';
    form.data.media_name = '';
};

const searchBankSoal = () => {
    // Implement search logic
};

const handleImport = () => {
    if (selectedBankSoals.value.length === 0) {
        toast.error('Pilih minimal 1 soal');
        return;
    }

    importing.value = true;

    router.post(route('ujian.soal.import', props.ujian.id), {
        soal_ids: selectedBankSoals.value
    }, {
        onSuccess: () => {
            toast.success(`${selectedBankSoals.value.length} soal berhasil diimport`);
            showImportModal.value = false;
            selectedBankSoals.value = [];
        },
        onError: () => {
            toast.error('Gagal import soal');
        },
        onFinish: () => {
            importing.value = false;
        }
    });
};
</script>

<style scoped>
.soal-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.soal-item {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    overflow: hidden;
    transition: all 0.3s;
}

.soal-item.reorder-mode {
    cursor: move;
}

.soal-item.reorder-mode:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.soal-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.soal-number {
    display: flex;
    align-items: center;
}

.soal-meta {
    display: flex;
    align-items: center;
}

.soal-actions {
    display: flex;
    gap: 0.25rem;
}

.soal-content {
    padding: 1rem;
}

.soal-pertanyaan {
    font-size: 1rem;
    line-height: 1.6;
}

.soal-preview {
    font-size: 0.875rem;
}

.opsi-input-group {
    display: flex;
    gap: 0.5rem;
}

.opsi-label {
    font-weight: 600;
    padding-top: 0.5rem;
}

.opsi-actions {
    display: flex;
    align-items: center;
}

.bank-soal-list {
    max-height: 400px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.bank-soal-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
}

.bank-soal-content {
    flex: 1;
}

.soal-preview-full .soal-pertanyaan {
    font-size: 1.1rem;
    line-height: 1.8;
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

.opsi-text {
    flex: 1;
}

.soal-pembahasan {
    padding: 1rem;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 0.25rem;
}

.media-upload {
    padding: 1rem;
}

.current-media {
    text-align: center;
}

.media-info {
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}
</style>
