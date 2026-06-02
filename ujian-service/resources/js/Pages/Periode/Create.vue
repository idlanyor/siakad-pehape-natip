<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ isEdit ? 'Edit' : 'Tambah' }} Periode Ujian</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="secondary"
                            @click="router.visit(route('periode.index'))"
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
                                Informasi Periode
                            </h5>
                        </template>

                        <div class="row">
                            <div class="col-md-12">
                                <Input
                                    v-model="form.data.nama"
                                    label="Nama Periode"
                                    placeholder="Contoh: Ujian Tengah Semester Ganjil"
                                    :error="form.errors.nama"
                                    required
                                />
                            </div>

                            <div class="col-md-6">
                                <Input
                                    v-model="form.data.tahun_ajaran"
                                    label="Tahun Ajaran"
                                    placeholder="Contoh: 2024/2025"
                                    :error="form.errors.tahun_ajaran"
                                    required
                                />
                            </div>

                            <div class="col-md-6">
                                <Select
                                    v-model="form.data.semester"
                                    label="Semester"
                                    :options="semesterOptions"
                                    :error="form.errors.semester"
                                    required
                                />
                            </div>

                            <div class="col-md-6">
                                <DatePicker
                                    v-model="form.data.tanggal_mulai"
                                    label="Tanggal Mulai"
                                    :error="form.errors.tanggal_mulai"
                                    required
                                />
                            </div>

                            <div class="col-md-6">
                                <DatePicker
                                    v-model="form.data.tanggal_selesai"
                                    label="Tanggal Selesai"
                                    :min="form.data.tanggal_mulai"
                                    :error="form.errors.tanggal_selesai"
                                    required
                                />
                            </div>

                            <div class="col-md-12">
                                <Textarea
                                    v-model="form.data.deskripsi"
                                    label="Deskripsi"
                                    placeholder="Deskripsi periode ujian..."
                                    :rows="4"
                                    :error="form.errors.deskripsi"
                                />
                            </div>

                            <div class="col-md-12">
                                <Checkbox
                                    v-model="form.data.is_aktif"
                                    label="Aktifkan periode ini"
                                />
                                <small class="text-muted">
                                    Hanya satu periode yang dapat aktif dalam satu waktu
                                </small>
                            </div>
                        </div>
                    </Card>

                    <!-- Fase Configuration -->
                    <Card class="mt-4">
                        <template #header>
                            <h5 class="mb-0">
                                <i class="fas fa-cog mr-2"></i>
                                Konfigurasi Fase
                            </h5>
                        </template>

                        <Alert variant="info" icon>
                            Fase akan otomatis berpindah berdasarkan tanggal yang ditentukan. 
                            Anda dapat mengubah fase secara manual setelah periode dibuat.
                        </Alert>

                        <div class="fase-list">
                            <div
                                v-for="(fase, index) in form.data.fase"
                                :key="index"
                                class="fase-item"
                            >
                                <div class="fase-header">
                                    <h6>
                                        <Badge :variant="getFaseVariant(fase.nama)">
                                            {{ index + 1 }}
                                        </Badge>
                                        {{ getFaseLabel(fase.nama) }}
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <DatePicker
                                            v-model="fase.tanggal_mulai"
                                            label="Tanggal Mulai"
                                            :min="index === 0 ? form.data.tanggal_mulai : form.data.fase[index - 1].tanggal_selesai"
                                            :error="form.errors[`fase.${index}.tanggal_mulai`]"
                                            required
                                        />
                                    </div>
                                    <div class="col-md-6">
                                        <DatePicker
                                            v-model="fase.tanggal_selesai"
                                            label="Tanggal Selesai"
                                            :min="fase.tanggal_mulai"
                                            :max="form.data.tanggal_selesai"
                                            :error="form.errors[`fase.${index}.tanggal_selesai`]"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Actions -->
                    <div class="mt-4">
                        <Button
                            type="submit"
                            variant="primary"
                            :loading="form.processing"
                            :disabled="form.processing"
                        >
                            <i class="fas fa-save mr-2"></i>
                            {{ isEdit ? 'Update' : 'Simpan' }} Periode
                        </Button>
                        <Button
                            type="button"
                            variant="secondary"
                            class="ml-2"
                            @click="router.visit(route('periode.index'))"
                        >
                            Batal
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <Card variant="info" outline>
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle mr-2"></i>
                            Informasi
                        </h5>
                    </template>
                    <div class="info-content">
                        <h6>Fase Periode Ujian</h6>
                        <ul class="fase-info-list">
                            <li>
                                <strong>Persiapan:</strong> Persiapan soal dan konfigurasi ujian
                            </li>
                            <li>
                                <strong>Pendaftaran:</strong> Siswa dapat melihat jadwal ujian
                            </li>
                            <li>
                                <strong>Pelaksanaan:</strong> Ujian dapat dikerjakan
                            </li>
                            <li>
                                <strong>Penilaian:</strong> Guru melakukan penilaian
                            </li>
                            <li>
                                <strong>Selesai:</strong> Hasil dapat dilihat siswa
                            </li>
                        </ul>
                    </div>
                </Card>

                <Card class="mt-3">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb mr-2"></i>
                            Tips
                        </h5>
                    </template>
                    <ul class="tips-list mb-0">
                        <li>Pastikan tanggal fase tidak tumpang tindih</li>
                        <li>Fase akan berpindah otomatis sesuai jadwal</li>
                        <li>Anda dapat override fase secara manual</li>
                        <li>Hanya satu periode yang dapat aktif</li>
                    </ul>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, Input, Select, DatePicker, Textarea, Checkbox, Button, Badge, Alert, useForm, useToast } from '@/Components';

