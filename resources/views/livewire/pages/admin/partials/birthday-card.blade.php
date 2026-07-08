@if (auth()->check() && auth()->user()->isBirthday())
<style>
    .bday-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.25rem;
        padding: 2.5rem 2rem;
        color: #fff;
        border: 0;
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 40%, #4e46e5 75%, #2563eb 100%);
        box-shadow: 0 18px 45px rgba(76, 29, 149, .35);
    }

    /* ===== Konten ===== */
    .bday-content {
        position: relative;
        z-index: 5;
        max-width: 720px;
        margin: 0 auto;
        text-align: center;
    }

    .bday-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        font-size: .78rem;
        font-weight: 700;
        color: #4e46e5;
        background: #fde68a;
        padding: 7px 16px;
        border-radius: 999px;
        line-height: 1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .18);
    }

    .bday-badge i.bi {
        display: inline-flex;
        align-items: center;
        line-height: 1;
        font-size: .9rem;
    }

    .bday-badge i.bi::before {
        display: block;
        line-height: 1;
    }

    .bday-title {
        color: #ffffff !important;
        font-weight: 800;
        font-size: clamp(1.5rem, 3.5vw, 2.1rem);
        margin: 1rem 0 .6rem;
        text-shadow: 0 2px 12px rgba(0, 0, 0, .28);
        line-height: 1.2;
    }

    .bday-wishes {
        color: rgba(255, 255, 255, .95);
        font-size: 1.02rem;
        line-height: 1.65;
        margin: 0 auto 1.3rem;
        max-width: 620px;
    }

    /* ===== Kartu ayat ===== */
    .bday-ayat {
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 1rem;
        padding: 1.15rem 1.35rem;
        backdrop-filter: blur(6px);
        text-align: center;
        max-width: 600px;
        margin: 0 auto;
    }

    .bday-arabic {
        color: #fff;
        font-size: 1.7rem;
        line-height: 2.8rem;
        font-weight: 600;
        text-align: center;
        margin-bottom: .7rem;
        text-shadow: 0 2px 8px rgba(0, 0, 0, .2);
    }

    .bday-arti {
        font-style: italic;
        color: rgba(255, 255, 255, .95);
        margin-bottom: .4rem;
        font-size: .96rem;
        line-height: 1.55;
    }

    .bday-ref {
        font-size: .8rem;
        font-weight: 700;
        color: #fde68a;
    }

    /* ===== Balon terbang ===== */
    .bday-balloon {
        position: absolute;
        bottom: -50px;
        font-size: 1.9rem;
        z-index: 2;
        opacity: 0;
        pointer-events: none;
        animation: bday-float 7s ease-in infinite;
        filter: drop-shadow(0 6px 6px rgba(0, 0, 0, .18));
    }

    @keyframes bday-float {
        0% {
            transform: translateY(0) rotate(-8deg);
            opacity: 0;
        }

        12% {
            opacity: .95;
        }

        85% {
            opacity: .95;
        }

        100% {
            transform: translateY(-360px) rotate(8deg);
            opacity: 0;
        }
    }

    /* ===== Kembang api ===== */
    .bday-firework {
        position: absolute;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        z-index: 1;
        opacity: 0;
        box-shadow:
            0 -34px 0 #ffd166, 0 34px 0 #ef476f, 34px 0 0 #06d6a0, -34px 0 0 #4cc9f0,
            24px -24px 0 #ffd166, -24px 24px 0 #ef476f, 24px 24px 0 #06d6a0, -24px -24px 0 #4cc9f0;
        animation: bday-boom 1.9s ease-out infinite;
    }

    @keyframes bday-boom {
        0% {
            transform: scale(.15);
            opacity: 1;
        }

        65% {
            opacity: 1;
        }

        100% {
            transform: scale(1.7);
            opacity: 0;
        }
    }

    @media (max-width: 576px) {
        .bday-arabic {
            font-size: 1.3rem;
            line-height: 2.2rem;
        }

        .bday-card {
            padding: 2rem 1.25rem;
        }
    }
</style>

<div class="bday-card mb-4">
    {{-- Kembang api --}}
    <span class="bday-firework" style="top: 22%; left: 12%;"></span>
    <span class="bday-firework" style="top: 30%; right: 16%; animation-delay: .7s;"></span>
    <span class="bday-firework" style="top: 60%; left: 26%; animation-delay: 1.2s;"></span>
    <span class="bday-firework" style="top: 15%; right: 38%; animation-delay: 1.5s;"></span>

    {{-- Balon terbang --}}
    <span class="bday-balloon" style="left: 6%;  animation-delay: 0s;">🎈</span>
    <span class="bday-balloon" style="left: 20%; animation-delay: 1.4s;">🎈</span>
    <span class="bday-balloon" style="left: 82%; animation-delay: .6s;">🎈</span>
    <span class="bday-balloon" style="left: 92%; animation-delay: 2.1s;">🎈</span>
    <span class="bday-balloon" style="left: 68%; animation-delay: 3s;">🎉</span>

    <div class="bday-content">
        <span class="bday-badge"><i class="bi bi-gift-fill"></i> Selamat Ulang Tahun</span>
        <h2 class="bday-title">🎂 Selamat Ulang Tahun, {{ auth()->user()->name }}!</h2>
        <p class="bday-wishes">
            Semoga <b class="text-white">panjang umur</b>, <b class="text-white">sehat selalu</b>,
            <b class="text-white">murah rezeki</b>, senantiasa dalam lindungan Allah, dan diberi kelancaran
            dalam setiap urusan. Terima kasih atas dedikasimu untuk keluarga besar
            <b class="text-white">PT. Asthana Cipta Mandiri</b>. 🥳
        </p>

        <div class="bday-ayat">
            <p class="bday-arabic" dir="rtl" lang="ar">وَإِذْ تَأَذَّنَ رَبُّكُمْ لَئِنْ شَكَرْتُمْ لَأَزِيدَنَّكُمْ</p>
            <p class="bday-arti">
                &ldquo;Dan (ingatlah) ketika Tuhanmu memaklumkan, &lsquo;Sesungguhnya jika kamu bersyukur,
                niscaya Aku akan menambah (nikmat) kepadamu.&rsquo;&rdquo;
            </p>
            <span class="bday-ref">— QS. Ibrahim [14]: 7</span>
        </div>
    </div>
</div>
@endif
