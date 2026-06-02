<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ isEdit ? 'Edit' : 'Tambah' }} Soal ke Bank Soal</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="secondary"
                            @click="router.visit(route('bank-soal.index'))"
                        >
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </Button>
                    </div>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-lg-8">
                <form @submit.prevent="handleSubmit">
                    <Card>
                        <template #header>
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                Informasi Soal
                            </h5>
                        </template>

                        <Tabs v-model="activeTab">
                            <TabPane name="info" label="Info Dasar">
                                <div class="row">
                                    <div class="col-md-6">
                                        <Select
                                            v-model="form.data.kategori_id"
                                            label="Kategori"
                                            :options="kategoriOptions"
                                            :error="form.errors.kategori_id"
                                            required
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <Select
                                            v-model="form.data.tipe"
                                            label="Tipe Soal"
                                            :options="tipeSoalOptions"
                                            :error="form.errors.tipe"
                                            required
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <Input
                                            v-model="form.data.mata_pelajaran"
                                            label="Mata Pelajaran"
                                            placeholder="Contoh: Matematika"
                                            :error="form.errors.mata_pelajaran"
                                            required
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <Input
                                            v-model="form.data.topik"
                                            label="Topik/Bab"
                                            placeholder="Contoh: Aljabar"
                                            :error="form.errors.topik"
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <Select
                                            v-model="form.data.tingkat_kesulitan"
                                            label="Tingkat Kesulitan"
                                            :options="tingkatOptions"
                                            :error="form.errors.tingkat_kesulitan"
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

                                    <div class="col-md-12">
                                        <Textarea
                                            v-model="form.data.tags"
                                            label="Tags (pisahkan dengan koma)"
                                            placeholder="aljabar, persamaan, matematika"
                                            :rows="2"
                                            :error="form.errors.tags"
                                        />
                                        <small class="text-muted">
                                            Tags membantu pencarian soal
                                        </small>
                                    </div>
                                </div>
                            </TabPane>

                            <TabPane name="pertanyaan" label="Pertanyaan">
                                <div class="row">
                                    <div class="col-md-12">
                                        <Textarea
                                            v-model="form.data.pertanyaan"
                                            label="Pertanyaan"
                                            placeholder="Tulis pertanyaan soal..."
                                            :rows="8"
                                            :error="form.errors.pertanyaan"
                                            required
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
                                                    :error="form.errors[`opsi.${index}.text`]"
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
                                                    :disabled="form.data.opsi.length <= 2"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <Alert v-if="!hasCorrectAnswer" variant="warning" icon>
                                        Pilih satu opsi sebagai jawaban yang benar
                                    </Alert>
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
                                        Berikan panduan penilaian di tab Pembahasan.
                                    </Alert>
                                    <Textarea
                                        v-model="form.data.kunci_jawaban_essay"
                                        label="Kunci Jawaban / Poin Penting (Opsional)"
                                        placeholder="Tulis poin-poin penting yang harus ada dalam jawaban..."
                                        :rows="6"
                                    />
                                </div>
                            </TabPane>

                            <TabPane name="pembahasan" label="Pembahasan">
                                <Textarea
                                    v-model="form.data.pembahasan"
                                    label="Pembahasan (Opsional)"
                                    placeholder="Tulis pembahasan atau penjelasan jawaban..."
                                    :rows="10"
                                />
                                <small class="text-muted">
                                    Pembahasan akan ditampilkan kepada siswa setelah ujian selesai (jika diaktifkan)
                                </small>
                            </TabPane>

                            <TabPane name="media" label="Media">
                                <div class="media-upload">
                                    <Alert variant="info" icon class="mb-3">
                                        Upload gambar, audio, atau video untuk melengkapi soal.
                                        <br>
                                        <strong>Maksimal ukuran file: 5MB</strong>
                                    </Alert>

                                    <div v-if="form.data.media_url" class="current-media mb-3">
                                        <h6>Media Saat Ini:</h6>
                                        <div class="media-preview">
                                            <img
                                                v-if="isImage(form.data.media_url)"
                                                :src="form.data.media_url"
                                                alt="Media"
                                                class="img-fluid"
                                                style="max-height: 400px;"
                                            >
                                            <div v-else class="media-info">
                                                <i class="fas fa-file fa-3x text-muted"></i>
                                                <p class="mt-2">{{ form.data.media_name }}</p>
                                            </div>
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
                    </Card>

                    <!-- Actions -->
                    <div class="mt-4">
                        <Button
                            type="submit"
                            variant="primary"
                            :loading="form.processing"
                            :disabled="form.processing || !isFormValid"
                        >
                            <i class="fas fa-save mr-2"></i>
                            {{ isEdit ? 'Update' : 'Simpan' }} Soal
                        </Button>
                        <Button
                            type="button"
                            variant="secondary"
                            class="ml-2"
                            @click="router.visit(route('bank-soal.index'))"
                        >
                            Batal
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <Card variant="info" outline>
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb mr-2"></i>
                            Tips
                        </h5>
                    </template>
                    <ul class="tips-list mb-0">
                        <li>Tulis pertanyaan yang jelas dan spesifik</li>
                        <li>Untuk pilihan ganda, buat distractor yang masuk akal</li>
                        <li>Gunakan media untuk memperjelas pertanyaan</li>
                        <li>Berikan pembahasan yang detail</li>
                        <li>Gunakan tags untuk memudahkan pencarian</li>
                        <li>Pilih tingkat kesulitan yang sesuai</li>
                    </ul>
                </Card>

                <Card class="mt-3">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle mr-2"></i>
                            Tingkat Kesulitan
                        </h5>
                    </template>
                    <div class="tingkat-info">
                        <div class="tingkat-item">
                            <Badge variant="success">Mudah</Badge>
                            <p class="text-muted mb-0">Soal dasar, konsep fundamental</p>
                        </div>
                        <div class="tingkat-item">
                            <Badge variant="warning">Sedang</Badge>
                            <p class="text-muted mb-0">Soal aplikasi, pemahaman konsep</p>
                        </div>
                        <div class="tingkat-item">
                            <Badge variant="danger">Sulit</Badge>
                            <p class="text-muted mb-0">Soal analisis, pemecahan masalah kompleks</p>
                        </div>
                    </div>
                </Card>

                <Card class="mt-3">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Preview
                        </h5>
                    </template>
                    <div class="preview-info">
                        <div class="preview-item">
                            <span class="preview-label">Tipe:</span>
                            <Badge :variant="getTipeSoalVariant(form.data.tipe)">
                                {{ getTipeSoalLabel(form.data.tipe) }}
                            </Badge>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Tingkat:</span>
                            <Badge :variant="getTingkatVariant(form.data.tingkat_kesulitan)">
                                {{ getTingkatLabel(form.data.tingkat_kesulitan) }}
                            </Badge>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Bobot:</span>
                            <strong>{{ form.data.bobot }} poin</strong>
                        </div>
                        <div v-if="form.data.tipe === 'pilihan_ganda'" class="preview-item">
                            <span class="preview-label">Opsi:</span>
                            <strong>{{ form.data.opsi.length }}</strong>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    Card, Input, Select, Textarea, Checkbox, Radio, Button, Badge, 
    Alert, Tabs, TabPane, useForm, useToast 
} from '@/Components';

