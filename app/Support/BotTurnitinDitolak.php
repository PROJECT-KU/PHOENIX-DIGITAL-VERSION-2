<?php

namespace App\Support;

/**
 * Permintaan bot ditolak karena keadaannya tidak cocok (kode berbeda, unggahan
 * sudah dikerjakan admin, dll.). Dijawab HTTP 409 — bukan galat server.
 */
class BotTurnitinDitolak extends \RuntimeException {}
