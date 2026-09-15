@section('title')
Pengguna Online || lemon
@stop
{{-- Dipakai HANYA di dasbor, dan memakai bahasa rupa dasbor (dsb-*) supaya
     kartunya tidak terlihat seperti tempelan dari template lain di sebelah
     kartu Metode Pembayaran. --}}
<div class="dsb-kartu h-100" wire:poll.10s>
    {{-- Gaya dsb-* sengaja TIDAK di-include di sini: dasbor yang memuat
         komponen ini sudah mencetaknya sekali. Menyertakannya di sini membuat
         seluruh blok <style> ikut terkirim ulang tiap 10 detik. --}}
    <div class="dsb-kartu-kepala">
        <div class="dsb-kartu-kepala-kiri">
            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-people-fill"></i></span>
            <div>
                <h3 class="dsb-kartu-judul">Karyawan Online</h3>
                <span class="dsb-kartu-sub">Diperbarui otomatis tiap 10 detik</span>
            </div>
        </div>
        <span class="dsb-lencana is-hijau">
            <span class="dsb-bulat"></span>{{ $users->where('online', true)->count() }} aktif
        </span>
    </div>

    <div class="dsb-daftar" id="online-users-container">
        @forelse($users as $user)
            <div class="dsb-baris" id="user-{{ $user->id }}">
                <span class="dsb-aku-foto">
                    <img src="{{ $user->profile_photo && Storage::disk('public')->exists($user->profile_photo) ? Storage::url($user->profile_photo) : asset('mazer/compiled/jpg/1.jpg') }}"
                        alt="{{ $user->name }}" style="width:38px; height:38px; border-radius:12px; object-fit:cover; display:block;">
                    <span class="dsb-titik {{ $user->online ? 'is-daring' : 'is-luring' }}"></span>
                </span>

                <span class="dsb-baris-isi">
                    <span class="dsb-baris-judul">{{ $user->name }}</span>
                    @if ($user->online)
                        <span class="dsb-baris-meta">Sedang daring</span>
                    @else
                        <span class="dsb-baris-meta">
                            Terakhir terlihat {{ $user->last_seen_diff ?: 'tidak diketahui' }}
                        </span>
                    @endif
                </span>

                <span class="dsb-baris-kanan">
                    <span class="dsb-lencana {{ $user->online ? 'is-hijau' : 'is-abu' }}">
                        <span class="dsb-bulat"></span>{{ $user->online ? 'ONLINE' : 'OFFLINE' }}
                    </span>
                </span>
            </div>
        @empty
            <div class="dsb-kosong">
                <span class="dsb-kosong-ikon"><i class="bi bi-person-badge"></i></span>
                <p class="dsb-kosong-judul">Belum ada karyawan tercatat</p>
                <p class="dsb-kosong-ket">Status daring muncul setelah karyawan pertama masuk panel.</p>
            </div>
        @endforelse
    </div>
</div>