const props = defineProps({
    soal: {
        type: Object,
        default: null
    },
    categories: {
        type: Array,
        default: () => []
    }
});

const toast = useToast();
const isEdit = computed(() => !!props.soal);
const activeTab = ref('info');
const mediaInput = ref(null);

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

const form = useForm({
    kategori_id: props.soal?.kategori_id || '',
    tipe: props.soal?.tipe || 'pilihan_ganda',
    mata_pelajaran: props.soal?.mata_pelajaran || '',
    topik: props.soal?.topik || '',
    tingkat_kesulitan: props.soal?.tingkat_kesulitan || 'sedang',
    bobot: props.soal?.bobot || 10,
    pertanyaan: props.soal?.pertanyaan || '',
    opsi: props.soal?.opsi || [
        { text: '', is_correct: false },
        { text: '', is_correct: false },
        { text: '', is_correct: false },
        { text: '', is_correct: false }
    ],
    jawaban_benar: props.soal?.jawaban_benar ?? true,
    kunci_jawaban_essay: props.soal?.kunci_jawaban_essay || '',
    pembahasan: props.soal?.pembahasan || '',
    tags: props.soal?.tags || '',
    media_url: props.soal?.media_url || '',
    media_name: props.soal?.media_name || ''
});

const hasCorrectAnswer = computed(() => {
    if (form.data.tipe !== 'pilihan_ganda') return true;
    return form.data.opsi.some(opsi => opsi.is_correct);
});

const isFormValid = computed(() => {
    if (!form.data.pertanyaan || !form.data.mata_pelajaran) return false;
    if (form.data.tipe === 'pilihan_ganda' && !hasCorrectAnswer.value) return false;
    return true;
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

const isImage = (url) => {
    return /\.(jpg|jpeg|png|gif|webp)$/i.test(url);
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
    // Only one correct answer
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

    const formData = new FormData();
    formData.append('media', file);

    axios.post(route('bank-soal.upload-media'), formData)
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

const handleSubmit = () => {
    const url = isEdit.value 
        ? route('bank-soal.update', props.soal.id)
        : route('bank-soal.store');
    
    const method = isEdit.value ? 'put' : 'post';

    form[method](url, {
        onSuccess: () => {
            toast.success(`Soal berhasil ${isEdit.value ? 'diupdate' : 'ditambahkan'} ke bank soal`);
            router.visit(route('bank-soal.index'));
        },
        onError: (errors) => {
            toast.error('Terjadi kesalahan. Periksa form Anda.');
        }
    });
};
</script>

<style scoped>
.opsi-input-group {
    display: flex;
    gap: 0.5rem;
}

.opsi-label {
    font-weight: 600;
    padding-top: 0.5rem;
    min-width: 30px;
}

.opsi-actions {
    display: flex;
    align-items: center;
}

.tips-list {
    padding-left: 1.5rem;
}

.tips-list li {
    margin-bottom: 0.75rem;
}

.tingkat-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.tingkat-item {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.25rem;
}

.preview-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.preview-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
}

.preview-item:last-child {
    border-bottom: none;
}

.preview-label {
    font-weight: 600;
    color: #6c757d;
}

.media-upload {
    padding: 1rem;
}

.current-media {
    text-align: center;
}

.media-preview {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}

.media-info {
    padding: 2rem;
}
</style>
