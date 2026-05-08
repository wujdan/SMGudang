@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@push('styles')
    <style>
        /* Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #dc2626;
            transition: .3s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #16a34a;
        }

        input:disabled+.slider {
            opacity: 0.6;
            cursor: not-allowed;
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }

        /* Animasi toast */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-users"></i> Daftar Pengguna
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" onclick="openTambahModal()">
                        <i class="fas fa-plus"></i> Tambah Pengguna
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 22%">Nama</th>
                                <th style="width: 25%">Email</th>
                                <th style="width: 12%">Role</th>
                                <th style="width: 12%">Status</th>
                                <th style="width: 14%">Dibuat Pada</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengguna as $key => $user)
                                <tr>
                                    <td>{{ $loop->iteration + ($pengguna->currentPage() - 1) * $pengguna->perPage() }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if ($user->role == 'super_admin')
                                            <span class="badge badge-primary">Super Admin</span>
                                        @elseif ($user->role == 'admin')
                                            <span class="badge badge-danger">Admin</span>
                                        @else
                                            <span class="badge badge-success">User</span>
                                        @endif
                                    </td>
                                    <td>
                                        <label class="switch" style="display: inline-block;">
                                            <input type="checkbox" class="toggle-active" data-user-id="{{ $user->id }}"
                                                {{ $user->is_active ? 'checked' : '' }}
                                                {{ $user->id == auth()->id() ? 'disabled' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="button" class="btn btn-info btn-sm"
                                                onclick="lihatPengguna({{ json_encode($user) }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm"
                                                onclick="editPengguna({{ json_encode($user) }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if (auth()->id() != $user->id)
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="hapusPengguna({{ $user->id }}, '{{ $user->name }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <i class="fa-solid fa-users"></i>
                                        <p>Tidak ada data pengguna</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    {{ $pengguna->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Pengguna -->
    <div id="modalTambah" class="modal-backdrop" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h4><i class="fa-solid fa-user-plus"></i> Tambah Pengguna Baru</h4>
                <button type="button" class="btn-close" onclick="closeTambahModal()">&times;</button>
            </div>
            <form action="{{ route('pengguna.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="Masukkan email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control"
                            placeholder="Ulangi password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-control" required>
                            <option value="">Pilih Role</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeTambahModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Lihat Pengguna -->
    <div id="modalLihat" class="modal-backdrop" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h4><i class="fa-solid fa-user"></i> Detail Pengguna</h4>
                <button type="button" class="btn-close" onclick="closeLihatModal()">&times;</button>
            </div>
            <div class="modal-body">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <th
                            style="width: 35%; padding: 8px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            Nama</th>
                        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;" id="lihat_nama">-</td>
                    </tr>
                    <tr>
                        <th
                            style="width: 35%; padding: 8px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            Email</th>
                        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;" id="lihat_email">-</td>
                    </tr>
                    <tr>
                        <th
                            style="width: 35%; padding: 8px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            Role</th>
                        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;" id="lihat_role">-</td>
                    </tr>
                    <tr>
                        <th
                            style="width: 35%; padding: 8px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            Status</th>
                        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;" id="lihat_status">-</td>
                    </tr>
                    <tr>
                        <th
                            style="width: 35%; padding: 8px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            Dibuat Pada</th>
                        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;" id="lihat_created">-</td>
                    </tr>
                    <tr>
                        <th style="width: 35%; padding: 8px; text-align: left; background: #f8fafc;">Terakhir Update</th>
                        <td style="padding: 8px;" id="lihat_updated">-</td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeLihatModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Edit Pengguna -->
    <div id="modalEdit" class="modal-backdrop" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h4><i class="fa-solid fa-user-pen"></i> Edit Pengguna</h4>
                <button type="button" class="btn-close" onclick="closeEditModal()">&times;</button>
            </div>
            <form id="formEdit" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password <span class="text-muted">(Kosongkan jika tidak
                                diubah)</span></label>
                        <input type="password" name="password" id="edit_password" class="form-control"
                            placeholder="Minimal 8 karakter">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="edit_password_confirmation"
                            class="form-control" placeholder="Ulangi password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="edit_role" class="form-control" required>
                            <option value="">Pilih Role</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Pengguna -->
    <div id="modalHapus" class="modal-backdrop" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h4><i class="fa-solid fa-trash"></i> Konfirmasi Hapus</h4>
                <button type="button" class="btn-close" onclick="closeHapusModal()">&times;</button>
            </div>
            <form id="formHapus" action="" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus pengguna <strong id="hapus_nama"></strong>?</p>
                    <p class="text-danger" style="color: var(--danger); font-size: 12px;">Tindakan ini tidak dapat
                        dibatalkan!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeHapusModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Modal functions
        function openTambahModal() {
            document.getElementById('modalTambah').style.display = 'flex';
        }

        function closeTambahModal() {
            document.getElementById('modalTambah').style.display = 'none';
            document.querySelector('#modalTambah form').reset();
        }

        function lihatPengguna(user) {
            document.getElementById('lihat_nama').innerText = user.name;
            document.getElementById('lihat_email').innerText = user.email;
            let roleHtml = '';
            if (user.role === 'super_admin') {
                roleHtml = '<span class="badge badge-primary">Super Admin</span>';
            } else if (user.role === 'admin') {
                roleHtml = '<span class="badge badge-danger">Admin</span>';
            } else {
                roleHtml = '<span class="badge badge-success">User</span>';
            }
            document.getElementById('lihat_role').innerHTML = roleHtml;
            document.getElementById('lihat_status').innerText = user.is_active ? 'Aktif' : 'Nonaktif';
            document.getElementById('lihat_created').innerText = user.created_at || '-';
            document.getElementById('lihat_updated').innerText = user.updated_at || '-';
            document.getElementById('modalLihat').style.display = 'flex';
        }

        function closeLihatModal() {
            document.getElementById('modalLihat').style.display = 'none';
        }

        function editPengguna(user) {
            document.getElementById('formEdit').action = '/pengguna/' + user.id;
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_password_confirmation').value = '';
            document.getElementById('modalEdit').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('modalEdit').style.display = 'none';
        }

        function hapusPengguna(id, name) {
            document.getElementById('formHapus').action = '/pengguna/' + id;
            document.getElementById('hapus_nama').innerText = name;
            document.getElementById('modalHapus').style.display = 'flex';
        }

        function closeHapusModal() {
            document.getElementById('modalHapus').style.display = 'none';
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal-backdrop').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        });

        // Toggle switch tanpa konfirmasi
        document.querySelectorAll('.toggle-active').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const userId = this.dataset.userId;
                const isActive = this.checked;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/pengguna/${userId}/toggle-active`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                        } else {
                            this.checked = !isActive;
                            showToast(data.message || 'Gagal mengubah status', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.checked = !isActive;
                        showToast('Terjadi kesalahan jaringan', 'error');
                    });
            });
        });

        // Fungsi toast
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = message;
            toast.style.position = 'fixed';
            toast.style.bottom = '20px';
            toast.style.right = '20px';
            toast.style.backgroundColor = type === 'success' ? '#16a34a' : '#dc2626';
            toast.style.color = 'white';
            toast.style.padding = '12px 20px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            toast.style.zIndex = '9999';
            toast.style.animation = 'fadeInUp 0.3s ease';
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
@endpush
