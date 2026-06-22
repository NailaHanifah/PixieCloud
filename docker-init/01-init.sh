#!/bin/sh
set -euo pipefail

echo "🌿 Memulai Inisialisasi Otomatis Vault PixieCloud..." | tee /dev/stderr

awslocal s3 mb s3://pixie-akuperi123-qhbq | tee /dev/stderr

echo "🟢 Inisialisasi Selesai! MiniStack Siap Digunakan." | tee /dev/stderr