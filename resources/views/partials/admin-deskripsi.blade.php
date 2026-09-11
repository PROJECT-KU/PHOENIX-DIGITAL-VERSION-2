{{-- Kolom deskripsi di form admin — produk DAN paket bundling.

     Satu partial untuk kedua form, supaya aturan penulisan, panduan, dan
     pratinjaunya tidak bisa menyimpang. Pratinjaunya memakai partial yang SAMA
     dengan halaman toko (partials/deskripsi-rapi), jadi yang terlihat admin
     saat mengetik sama persis dengan yang dilihat pembeli.

     Variabel:
       $deskripsi   properti Livewire form (wire:model.blur="deskripsi")
       $contoh      teks contoh di kotak kosong; baris baru ditulis "\n"
--}}
<div class="d-flex flex-wrap align-items-baseline justify-content-between gap-2 mb-2">
    <label for="deskripsi" class="form-label fw-semibold text-muted mb-0">Deskripsi</label>
    <small class="text-muted"><i class="bi bi-magic"></i> Ketik biasa atau tempel langsung dari ChatGPT — toko merapikannya otomatis.</small>
</div>
{{-- Gaya sengaja diletakkan di sini, menempel pada field-nya. --}}
<style>
    /* Deskripsi biasanya panjang, sulit dibaca di kotak pendek. min-height,
       bukan height, agar menang atas textarea.form-control bawaan tema. */
    #deskripsi.deskripsi-area { min-height: 340px; line-height: 1.75; resize: vertical; }
    @media (max-width: 767.98px) { #deskripsi.deskripsi-area { min-height: 240px; } }

    /* Pratinjau memakai partial yang SAMA dengan halaman toko, jadi yang
       terlihat di sini sama persis dengan yang dilihat pembeli. */
    .dr-pratinjau {
        height: 100%; border: 1px solid #eceff4; border-radius: 14px;
        background: #fff; padding: 16px 18px;
    }
    .dr-pratinjau-kepala {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 8px; flex-wrap: wrap;
        padding-bottom: 12px; margin-bottom: 14px; border-bottom: 1px solid #f2f4f7;
    }
    .dr-pratinjau-kepala span { font-weight: 700; font-size: .85rem; color: #1f2937; }
    .dr-pratinjau-kepala small { font-size: .72rem; color: #9aa2ae; }
    .dr-pratinjau-kosong { margin: 0; font-size: .86rem; color: #9aa2ae; }

    .dr-panduan { margin-top: 10px; font-size: .82rem; color: #6b7280; }
    .dr-panduan summary { cursor: pointer; font-weight: 600; color: #4b5563; }
    .dr-panduan ul { margin: 8px 0 0; padding-left: 18px; line-height: 1.7; }
    .dr-panduan code { background: #f3f4f6; border-radius: 5px; padding: 1px 6px; color: #b45309; }
</style>
<div class="row g-3">
    <div class="col-lg-7">
        {{-- wire:model.blur, bukan .defer: pratinjau di sebelah diperbarui begitu
             kursor meninggalkan kotak ini, tanpa mengirim permintaan di setiap
             ketukan tombol. --}}
        <textarea id="deskripsi" wire:model.blur="deskripsi" rows="16"
            class="form-control deskripsi-area @error('deskripsi') is-invalid @enderror"
            placeholder="{!! str_replace("\n", '&#10;', e($contoh ?? '')) !!}"></textarea>
        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror

        <details class="dr-panduan">
            <summary>Cara menulis supaya tampil rapi</summary>
            <ul>
                <li>Baris pertama pendek, misalnya <code>Nama – Akun Siap Pakai</code>: tampil sebagai slogan.</li>
                <li>Paragraf biasa: ketik saja, pisahkan dengan baris kosong.</li>
                <li>Poin fitur: awali dengan <code>✅</code> atau <code>-</code>, atau dengan emoji apa pun (<code>🎮</code> <code>📊</code> <code>👥</code>) — emojinya jadi ikon poin.</li>
                <li>Judul pendek tepat di atas daftar, misalnya <code>Fitur Utama</code>, otomatis jadi judul bagian.</li>
                <li>Baris biasa di bawah label bertitik dua, misalnya <code>📌 Yang kamu dapat:</code>, otomatis jadi daftar.</li>
                <li>Langkah berurutan: <code>1.</code> <code>2.</code> <code>3.</code></li>
                <li>Judul bagian: <code>## Fitur Utama</code> atau baris yang diakhiri titik dua, misalnya <code>Cocok untuk:</code></li>
                <li>Catatan: awali dengan <code>📌</code> <code>🎯</code> <code>⚡</code> atau <code>💡</code>.</li>
                <li>Teks <code>**tebal**</code> dari ChatGPT tetap tampil tebal.</li>
            </ul>
        </details>
    </div>
    <div class="col-lg-5">
        <div class="dr-pratinjau" style="--c: #f26522">
            <div class="dr-pratinjau-kepala">
                <span><i class="bi bi-eye"></i> Pratinjau di toko</span>
                <small>diperbarui saat kursor keluar dari kotak</small>
            </div>
            @if (trim((string) $deskripsi) !== '')
                @include('partials.deskripsi-rapi', ['teks' => $deskripsi])
            @else
                <p class="dr-pratinjau-kosong">Pratinjau muncul setelah deskripsi diisi.</p>
            @endif
        </div>
    </div>
</div>