const props = defineProps({
    periode: {
        type: Object,
        default: null
    }
});

const toast = useToast();
const isEdit = computed(() => !!props.periode);

const semesterOptions = [
    { value: 'ganjil', label: 'Ganjil' },
    { value: 'genap', label: 'Genap' }
];

const defaultFase = [
    { nama: 'persiapan', tanggal_mulai: '', tanggal_selesai: '' },
    { nama: 'pendaftaran', tanggal_mulai: '', tanggal_selesai: '' },
    { nama: 'pelaksanaan', tanggal_mulai: '', tanggal_selesai: '' },
    { nama: 'penilaian', tanggal_mulai: '', tanggal_selesai: '' },
    { nama: 'selesai', tanggal_mulai: '', tanggal_selesai: '' }
];

const form = useForm({
    nama: props.periode?.nama || '',
    tahun_ajaran: props.periode?.tahun_ajaran || '',
    semester: props.periode?.semester || 'ganjil',
    tanggal_mulai: props.periode?.tanggal_mulai || '',
    tanggal_selesai: props.periode?.tanggal_selesai || '',
    deskripsi: props.periode?.deskripsi || '',
    is_aktif: props.periode?.is_aktif || false,
    fase: props.periode?.fase || defaultFase
});

const getFaseVariant = (nama) => {
    const variants = {
        'persiapan': 'info',
        'pendaftaran': 'primary',
        'pelaksanaan': 'success',
        'penilaian': 'warning',
        'selesai': 'secondary'
    };
    return variants[nama] || 'secondary';
};

const getFaseLabel = (nama) => {
    const labels = {
        'persiapan': 'Persiapan',
        'pendaftaran': 'Pendaftaran',
        'pelaksanaan': 'Pelaksanaan',
        'penilaian': 'Penilaian',
        'selesai': 'Selesai'
    };
    return labels[nama] || nama;
};

const handleSubmit = () => {
    const url = isEdit.value 
        ? route('periode.update', props.periode.id)
        : route('periode.store');
    
    const method = isEdit.value ? 'put' : 'post';

    form[method](url, {
        onSuccess: () => {
            toast.success(`Periode berhasil ${isEdit.value ? 'diupdate' : 'ditambahkan'}`);
            router.visit(route('periode.index'));
        },
        onError: (errors) => {
            toast.error('Terjadi kesalahan. Periksa form Anda.');
        }
    });
};
</script>

<style scoped>
.fase-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.fase-item {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border-left: 4px solid #007bff;
}

.fase-header {
    margin-bottom: 1rem;
}

.fase-header h6 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.fase-info-list {
    padding-left: 1.5rem;
    margin-bottom: 0;
}

.fase-info-list li {
    margin-bottom: 0.75rem;
}

.tips-list {
    padding-left: 1.5rem;
}

.tips-list li {
    margin-bottom: 0.5rem;
}
</style>
