#!/usr/bin/env bash
#
# Cermin penuh: LOKAL -> PRODUKSI. Database dan gambar sekaligus.
#
# BACA INI SEKALI SEBELUM PAKAI.
#
# Skrip ini membuat produksi identik dengan lokal. Itu berarti tabel `bookings`,
# `customers`, dan `activity_logs` di server DITIMPA oleh isi lokal. Pesanan atau
# pelanggan yang masuk ke situs setelah salinan lokal Anda diambil akan HILANG --
# bukan tertimpa data lain, tapi lenyap. Ini konsekuensi yang dipilih sadar
# (keputusan pemilik, 2026-08-03), bukan kelalaian.
#
# Karena itu urutannya selalu: cadangkan produksi dulu, baru menulis. Cadangan
# disimpan DI LUAR folder proyek karena memuat hash password admin dan kunci API.
#
# Pemakaian:
#   bash scripts/sinkron-ke-produksi.sh --kering      # lihat apa yang akan berubah, tanpa menulis
#   bash scripts/sinkron-ke-produksi.sh --saya-yakin  # benar-benar mengirim
#
set -euo pipefail

SSH_PORT=65002
SSH_USER=u754986547
SSH_HOST=145.79.28.35
SSH_KEY="${SSH_KEY:-$HOME/.ssh/sujai_produksi}"
REMOTE_BASE='~/domains/sujaitobasumatera.com'

PROYEK="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
MEDIA_LOKAL="$PROYEK/storage/app/public"
CADANGAN="$PROYEK/../backup-sujaitobasumatera"
MYSQL_BIN="${MYSQL_BIN:-/c/xampp/mysql/bin}"
STEMPEL="$(date +%Y%m%d-%H%M%S)"

DB_LOKAL="$(grep -E '^DB_DATABASE=' "$PROYEK/.env" | head -1 | cut -d= -f2- | tr -d '"'"'"' \r')"
DB_USER="$(grep -E '^DB_USERNAME=' "$PROYEK/.env" | head -1 | cut -d= -f2- | tr -d '"'"'"' \r')"

MODE="${1:-}"
case "$MODE" in
    --kering|--saya-yakin) ;;
    *) echo "Pakai --kering (pratinjau) atau --saya-yakin (kirim sungguhan)." >&2; exit 2 ;;
esac

ssh_jalan() { ssh -p "$SSH_PORT" -i "$SSH_KEY" -o BatchMode=yes "$SSH_USER@$SSH_HOST" "$@"; }

# Kredensial DB produksi tidak pernah ditulis ke skrip ini maupun ke layar --
# selalu dibaca dari .env milik server, di server, saat dipakai.
ENV_REMOTE="cd $REMOTE_BASE/public_html && set -a && . ./.env && set +a"

echo "=== Sinkron LOKAL -> PRODUKSI ($STEMPEL) ==="
echo "    basis data : $DB_LOKAL"
echo "    media      : $MEDIA_LOKAL"
echo

# --- 1. Pratinjau: apa yang sebenarnya berbeda ---------------------------------
echo "[1/6] Membandingkan lokal dengan produksi..."
mkdir -p "$CADANGAN"

# Format dump dipaksa sama persis di kedua sisi. Tanpa --skip-extended-insert,
# MariaDB 11 (server) menulis satu baris per record sementara MariaDB 10.4 (lokal)
# menggabung semuanya jadi satu baris panjang -- diff-nya lalu memperlihatkan
# ratusan "perbedaan" yang sebenarnya cuma beda format.
OPTS="--no-tablespaces --single-transaction --default-character-set=utf8mb4 --skip-dump-date --skip-extended-insert --order-by-primary"

ssh_jalan "$ENV_REMOTE && mysqldump $OPTS -h \"\$DB_HOST\" -u \"\$DB_USERNAME\" -p\"\$DB_PASSWORD\" \"\$DB_DATABASE\"" 2>/dev/null \
    | grep -E '^INSERT' | sort > "$CADANGAN/.banding-server.txt"
"$MYSQL_BIN/mysqldump.exe" -h 127.0.0.1 -u "$DB_USER" $OPTS "$DB_LOKAL" 2>/dev/null \
    | grep -E '^INSERT' | sort > "$CADANGAN/.banding-lokal.txt"

HILANG=$(comm -23 "$CADANGAN/.banding-server.txt" "$CADANGAN/.banding-lokal.txt" | wc -l)
BARU=$(comm -13 "$CADANGAN/.banding-server.txt" "$CADANGAN/.banding-lokal.txt" | wc -l)
echo "      baris ada di server tapi tidak di lokal (AKAN HILANG) : $HILANG"
echo "      baris ada di lokal tapi tidak di server (akan ditambah): $BARU"

