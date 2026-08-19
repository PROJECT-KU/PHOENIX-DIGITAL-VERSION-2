#!/bin/bash
# Deploy Phoenix lewat SSH — cadangkan dulu, baru ubah.
#
# Skripnya DIKIRIM lewat stdin, bukan ditempel sebagai argumen: perintah
# panjang berisi tanda kutip (sandi database, query SQL) mustahil dikutip
# dengan benar lewat argumen.
#
# --noprofile --norc: bash melewati .bashrc sepenuhnya. Perintah SSH jarak
# jauh tetap membacanya, dan itu dugaan terkuat penyebab perintah
# menggantung pada 13 Agustus 2026.
set -e
DIR="$(cd "$(dirname "$0")" && pwd)"
ssh phoenix "bash --noprofile --norc -s" < "$DIR/remote-deploy.sh"
