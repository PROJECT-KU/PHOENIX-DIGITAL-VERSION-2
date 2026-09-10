{{-- Empat jaminan, tepat di bawah header.

     Sebelumnya baris ini menempel di kaki hero. Dipindah ke atas hero karena
     urutan pertanyaan pengunjung baru memang begitu: "toko ini bisa dipercaya
     tidak" datang SEBELUM "apa yang dijual". Menjawabnya setelah ia melewati
     seluruh hero berarti menjawab terlambat.

     Butir keempat sengaja soal pembayaran, bukan jumlah pelanggan: yang membuat
     orang mengurungkan niat di detik terakhir hampir selalu "bayarnya bagaimana",
     dan itu pertanyaan yang bisa dijawab dengan fakta, bukan dengan angka
     yang tidak bisa ia periksa. --}}
<section class="jaminan-atas" aria-label="Jaminan belanja">
    <style>
        /* Inline: public/build masuk .gitignore dan tidak ikut terdeploy. */
        .jaminan-atas { padding: 18px 0 4px; }

        .jm-deret {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;
        }

        .jm-kartu {
            display: flex; align-items: center; gap: 14px;
            background: #fff; border: 1px solid #eceff4; border-radius: 16px;
            padding: 16px 18px; min-width: 0;
            transition: border-color .22s ease, box-shadow .22s ease, transform .22s ease;
        }
        .jm-kartu:hover {
            border-color: #f7c9ae; transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(242, 101, 34, .09);
        }

        .jm-ikon {
            flex: 0 0 auto; width: 42px; height: 42px; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #f26522, #fb8b3c);
            color: #fff; font-size: 1.15rem;
            box-shadow: 0 6px 14px rgba(242, 101, 34, .26);
        }
        .jm-ikon i.bi { line-height: 1; }

        .jm-teks { min-width: 0; }
        .jm-teks strong {
            display: block; font-family: 'Poppins', sans-serif; font-weight: 700;
            font-size: .92rem; color: #1c1f26; line-height: 1.3; letter-spacing: -.01em;
        }
        .jm-teks span { display: block; font-size: .79rem; color: #8b94a3; line-height: 1.4; }

        @media (max-width: 991.98px) {
            .jm-deret { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        }
        @media (max-width: 479.98px) {
            .jm-kartu { padding: 13px 14px; gap: 11px; }
            .jm-ikon { width: 36px; height: 36px; font-size: 1rem; }
            .jm-teks strong { font-size: .84rem; }
            .jm-teks span { font-size: .73rem; }
        }
    </style>

    <div class="container">
        <div class="jm-deret">
            @foreach ([
                ['bi-lightning-charge-fill', 'Proses Instan', 'Akun langsung aktif'],
                ['bi-shield-check', 'Bergaransi', 'Aman & terpercaya'],
                ['bi-headset', 'Bantuan 24/7', 'Siap membantu'],
                ['bi-credit-card-2-front', 'Pembayaran Mudah', 'Transfer, QRIS, e-Wallet'],
            ] as [$ikon, $judul, $sub])
            <div class="jm-kartu">
                <span class="jm-ikon"><i class="bi {{ $ikon }}"></i></span>
                <span class="jm-teks">
                    <strong>{{ $judul }}</strong>
                    <span>{{ $sub }}</span>
                </span>
            </div>
            @endforeach
        </div>
    </div>
</section>