if [ "$HILANG" -gt 0 ]; then
    echo
    echo "      Contoh baris yang akan hilang:"
    comm -23 "$CADANGAN/.banding-server.txt" "$CADANGAN/.banding-lokal.txt" | cut -c1-100 | head -5 | sed 's/^/        /'
    echo
fi

if [ "$MODE" = "--kering" ]; then
    echo
    echo "[kering] Pratinjau media (tanpa menulis):"
    tar czf - -C "$MEDIA_LOKAL" . | ssh_jalan "mkdir -p /tmp/sinkron-kering && rm -rf /tmp/sinkron-kering/* && tar xzf - -C /tmp/sinkron-kering && rsync -a --delete --dry-run --itemize-changes /tmp/sinkron-kering/ $REMOTE_BASE/persistent_uploads/ | head -30; rm -rf /tmp/sinkron-kering"
    echo
    echo "Selesai (mode kering). Tidak ada yang ditulis ke server."
    exit 0
fi

# --- 2. Cadangkan database produksi -------------------------------------------
echo "[2/6] Mencadangkan database produksi..."
ssh_jalan "$ENV_REMOTE && mysqldump --no-tablespaces --single-transaction --default-character-set=utf8mb4 -h \"\$DB_HOST\" -u \"\$DB_USERNAME\" -p\"\$DB_PASSWORD\" \"\$DB_DATABASE\"" 2>/dev/null \
    > "$CADANGAN/db-produksi-$STEMPEL.sql"

UKURAN=$(wc -c < "$CADANGAN/db-produksi-$STEMPEL.sql")
if [ "$UKURAN" -lt 50000 ]; then
    echo "GAGAL: cadangan cuma $UKURAN byte -- terlalu kecil, tidak masuk akal. Berhenti." >&2
    exit 1
fi
echo "      -> db-produksi-$STEMPEL.sql ($UKURAN byte)"

# --- 3. Cadangkan media produksi ----------------------------------------------
echo "[3/6] Mencadangkan media produksi..."
ssh_jalan "cd $REMOTE_BASE/persistent_uploads && tar czf - ." > "$CADANGAN/media-produksi-$STEMPEL.tar.gz"
echo "      -> media-produksi-$STEMPEL.tar.gz ($(wc -c < "$CADANGAN/media-produksi-$STEMPEL.tar.gz") byte)"

# --- 4. Kirim database ---------------------------------------------------------
echo "[4/6] Mengirim database lokal ke produksi..."
"$MYSQL_BIN/mysqldump.exe" -h 127.0.0.1 -u "$DB_USER" \
    --no-tablespaces --single-transaction --default-character-set=utf8mb4 "$DB_LOKAL" 2>/dev/null \
    | gzip \
    | ssh_jalan "$ENV_REMOTE && gunzip | mysql --default-character-set=utf8mb4 -h \"\$DB_HOST\" -u \"\$DB_USERNAME\" -p\"\$DB_PASSWORD\" \"\$DB_DATABASE\"" 2>/dev/null
echo "      -> selesai"

# --- 5. Kirim media (cermin sejati, termasuk penghapusan) ----------------------
# Lokal tidak punya rsync, server punya. Jadi: tar ke direktori sementara di
# server, lalu rsync --delete dari sana. Hasilnya sama dengan rsync langsung.
echo "[5/6] Mengirim media..."
tar czf - -C "$MEDIA_LOKAL" . | ssh_jalan "
    rm -rf /tmp/sinkron-media && mkdir -p /tmp/sinkron-media &&
    tar xzf - -C /tmp/sinkron-media &&
    rsync -a --delete /tmp/sinkron-media/ $REMOTE_BASE/persistent_uploads/ &&
    rm -rf /tmp/sinkron-media &&
    echo \"      -> \$(find $REMOTE_BASE/persistent_uploads -type f | wc -l) berkas di server\""

# --- 6. Bersihkan cache server -------------------------------------------------
# Setelan situs dibaca lewat cache; tanpa ini halaman masih menampilkan data lama.
echo "[6/6] Membersihkan cache aplikasi di server..."
ssh_jalan "cd $REMOTE_BASE/public_html && php artisan optimize:clear 2>&1 | tail -3"

echo
echo "=== Selesai. Cadangan produksi ada di: $CADANGAN ==="
echo "Kalau hasilnya salah, pulihkan dengan:"
echo "  gzip -c '$CADANGAN/db-produksi-$STEMPEL.sql' | ssh -p $SSH_PORT -i '$SSH_KEY' $SSH_USER@$SSH_HOST \"$ENV_REMOTE && gunzip | mysql -h \\\"\\\$DB_HOST\\\" -u \\\"\\\$DB_USERNAME\\\" -p\\\"\\\$DB_PASSWORD\\\" \\\"\\\$DB_DATABASE\\\"\""
