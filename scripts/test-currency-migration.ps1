<#
.SYNOPSIS
    Menguji migrasi harga IDR -> MYR memakai dump database produksi, di
    database uji terpisah. Produksi tidak disentuh sama sekali.

.DESCRIPTION
    Menjalankan Langkah 2 dari DEPLOY.md secara otomatis:
      1. Membuat database uji kosong
      2. Mengimpor dump produksi ke dalamnya
      3. Mencatat harga SEBELUM
      4. Menjalankan migrasi dengan kurs yang Anda tentukan
      5. Mencatat harga SESUDAH lalu mencetak perbandingannya

    Gunanya: melihat harga sungguhan Anda dalam ringgit sebelum produksi
    diubah. Kalau angkanya terasa keliru, hentikan di sini — bukan setelah
    pelanggan melihatnya.

.EXAMPLE
    .\scripts\test-currency-migration.ps1 -DumpFile backup.sql -Rate 4400

.EXAMPLE
    .\scripts\test-currency-migration.ps1 -DumpFile backup.sql -Rate 4400 -MysqlUser root -MysqlPassword rahasia
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory = $true, HelpMessage = "Path ke file dump .sql dari produksi")]
    [string]$DumpFile,

    [Parameter(Mandatory = $true, HelpMessage = "Berapa Rupiah per 1 MYR, mis. 4400")]
    [double]$Rate,

    [string]$MysqlUser = "root",
    [string]$MysqlPassword = "",
    [string]$MysqlHost = "127.0.0.1",
    [int]$MysqlPort = 3306,
    [string]$TestDb = "toba_uji_migrasi",

    [switch]$KeepDatabase
)

$ErrorActionPreference = "Stop"
$projectRoot = Split-Path -Parent $PSScriptRoot

function Write-Step($text) { Write-Host "`n=== $text ===" -ForegroundColor Cyan }
function Write-Ok($text)   { Write-Host "  $text" -ForegroundColor Green }
function Write-Warn($text) { Write-Host "  $text" -ForegroundColor Yellow }

# --- Prasyarat -------------------------------------------------------------

Write-Step "Memeriksa prasyarat"

if (-not (Test-Path $DumpFile)) {
    throw "File dump tidak ditemukan: $DumpFile"
}

$dumpSize = (Get-Item $DumpFile).Length
if ($dumpSize -lt 1024) {
    throw "File dump hanya $dumpSize byte. Itu bukan backup yang utuh — ambil ulang sebelum melanjutkan."
}
Write-Ok "Dump ditemukan, ukuran $([math]::Round($dumpSize / 1MB, 2)) MB"

if (-not (Get-Command mysql -ErrorAction SilentlyContinue)) {
    throw "Perintah 'mysql' tidak ada di PATH. Pasang MySQL client, atau jalankan langkah ini di mesin yang punya."
}
Write-Ok "MySQL client tersedia"

if ($Rate -le 0) {
    throw "Kurs harus lebih besar dari 0."
}
Write-Ok "Kurs yang dipakai: 1 MYR = Rp $Rate"

# Argumen koneksi, dipakai berulang.
$mysqlArgs = @("-h", $MysqlHost, "-P", "$MysqlPort", "-u", $MysqlUser)
if ($MysqlPassword -ne "") { $mysqlArgs += "-p$MysqlPassword" }

function Invoke-Sql($sql, $database = $null) {
    $a = $mysqlArgs + @("--batch", "--raw", "--skip-column-names")
    if ($database) { $a += $database }
    $a += @("-e", $sql)
    $out = & mysql @a 2>&1
    if ($LASTEXITCODE -ne 0) { throw "Query gagal: $out" }
    return $out
}

# --- Siapkan database uji --------------------------------------------------

Write-Step "Menyiapkan database uji '$TestDb'"

Invoke-Sql "DROP DATABASE IF EXISTS ``$TestDb``; CREATE DATABASE ``$TestDb`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" | Out-Null
Write-Ok "Database uji dibuat"

Write-Step "Mengimpor dump (bisa memakan waktu)"
$importArgs = $mysqlArgs + @($TestDb)
Get-Content $DumpFile -Raw | & mysql @importArgs
if ($LASTEXITCODE -ne 0) { throw "Impor dump gagal." }
Write-Ok "Dump terimpor"

$packageCount = (Invoke-Sql "SELECT COUNT(*) FROM packages;" $TestDb | Select-Object -First 1)
if ([int]$packageCount -eq 0) {
    throw "Tabel packages kosong setelah impor. Dump-nya kemungkinan tidak berisi data — periksa backup Anda."
}
Write-Ok "$packageCount paket ditemukan"

# --- Harga SEBELUM ---------------------------------------------------------

Write-Step "Mencatat harga sebelum migrasi"

$beforeRaw = Invoke-Sql "SELECT id, name, price FROM packages WHERE deleted_at IS NULL ORDER BY id;" $TestDb
$before = @{}
foreach ($line in $beforeRaw) {
    if ([string]::IsNullOrWhiteSpace($line)) { continue }
    $parts = $line -split "`t"
    $before[$parts[0]] = @{ Name = $parts[1]; Price = [double]$parts[2] }
}
Write-Ok "$($before.Count) harga tercatat"

