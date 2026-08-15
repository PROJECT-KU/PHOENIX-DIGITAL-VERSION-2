<?php

namespace App\Exceptions;

use Exception;

/**
 * Dilempar bila API Orcha tidak bisa dihubungi atau menolak permintaan.
 *
 * Pesannya sengaja sudah berbahasa Indonesia dan layak dibaca admin, jadi
 * halaman cukup menampilkan getMessage() apa adanya.
 */
class OrchaTidakTerjangkau extends Exception {}
