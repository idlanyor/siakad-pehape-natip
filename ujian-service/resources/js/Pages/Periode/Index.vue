<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Periode Ujian</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="primary"
                            @click="router.visit(route('periode.create'))"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Periode
                        </Button>
                    </div>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <Card>
                    <DataTable
                        :data="periodes"
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
                        </template>

                        <template #cell-nama="{ row }">
                            <div>
                                <strong>{{ row.nama }}</strong>
                                <br>
                                <small class="text-muted">{{ row.tahun_ajaran }}</small>
                            </div>
                        </template>

                        <template #cell-tanggal="{ row }">
                            <div>
                                <small class="text-muted">Mulai:</small> {{ formatDate(row.tanggal_mulai) }}
                                <br>
                                <small class="text-muted">Selesai:</small> {{ formatDate(row.tanggal_selesai) }}
                            </div>
                        </template>

                        <template #cell-fase_aktif="{ row }">
                            <Badge :variant="getFaseVariant(row.fase_aktif)">
                                {{ getFaseLabel(row.fase_aktif) }}
                            </Badge>
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
                                <a class="dropdown-item" href="#" @click.prevent="editPeriode(row.id)">
                                    <i class="fas fa-edit mr-2"></i> Edit
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="manageFase(row.id)">
                                    <i class="fas fa-cog mr-2"></i> Kelola Fase
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
            <p>Apakah Anda yakin ingin menghapus periode <strong>{{ selectedPeriode?.nama }}</strong>?</p>
            <p class="text-muted mb-0">
                Semua data ujian yang terkait dengan periode ini juga akan terhapus.
            </p>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, DataTable, Button, Badge, Dropdown, Modal, Alert, useToast } from '@/Components';

const props = defineProps({
    periodes: {
        type: Array,
        required: true
    }
});

const loading = ref(false);
const showDeleteModal = ref(false);
const selectedPeriode = ref(null);
const deleting = ref(false);
const toast = useToast();

const columns = [
    { key: 'nama', label: 'Nama Periode', sortable: true },
    { key: 'tanggal', label: 'Tanggal', sortable: false },
    { key: 'fase_aktif', label: 'Fase Aktif', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { 
        key: 'jumlah_ujian', 
        label: 'Jumlah Ujian', 
        sortable: true,
        formatter: (value) => value || 0
    }
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

const getFaseVariant = (fase) => {
    const variants = {
        'persiapan': 'info',
        'pendaftaran': 'primary',
        'pelaksanaan': 'success',
        'penilaian': 'warning',
        'selesai': 'secondary'
    };
    return variants[fase] || 'secondary';
};

const getFaseLabel = (fase) => {
    const labels = {
        'persiapan': 'Persiapan',
        'pendaftaran': 'Pendaftaran',
        'pelaksanaan': 'Pelaksanaan',
        'penilaian': 'Penilaian',
        'selesai': 'Selesai'
    };
    return labels[fase] || fase;
};

const getStatusVariant = (status) => {
    const variants = {
        'aktif': 'success',
        'nonaktif': 'secondary',
        'selesai': 'dark'
    };
    return variants[status] || 'secondary';
};

const handleSearch = (query) => {
    // Implement search logic
    console.log('Search:', query);
};

const refreshData = () => {
    router.reload({ only: ['periodes'] });
};

const viewDetail = (id) => {
    router.visit(route('periode.show', id));
};

const editPeriode = (id) => {
    router.visit(route('periode.edit', id));
};

const manageFase = (id) => {
    router.visit(route('periode.fase', id));
};

const confirmDelete = (periode) => {
    selectedPeriode.value = periode;
    showDeleteModal.value = true;
};

const handleDelete = async () => {
    deleting.value = true;

    router.delete(route('periode.destroy', selectedPeriode.value.id), {
        onSuccess: () => {
            toast.success('Periode berhasil dihapus');
            showDeleteModal.value = false;
            selectedPeriode.value = null;
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal menghapus periode');
        },
        onFinish: () => {
            deleting.value = false;
        }
    });
};
</script>
