<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Ujian Tersedia</h1>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <Card>
                    <template #header>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Daftar Ujian</h3>
                            <div class="card-tools">
                                <Input
                                    v-model="search"
                                    placeholder="Cari ujian..."
                                    prepend="fas fa-search"
                                />
                            </div>
                        </div>
                    </template>

                    <Loading v-if="loading" centered />

                    <div v-else-if="filteredExams.length === 0" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Tidak ada ujian tersedia saat ini</p>
                    </div>

                    <div v-else class="list-group list-group-flush">
                        <div
                            v-for="exam in filteredExams"
                            :key="exam.id"
                            class="list-group-item"
                        >
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-1">{{ exam.judul }}</h5>
                                    <p class="mb-2 text-muted">{{ exam.deskripsi }}</p>
                                    <div class="exam-meta">
                                        <span class="badge badge-info mr-2">
                                            <i class="fas fa-book mr-1"></i>
                                            {{ exam.mata_pelajaran }}
                                        </span>
                                        <span class="badge badge-secondary mr-2">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ exam.durasi }} menit
                                        </span>
                                        <span class="badge badge-primary">
                                            <i class="fas fa-question-circle mr-1"></i>
                                            {{ exam.jumlah_soal }} soal
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="mb-2">
                                        <Badge :variant="getStatusVariant(exam.status)">
                                            {{ getStatusLabel(exam.status) }}
                                        </Badge>
                                    </div>
                                    <Button
                                        v-if="canStartExam(exam)"
                                        variant="primary"
                                        @click="startExam(exam.id)"
                                    >
                                        <i class="fas fa-play mr-2"></i>
                                        Mulai Ujian
                                    </Button>
                                    <Button
                                        v-else-if="exam.sesi_aktif"
                                        variant="success"
                                        @click="continueExam(exam.sesi_aktif.id)"
                                    >
                                        <i class="fas fa-arrow-right mr-2"></i>
                                        Lanjutkan
                                    </Button>
                                    <Button
                                        v-else-if="exam.hasil"
                                        variant="info"
                                        @click="viewResult(exam.hasil.id)"
                                    >
                                        <i class="fas fa-chart-bar mr-2"></i>
                                        Lihat Hasil
                                    </Button>
                                    <Button
                                        v-else
                                        variant="secondary"
                                        disabled
                                    >
                                        Tidak Tersedia
                                    </Button>
                                </div>
                            </div>
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
import { Card, Button, Badge, Input, Loading } from '@/Components';

const props = defineProps({
    exams: {
        type: Array,
        required: true
    }
});

const search = ref('');
const loading = ref(false);

const filteredExams = computed(() => {
    if (!search.value) return props.exams;
    
    const query = search.value.toLowerCase();
    return props.exams.filter(exam => {
        return exam.judul.toLowerCase().includes(query) ||
               exam.deskripsi.toLowerCase().includes(query) ||
               exam.mata_pelajaran.toLowerCase().includes(query);
    });
});

const getStatusVariant = (status) => {
    const variants = {
        'draft': 'secondary',
        'published': 'success',
        'archived': 'dark'
    };
    return variants[status] || 'secondary';
};

const getStatusLabel = (status) => {
    const labels = {
        'draft': 'Draft',
        'published': 'Aktif',
        'archived': 'Arsip'
    };
    return labels[status] || status;
};

const canStartExam = (exam) => {
    return exam.status === 'published' && !exam.sesi_aktif && !exam.hasil;
};

const startExam = (examId) => {
    router.visit(route('exam.start', examId));
};

const continueExam = (sessionId) => {
    router.visit(route('exam.take', sessionId));
};

const viewResult = (resultId) => {
    router.visit(route('exam.result', resultId));
};
</script>

<style scoped>
.exam-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.list-group-item {
    transition: background-color 0.2s;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>