# --- Jalankan migrasi ------------------------------------------------------

Write-Step "Menjalankan migrasi ke database uji"

Push-Location $projectRoot
try {
    # Laravel memakai Dotenv immutable: variabel yang sudah ada di environment
    # menang atas isi .env, jadi ini mengarahkan artisan ke database uji tanpa
    # menyentuh berkas .env sama sekali.
    $env:DB_CONNECTION = "mysql"
    $env:DB_HOST = $MysqlHost
    $env:DB_PORT = "$MysqlPort"
    $env:DB_DATABASE = $TestDb
    $env:DB_USERNAME = $MysqlUser
    $env:DB_PASSWORD = $MysqlPassword
    $env:PRICE_MIGRATION_MYR_IDR = "$Rate"

    & php artisan migrate --force
    if ($LASTEXITCODE -ne 0) { throw "Migrasi gagal. Baca pesan di atas." }
}
finally {
    Remove-Item Env:\DB_CONNECTION, Env:\DB_HOST, Env:\DB_PORT, Env:\DB_DATABASE, `
                Env:\DB_USERNAME, Env:\DB_PASSWORD, Env:\PRICE_MIGRATION_MYR_IDR `
                -ErrorAction SilentlyContinue
    Pop-Location
}
Write-Ok "Migrasi selesai"

# --- Harga SESUDAH & perbandingan -----------------------------------------

Write-Step "Perbandingan harga"

$afterRaw = Invoke-Sql "SELECT id, name, price FROM packages WHERE deleted_at IS NULL ORDER BY id;" $TestDb

$rows = @()
foreach ($line in $afterRaw) {
    if ([string]::IsNullOrWhiteSpace($line)) { continue }
    $parts = $line -split "`t"
    $id = $parts[0]
    $after = [double]$parts[2]
    $prev = $before[$id]
    if (-not $prev) { continue }

    # Pembulatan ke atas ke kelipatan 10, lalu -1 (RM 613,64 -> RM 649).
    # SELALU >= hasil konversi, supaya tidak pernah memotong harga. Versi
    # pertama memakai kelipatan 10 saja dan sempat menghasilkan RM 79 dari
    # RM 79,55 — sebuah potongan harga yang menyamar sebagai pembulatan.
    $suggested = [math]::Ceiling(($after + 0.01) / 50) * 50 - 1
    if ($suggested -lt $after) { $suggested = [math]::Ceiling($after) }

    $rows += [PSCustomObject]@{
        Paket        = if ($prev.Name.Length -gt 34) { $prev.Name.Substring(0, 31) + "..." } else { $prev.Name }
        "Sebelum"    = "Rp " + $prev.Price.ToString("N0")
        "Sesudah"    = "RM " + $after.ToString("N2")
        "Saran"      = "RM " + $suggested.ToString("N0")
    }
}

$rows | Format-Table -AutoSize

# --- Cek pesanan tidak tersentuh ------------------------------------------

Write-Step "Memastikan pesanan lama tidak berubah"

$bookingCheck = Invoke-Sql "SELECT COUNT(*), COALESCE(SUM(totalPrice),0), COALESCE(SUM(totalPrice_idr),0) FROM bookings;" $TestDb
$bc = ($bookingCheck | Select-Object -First 1) -split "`t"
Write-Ok "$($bc[0]) pesanan | total totalPrice = $($bc[1]) | total totalPrice_idr = $($bc[2])"
Write-Warn "Kedua angka itu HARUS sama untuk pesanan lama (semuanya dilabeli IDR rate 1)."

$mismatch = Invoke-Sql "SELECT COUNT(*) FROM bookings WHERE currency = 'IDR' AND totalPrice <> totalPrice_idr;" $TestDb
if ([int]($mismatch | Select-Object -First 1) -ne 0) {
    Write-Host "  PERINGATAN: ada pesanan IDR yang totalPrice-nya tidak sama dengan totalPrice_idr." -ForegroundColor Red
} else {
    Write-Ok "Tidak ada pesanan yang nominalnya bergeser"
}

# --- Penutup ---------------------------------------------------------------

Write-Step "Selesai"

if ($KeepDatabase) {
    Write-Ok "Database uji '$TestDb' dibiarkan agar bisa Anda periksa."
    Write-Host "  Jalankan situs terhadapnya:" -ForegroundColor DarkGray
    Write-Host "    `$env:DB_DATABASE='$TestDb'; php artisan serve" -ForegroundColor DarkGray
    Write-Host "  Hapus bila sudah selesai:" -ForegroundColor DarkGray
    Write-Host "    mysql -u $MysqlUser -e `"DROP DATABASE $TestDb`"" -ForegroundColor DarkGray
} else {
    Invoke-Sql "DROP DATABASE IF EXISTS ``$TestDb``;" | Out-Null
    Write-Ok "Database uji dihapus. Pakai -KeepDatabase kalau ingin memeriksanya sendiri."
}

Write-Host "`nProduksi tidak disentuh sama sekali oleh skrip ini.`n" -ForegroundColor Green
