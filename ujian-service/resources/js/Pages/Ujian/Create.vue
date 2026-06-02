<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ isEdit ? 'Edit' : 'Buat' }} Ujian</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="secondary"
                            @click="router.visit(route('ujian.index'))"
                        >
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </Button>
                    </div>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <Card>
                    <!-- Wizard Steps -->
                    <div class="wizard-steps mb-4">
                        <div
                            v-for="(step, index) in steps"
                            :key="index"
                            class="wizard-step"
                            :class="{
                                'active': currentStep === index,
                                'completed': currentStep > index
                            }"
                            @click="goToStep(index)"
                        >
                            <div class="step-number">
                                <i v-if="currentStep > index" class="fas fa-check"></i>
                                <span v-else>{{ index + 1 }}</span>
                            </div>
                            <div class="step-label">{{ step.label }}</div>
                        </div>
                    </div>

                    <form @submit.prevent="handleSubmit">
                        <!-- Step 1: Informasi Dasar -->
                        <div v-show="currentStep === 0" class="wizard-content">
                            <h4 class="mb-4">
                                <i class="fas fa-info-circle mr-2"></i>
                                Informasi Dasar Ujian
                            </h4>

                            <div class="row">
                                <div class="col-md-8">
                                    <Input
                                        v-model="form.data.judul"
                                        label="Judul Ujian"
                                        placeholder="Contoh: Ujian Tengah Semester Matematika"
                                        :error="form.errors.judul"
                                        required
                                    />
                                </div>

                                <div class="col-md-4">
                                    <Select
                                        v-model="form.data.jenis"
                                        label="Jenis Ujian"
                                        :options="jenisOptions"
                                        :error="form.errors.jenis"
                                        required
                                    />
                                </div>

                                <div class="col-md-6">
                                    <Select
                                        v-model="form.data.periode_id"
                                        label="Periode Ujian"
                                        :options="periodeOptions"
                                        :error="form.errors.periode_id"
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

                                <div class="col-md-12">
                                    <Textarea
                                        v-model="form.data.deskripsi"
                                        label="Deskripsi"
                                        placeholder="Deskripsi ujian..."
                                        :rows="4"
                                        :error="form.errors.deskripsi"
                                    />
                                </div>

                                <div class="col-md-12">
                                    <Textarea
                                        v-model="form.data.instruksi"
                                        label="Instruksi Ujian"
                                        placeholder="Instruksi untuk siswa sebelum mengerjakan ujian..."
                                        :rows="6"
                                        :error="form.errors.instruksi"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Konfigurasi -->
                        <div v-show="currentStep === 1" class="wizard-content">
                            <h4 class="mb-4">
                                <i class="fas fa-cog mr-2"></i>
                                Konfigurasi Ujian
                            </h4>

                            <div class="row">
                                <div class="col-md-4">
                                    <Input
                                        v-model.number="form.data.durasi"
                                        type="number"
                                        label="Durasi (menit)"
                                        placeholder="90"
                                        :error="form.errors.durasi"
                                        required
                                        min="1"
                                    />
                                </div>

                                <div class="col-md-4">
                                    <Input
                                        v-model.number="form.data.passing_grade"
                                        type="number"
                                        label="Passing Grade (%)"
                                        placeholder="75"
                                        :error="form.errors.passing_grade"
                                        required
                                        min="0"
                                        max="100"
                                    />
                                </div>

                                <div class="col-md-4">
                                    <Input
                                        v-model.number="form.data.max_attempts"
                                        type="number"
                                        label="Maksimal Percobaan"
                                        placeholder="1"
                                        :error="form.errors.max_attempts"
                                        required
                                        min="1"
                                    />
                                </div>

                                <div class="col-md-6">
                                    <DatePicker
                                        v-model="form.data.tanggal_mulai"
                                        label="Tanggal & Waktu Mulai"
                                        type="datetime-local"
                                        :error="form.errors.tanggal_mulai"
                                        required
                                    />
                                </div>

                                <div class="col-md-6">
                                    <DatePicker
                                        v-model="form.data.tanggal_selesai"
                                        label="Tanggal & Waktu Selesai"
                                        type="datetime-local"
                                        :min="form.data.tanggal_mulai"
                                        :error="form.errors.tanggal_selesai"
                                        required
                                    />
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="d-block mb-3">Pengaturan Tambahan</label>
                                        
                                        <Checkbox
                                            v-model="form.data.acak_soal"
                                            label="Acak urutan soal"
                                        />
                                        <small class="text-muted d-block mb-2">
                                            Soal akan ditampilkan dalam urutan acak untuk setiap siswa
                                        </small>

                                        <Checkbox
                                            v-model="form.data.acak_opsi"
                                            label="Acak urutan opsi jawaban"
                                        />
                                        <small class="text-muted d-block mb-2">
                                            Opsi jawaban akan diacak untuk soal pilihan ganda
                                        </small>

                                        <Checkbox
                                            v-model="form.data.tampilkan_nilai"
                                            label="Tampilkan nilai setelah selesai"
                                        />
                                        <small class="text-muted d-block mb-2">
                                            Siswa dapat melihat nilai langsung setelah menyelesaikan ujian
                                        </small>

                                        <Checkbox
                                            v-model="form.data.tampilkan_pembahasan"
                                            label="Tampilkan pembahasan"
                                        />
                                        <small class="text-muted d-block mb-2">
                                            Siswa dapat melihat pembahasan soal setelah ujian selesai
                                        </small>

                                        <Checkbox
                                            v-model="form.data.izinkan_review"
                                            label="Izinkan review jawaban"
                                        />
                                        <small class="text-muted d-block">
                                            Siswa dapat mereview jawaban mereka sebelum submit
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Peserta -->
                        <div v-show="currentStep === 2" class="wizard-content">
                            <h4 class="mb-4">
                                <i class="fas fa-users mr-2"></i>
                                Peserta Ujian
                            </h4>

                            <Alert variant="info" icon class="mb-4">
                                Pilih kelas atau siswa yang akan mengikuti ujian ini.
                            </Alert>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Pilih Berdasarkan</label>
                                        <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                                            <label 
                                                class="btn btn-outline-primary"
                                                :class="{ active: pesertaMode === 'kelas' }"
                                            >
                                                <input 
                                                    type="radio" 
                                                    value="kelas"
                                                    v-model="pesertaMode"
                                                > Kelas
                                            </label>
                                            <label 
                                                class="btn btn-outline-primary"
                                                :class="{ active: pesertaMode === 'siswa' }"
                                            >
                                                <input 
                                                    type="radio" 
                                                    value="siswa"
                                                    v-model="pesertaMode"
                                                > Siswa Individual
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="pesertaMode === 'kelas'" class="col-md-12">
                                    <Select
                                        v-model="form.data.kelas_ids"
                                        label="Pilih Kelas"
                                        :options="kelasOptions"
                                        multiple
                                        :error="form.errors.kelas_ids"
                                    />
                                    <small class="text-muted">
                                        Tekan Ctrl/Cmd untuk memilih beberapa kelas
                                    </small>
                                </div>

                                <div v-if="pesertaMode === 'siswa'" class="col-md-12">
                                    <Select
                                        v-model="form.data.siswa_ids"
                                        label="Pilih Siswa"
                                        :options="siswaOptions"
                                        multiple
                                        :error="form.errors.siswa_ids"
                                    />
                                    <small class="text-muted">
                                        Tekan Ctrl/Cmd untuk memilih beberapa siswa
                                    </small>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <div v-if="selectedPesertaCount > 0" class="alert alert-success">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <strong>{{ selectedPesertaCount }}</strong> peserta dipilih
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Review -->
                        <div v-show="currentStep === 3" class="wizard-content">
                            <h4 class="mb-4">
                                <i class="fas fa-check-circle mr-2"></i>
                                Review & Konfirmasi
                            </h4>

                            <Alert variant="info" icon class="mb-4">
                                Periksa kembali informasi ujian sebelum menyimpan.
                            </Alert>

                            <div class="review-section">
                                <h5>Informasi Dasar</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="200">Judul</th>
                                        <td>{{ form.data.judul }}</td>
                                    </tr>
                                    <tr>
                                        <th>Jenis</th>
                                        <td>{{ getJenisLabel(form.data.jenis) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mata Pelajaran</th>
                                        <td>{{ form.data.mata_pelajaran }}</td>
                                    </tr>
                                    <tr>
                                        <th>Periode</th>
                                        <td>{{ getPeriodeLabel(form.data.periode_id) }}</td>
                                    </tr>
                                </table>

                                <h5 class="mt-4">Konfigurasi</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="200">Durasi</th>
                                        <td>{{ form.data.durasi }} menit</td>
                                    </tr>
                                    <tr>
                                        <th>Passing Grade</th>
                                        <td>{{ form.data.passing_grade }}%</td>
                                    </tr>
                                    <tr>
                                        <th>Maksimal Percobaan</th>
                                        <td>{{ form.data.max_attempts }}x</td>
                                    </tr>
                                    <tr>
                                        <th>Jadwal</th>
                                        <td>
                                            {{ formatDateTime(form.data.tanggal_mulai) }} - 
                                            {{ formatDateTime(form.data.tanggal_selesai) }}
                                        </td>
                                    </tr>
                                </table>

                                <h5 class="mt-4">Pengaturan</h5>
                                <ul class="list-unstyled">
                                    <li>
                                        <i :class="form.data.acak_soal ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-muted'"></i>
                                        Acak urutan soal
                                    </li>
                                    <li>
                                        <i :class="form.data.acak_opsi ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-muted'"></i>
                                        Acak urutan opsi
                                    </li>
                                    <li>
                                        <i :class="form.data.tampilkan_nilai ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-muted'"></i>
                                        Tampilkan nilai
                                    </li>
                                    <li>
                                        <i :class="form.data.tampilkan_pembahasan ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-muted'"></i>
                                        Tampilkan pembahasan
                                    </li>
                                    <li>
                                        <i :class="form.data.izinkan_review ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-muted'"></i>
                                        Izinkan review
                                    </li>
                                </ul>

                                <h5 class="mt-4">Peserta</h5>
                                <div class="alert alert-info">
                                    <i class="fas fa-users mr-2"></i>
                                    <strong>{{ selectedPesertaCount }}</strong> peserta dipilih
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="wizard-navigation mt-4 pt-4 border-top">
                            <Button
                                v-if="currentStep > 0"
                                type="button"
                                variant="secondary"
                                @click="previousStep"
                            >
                                <i class="fas fa-arrow-left mr-2"></i>
                                Sebelumnya
                            </Button>

                            <div class="ml-auto">
                                <Button
                                    v-if="currentStep < steps.length - 1"
                                    type="button"
                                    variant="primary"
                                    @click="nextStep"
                                >
                                    Selanjutnya
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </Button>

                                <Button
                                    v-if="currentStep === steps.length - 1"
                                    type="submit"
                                    variant="success"
                                    :loading="form.processing"
                                    :disabled="form.processing"
                                >
                                    <i class="fas fa-save mr-2"></i>
                                    {{ isEdit ? 'Update' : 'Simpan' }} Ujian
                                </Button>
                            </div>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, Input, Select, DatePicker, Textarea, Checkbox, Button, Alert, useForm, useToast } from '@/Components';

const props = defineProps({
    ujian: {
        type: Object,
        default: null
    },
    periodes: {
        type: Array,
        default: () => []
    },
    kelas: {
        type: Array,
        default: () => []
    },
    siswa: {
        type: Array,
        default: () => []
    }
});

const toast = useToast();
const isEdit = computed(() => !!props.ujian);
const currentStep = ref(0);
const pesertaMode = ref('kelas');

const steps = [
    { label: 'Informasi Dasar' },
    { label: 'Konfigurasi' },
    { label: 'Peserta' },
    { label: 'Review' }
];

const jenisOptions = [
    { value: 'uts', label: 'UTS (Ujian Tengah Semester)' },
    { value: 'uas', label: 'UAS (Ujian Akhir Semester)' },
    { value: 'quiz', label: 'Quiz' },
    { value: 'tugas', label: 'Tugas' }
];

const periodeOptions = computed(() => {
    return props.periodes.map(p => ({
        value: p.id,
        label: `${p.nama} (${p.tahun_ajaran})`
    }));
});

const kelasOptions = computed(() => {
    return props.kelas.map(k => ({
        value: k.id,
        label: `${k.nama} - ${k.jurusan || ''}`
    }));
});

const siswaOptions = computed(() => {
    return props.siswa.map(s => ({
        value: s.id,
        label: `${s.nama} (${s.nis})`
    }));
});

const form = useForm({
    judul: props.ujian?.judul || '',
    jenis: props.ujian?.jenis || 'uts',
    periode_id: props.ujian?.periode_id || '',
    mata_pelajaran: props.ujian?.mata_pelajaran || '',
    deskripsi: props.ujian?.deskripsi || '',
    instruksi: props.ujian?.instruksi || '',
    durasi: props.ujian?.durasi || 90,
    passing_grade: props.ujian?.passing_grade || 75,
    max_attempts: props.ujian?.max_attempts || 1,
    tanggal_mulai: props.ujian?.tanggal_mulai || '',
    tanggal_selesai: props.ujian?.tanggal_selesai || '',
    acak_soal: props.ujian?.acak_soal || false,
    acak_opsi: props.ujian?.acak_opsi || false,
    tampilkan_nilai: props.ujian?.tampilkan_nilai || true,
    tampilkan_pembahasan: props.ujian?.tampilkan_pembahasan || false,
    izinkan_review: props.ujian?.izinkan_review || true,
    kelas_ids: props.ujian?.kelas_ids || [],
    siswa_ids: props.ujian?.siswa_ids || []
});

const selectedPesertaCount = computed(() => {
    if (pesertaMode.value === 'kelas') {
        return form.data.kelas_ids.length * 30; // Estimate
    }
    return form.data.siswa_ids.length;
});

const goToStep = (index) => {
    if (index < currentStep.value) {
        currentStep.value = index;
    }
};

const nextStep = () => {
    if (validateCurrentStep()) {
        currentStep.value++;
    }
};

const previousStep = () => {
    currentStep.value--;
};

const validateCurrentStep = () => {
    // Basic validation for each step
    if (currentStep.value === 0) {
        if (!form.data.judul || !form.data.periode_id || !form.data.mata_pelajaran) {
            toast.error('Lengkapi semua field yang wajib diisi');
            return false;
        }
    }
    if (currentStep.value === 1) {
        if (!form.data.durasi || !form.data.tanggal_mulai || !form.data.tanggal_selesai) {
            toast.error('Lengkapi konfigurasi ujian');
            return false;
        }
    }
    return true;
};

const getJenisLabel = (value) => {
    const option = jenisOptions.find(o => o.value === value);
    return option?.label || value;
};

const getPeriodeLabel = (id) => {
    const periode = props.periodes.find(p => p.id === id);
    return periode ? `${periode.nama} (${periode.tahun_ajaran})` : '-';
};

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

const handleSubmit = () => {
    const url = isEdit.value 
        ? route('ujian.update', props.ujian.id)
        : route('ujian.store');
    
    const method = isEdit.value ? 'put' : 'post';

    form[method](url, {
        onSuccess: () => {
            toast.success(`Ujian berhasil ${isEdit.value ? 'diupdate' : 'dibuat'}`);
            router.visit(route('ujian.index'));
        },
        onError: (errors) => {
            toast.error('Terjadi kesalahan. Periksa form Anda.');
            // Go back to first step with error
            currentStep.value = 0;
        }
    });
};
</script>

<style scoped>
.wizard-steps {
    display: flex;
    justify-content: space-between;
    padding: 2rem 0;
    position: relative;
}

.wizard-steps::before {
    content: '';
    position: absolute;
    top: 2.5rem;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #dee2e6;
    z-index: 0;
}

.wizard-step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    cursor: pointer;
    z-index: 1;
}

.wizard-step.completed .step-number {
    background: #28a745;
    border-color: #28a745;
    color: white;
}

.wizard-step.active .step-number {
    background: #007bff;
    border-color: #007bff;
    color: white;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
}

.step-number {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: white;
    border: 3px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    transition: all 0.3s;
}

.step-label {
    font-size: 0.875rem;
    font-weight: 500;
    text-align: center;
}

.wizard-content {
    padding: 2rem 0;
}

.wizard-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.review-section h5 {
    color: #495057;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #dee2e6;
}

.review-section table th {
    background: #f8f9fa;
}

.review-section ul li {
    padding: 0.5rem 0;
}
</style>
