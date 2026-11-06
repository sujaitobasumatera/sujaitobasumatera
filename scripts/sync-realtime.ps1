$ftpUser = 'admin@sujaitobasumatera.com'
$ftpPass = 'Laketoba_1'
$ftpBase = 'ftp://ftp.sujaitobasumatera.com'
$localBase = 'd:\5.RIDHO\1.BISNIS\sujailaketoba.com'

# Ambil file yang berubah dari commit terakhir saja agar cepat dan tepat sasaran
$files = git diff --name-only HEAD~1 HEAD
if (-not $files) {
    Write-Host "Tidak ada file yang berubah di commit terakhir."
    exit
}

$webclient = New-Object System.Net.WebClient
$webclient.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)

foreach ($file in $files) {
    if (-not (Test-Path -Path $file -PathType Leaf)) {
        Write-Host "Skipping $file (Deleted or is a directory)"
        continue
    }

    $localPath = Join-Path $localBase ($file -replace '/', '\')
    $remotePath = "$ftpBase/$file"
    
    Write-Host "SYNCING: $file -> Server"
    
    try {
        $webclient.UploadFile($remotePath, $localPath)
        Write-Host "  [✓] Sukses ter-upload!"
    } catch {
        if ($_.Exception.Message -match "550") {
            try {
                $dir = Split-Path $remotePath -Parent
                $req = [System.Net.FtpWebRequest]::Create($dir)
                $req.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                $req.Credentials = $webclient.Credentials
                $resp = $req.GetResponse(); $resp.Close()
                
                $webclient.UploadFile($remotePath, $localPath)
                Write-Host "  [✓] Sukses ter-upload (Folder baru dibuat)"
            } catch {
                Write-Host "  [✗] GAGAL: $($_.Exception.Message)"
            }
        } else {
            Write-Host "  [✗] GAGAL: $($_.Exception.Message)"
        }
    }
}
Write-Host "=== SINKRONISASI SELESAI ==="
