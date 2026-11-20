{{--
    Pengintip versi RAB.

    Tidak menggambar apa pun selama tidak ada kabar: akar komponennya sengaja
    dibiarkan kosong supaya tidak menyisakan ruang di antara kartu-kartu layar
    susun. Yang berjalan di sini cuma wire:poll.

    Pengintipannya BERHENTI begitu bilahnya muncul. Sesudah tahu datanya
    berubah, menanyakannya lagi tiap 10 detik tidak menambah apa pun — dan
    hosting ini pernah tumbang karena poller yang tak pernah diam.
--}}
<div @if (! $berubah && $versi > 0) wire:poll.10s="periksa" @endif>
    @if ($berubah)
        {{--
            Dipindahkan ke <body>.

            Posisi fixed dihitung dari leluhur terdekat yang punya transform
            atau filter, bukan dari jendela. Layar susun penuh kartu bertumpuk
            dengan bayangan dan animasi, dan bilah yang "sudah fixed" pernah
            tertahan di dalam salah satunya — terlihat separuh, atau tertindih
            kartu lain meski z-index-nya besar. Diteleport ke body, tidak ada
            lagi leluhur yang bisa mengurungnya.
        --}}
        <template x-teleport="body">
            <div class="rb-penanda" role="status" aria-live="polite" wire:key="penanda-{{ $versi }}">
                <span class="rb-penanda-ikon" aria-hidden="true">
                    <i class="bi bi-arrow-repeat"></i>
                </span>
                <span class="rb-penanda-teks">
                    <strong>Diubah oleh {{ $oleh }}</strong>
                    @if ($kapan)
                        <span class="rb-penanda-kapan">{{ $kapan }}</span>
                    @endif
                    <span class="rb-penanda-ket">
                        Layar Anda masih menampilkan angka yang lama. Muat ulang
                        dulu sebelum menyunting, supaya suntingan Anda tidak
                        ditolak.
                    </span>
                </span>
                {{--
                    Lewat metode komponen, bukan $dispatch langsung di
                    wire:click. $dispatch di atribut dikerjakan peramban tanpa
                    menyentuh server, jadi wire:loading tidak pernah menyala —
                    tombolnya terlihat tidak bereaksi padahal layar susun di
                    belakangnya sedang menarik data dari Orcha, dan admin
                    menekannya berulang kali.
                --}}
                <button type="button" class="rb-penanda-tombol"
                    wire:click="mintaMuatUlang"
                    wire:loading.attr="disabled" wire:target="mintaMuatUlang">
                    <span wire:loading.remove wire:target="mintaMuatUlang">Muat ulang</span>
                    <span wire:loading wire:target="mintaMuatUlang">Memuat…</span>
                </button>
            </div>
        </template>

        <style>
            .rb-penanda {
                position: fixed;
                z-index: 2000;
                left: 50%;
                bottom: 1.25rem;
                transform: translateX(-50%);
                display: flex;
                align-items: flex-start;
                gap: .75rem;
                max-width: min(37rem, calc(100vw - 2rem));
                padding: .8rem 1rem;
                border-radius: 14px;
                border: 1px solid #fcd34d;
                background: #fffbeb;
                box-shadow: 0 12px 32px rgba(15, 23, 42, .18);
                color: #78350f;
                font-size: .82rem;
                line-height: 1.45;
            }

            .rb-penanda-ikon {
                flex: 0 0 auto;
                display: grid;
                place-items: center;
                width: 1.9rem;
                height: 1.9rem;
                border-radius: 50%;
                background: #fde68a;
                color: #92400e;
                font-size: .95rem;
            }

            .rb-penanda-teks { display: block; min-width: 0; }

            .rb-penanda-kapan {
                display: inline;
                margin-left: .3rem;
                font-weight: 600;
                opacity: .75;
            }

            .rb-penanda-ket { display: block; margin-top: .15rem; opacity: .85; }

            .rb-penanda-tombol {
                flex: 0 0 auto;
                align-self: center;
                padding: .42rem .9rem;
                border: 0;
                border-radius: 10px;
                background: #b45309;
                color: #fff;
                font-size: .8rem;
                font-weight: 700;
                cursor: pointer;
            }

            .rb-penanda-tombol:hover { background: #92400e; }

            .rb-penanda-tombol[disabled] { opacity: .65; cursor: progress; }

            @media (max-width: 575.98px) {
                .rb-penanda {
                    left: 1rem;
                    right: 1rem;
                    bottom: 1rem;
                    max-width: none;
                    transform: none;
                    flex-wrap: wrap;
                }

                .rb-penanda-tombol { width: 100%; }
            }
        </style>
    @endif
</div>
