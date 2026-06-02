@extends('layouts.admin')

@section('title', 'Kelola Akun Siswa')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Kelola Akun Siswa</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('proktor.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kelola Akun Siswa</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="stat-total">-</h3>
                        <p>Total Akun</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="stat-active">-</h3>
                        <p>Akun Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="stat-locked">-</h3>
                        <p>Akun Terkunci</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-lock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3 id="stat-today">-</h3>
                        <p>Login Hari Ini</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter & Aksi</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('proktor.siswa.index') }}" class="form-inline">
                    <div class="form-group mr-2">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama/NIS..." value="{{ request('search') }}">
                    </div>
                    <div class="form-group mr-2">
                        <select name="kelas_id" class="form-control">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->kelas_id }}" {{ request('kelas_id') == $kelas->kelas_id ? 'selected' : '' }}>
                                    {{ $kelas->kelas_nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <select name="is_active" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('proktor.siswa.index') }}" class="btn btn-secondary mr-2">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                    <a href="{{ route('proktor.siswa.export', request()->all()) }}" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export CSV
                    </a>
                </form>

                <hr>

                <div class="btn-group">
                    <button type="button" class="btn btn-warning" onclick="bulkResetPassword()">
                        <i class="fas fa-key"></i> Reset Password Terpilih
                    </button>
                    <button type="button" class="btn btn-success" onclick="bulkActivate()">
                        <i class="fas fa-check"></i> Aktifkan Terpilih
                    </button>
                    <button type="button" class="btn btn-danger" onclick="bulkDeactivate()">
                        <i class="fas fa-times"></i> Nonaktifkan Terpilih
                    </button>
                </div>
            </div>
        </div>

        <!-- Siswa List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Akun Siswa ({{ $userSiswa->total() }})</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th>Login Terakhir</th>
                            <th>Percobaan Login</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userSiswa as $user)
                            <tr>
                                <td>
                                    <input type="checkbox" class="siswa-checkbox" value="{{ $user->id }}">
                                </td>
                                <td>{{ $user->siswa->nis }}</td>
                                <td>{{ $user->siswa->nama_lengkap }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $user->siswa->kelas_nama }}</span>
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>
                                    @if($user->isLocked())
                                        <span class="badge badge-danger">
                                            <i class="fas fa-lock"></i> Terkunci
                                        </span>
                                    @elseif($user->is_active)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-times"></i> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->last_login_at)
                                        {{ $user->last_login_at->diffForHumans() }}
                                        <br>
                                        <small class="text-muted">{{ $user->last_login_at->format('d/m/Y H:i') }}</small>
                                    @else
                                        <span class="text-muted">Belum pernah login</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->login_attempts > 0)
                                        <span class="badge badge-warning">{{ $user->login_attempts }}x</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('proktor.siswa.show', $user->id) }}" class="btn btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-warning" onclick="resetPassword({{ $user->id }})" title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        @if($user->isLocked())
                                            <button type="button" class="btn btn-success" onclick="unlock({{ $user->id }})" title="Buka Kunci">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-{{ $user->is_active ? 'danger' : 'success' }}" 
                                                onclick="toggleActive({{ $user->id }})" 
                                                title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="fas fa-{{ $user->is_active ? 'times' : 'check' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $userSiswa->links() }}
            </div>
        </div>
    </div>
</section>

<!-- Forms for actions -->
<form id="form-reset-password" method="POST" style="display: none;">
    @csrf
</form>

<form id="form-toggle-active" method="POST" style="display: none;">
    @csrf
</form>

<form id="form-unlock" method="POST" style="display: none;">
    @csrf
</form>

<form id="form-bulk-reset" action="{{ route('proktor.siswa.bulk-reset-password') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="siswa_ids" id="bulk-reset-ids">
</form>

<form id="form-bulk-toggle" action="{{ route('proktor.siswa.bulk-toggle-active') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="siswa_ids" id="bulk-toggle-ids">
    <input type="hidden" name="is_active" id="bulk-toggle-status">
</form>

@endsection

@push('scripts')
<script>
// Load statistics
$(document).ready(function() {
    $.get('{{ route("proktor.siswa.statistics") }}', function(response) {
        if (response.success) {
            $('#stat-total').text(response.data.total);
            $('#stat-active').text(response.data.active);
            $('#stat-locked').text(response.data.locked);
            $('#stat-today').text(response.data.logged_in_today);
        }
    });
});

// Select all checkboxes
$('#select-all').change(function() {
    $('.siswa-checkbox').prop('checked', $(this).prop('checked'));
});

// Get selected IDs
function getSelectedIds() {
    let ids = [];
    $('.siswa-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

// Reset password
function resetPassword(id) {
    if (confirm('Reset password siswa ini ke default (NIS)?')) {
        let form = $('#form-reset-password');
        form.attr('action', '/proktor/siswa/' + id + '/reset-password');
        form.submit();
    }
}

// Toggle active
function toggleActive(id) {
    if (confirm('Ubah status akun siswa ini?')) {
        let form = $('#form-toggle-active');
        form.attr('action', '/proktor/siswa/' + id + '/toggle-active');
        form.submit();
    }
}

// Unlock account
function unlock(id) {
    if (confirm('Buka kunci akun siswa ini?')) {
        let form = $('#form-unlock');
        form.attr('action', '/proktor/siswa/' + id + '/unlock');
        form.submit();
    }
}

// Bulk reset password
function bulkResetPassword() {
    let ids = getSelectedIds();
    if (ids.length === 0) {
        alert('Pilih minimal 1 siswa');
        return;
    }
    if (confirm('Reset password ' + ids.length + ' siswa terpilih ke default (NIS)?')) {
        $('#bulk-reset-ids').val(JSON.stringify(ids));
        $('#form-bulk-reset').submit();
    }
}

// Bulk activate
function bulkActivate() {
    let ids = getSelectedIds();
    if (ids.length === 0) {
        alert('Pilih minimal 1 siswa');
        return;
    }
    if (confirm('Aktifkan ' + ids.length + ' akun siswa terpilih?')) {
        $('#bulk-toggle-ids').val(JSON.stringify(ids));
        $('#bulk-toggle-status').val('1');
        $('#form-bulk-toggle').submit();
    }
}

// Bulk deactivate
function bulkDeactivate() {
    let ids = getSelectedIds();
    if (ids.length === 0) {
        alert('Pilih minimal 1 siswa');
        return;
    }
    if (confirm('Nonaktifkan ' + ids.length + ' akun siswa terpilih?')) {
        $('#bulk-toggle-ids').val(JSON.stringify(ids));
        $('#bulk-toggle-status').val('0');
        $('#form-bulk-toggle').submit();
    }
}

// Auto dismiss alerts
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
</script>
@endpush
