<?php

/*
|--------------------------------------------------------------------------
| Web-editable application config
|--------------------------------------------------------------------------
|
| Hampir seluruh isi .env bisa diubah dari panel admin. Nilainya disimpan di
| tabel `settings` lalu menimpa config() saat aplikasi berjalan — berkas .env
| tidak pernah ditulis, sehingga proses web tidak butuh izin tulis padanya.
|
| HANYA kunci yang terdaftar di 'fields' yang bisa dibaca atau ditulis.
| Apa pun di luar itu tak terlihat oleh editor, termasuk kunci yang dicoba
| disisipkan lewat kiriman form yang dipalsukan.
|
*/

return [

    /*
    | Tidak pernah bisa diedit maupun ditampilkan.
    |
    | Ini kredensial, bukan pengaturan. Menaruhnya di halaman web membuat satu
    | sesi admin yang bobol berubah menjadi akses penuh ke database, email,
    | dan penyimpanan — yang berarti nama, email, telepon, dan riwayat
    | pemesanan setiap pelanggan. Ubah langsung di .env pada server.
    |
    | Ditegakkan di AppConfigService, bukan sekadar dicatat di sini.
    */
    'denied' => [
        'APP_KEY',
        'DB_PASSWORD', 'DB_USERNAME', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_CONNECTION',
        'MAIL_PASSWORD', 'MAIL_USERNAME',
        'AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY',
        'REDIS_PASSWORD',
    ],

    /*
    | Field yang bisa diedit.
    |
    | key    — pengenal di form dan penyimpanan
    | config — jalur config() yang ditimpa saat boot
    | label  — yang tampil di panel
    | help   — satu baris menjelaskan akibat mengubahnya
    | type   — text | url | email | number | boolean | select
    | rules  — aturan validasi Laravel
    | warn   — peringatan merah untuk pengaturan yang bisa merusak situs
    */
    'fields' => [

        // ---------------------------------------------------------------- Umum

        'app_name' => [
            'config' => 'app.name',
            'label' => 'Nama Aplikasi',
            'help' => 'Muncul di judul tab, email, dan invoice.',
            'type' => 'text',
            'rules' => 'required|string|max:60',
            'group' => 'Umum',
        ],

        'app_url' => [
            'config' => 'app.url',
            'label' => 'URL Situs',
            'help' => 'Dipakai membentuk tautan di email dan invoice. Salah isi = tautan rusak.',
            'type' => 'url',
            'rules' => 'required|url|max:255',
            'group' => 'Umum',
        ],

        'app_env' => [
            'config' => 'app.env',
            'label' => 'Lingkungan',
            'help' => 'Situs yang dipakai pelanggan harus production.',
            'type' => 'select',
            'options' => ['production', 'staging', 'local'],
            'rules' => 'required|in:production,staging,local',
            'group' => 'Umum',
            'warn' => 'Mengubah ini mengubah perilaku error, cache, dan optimasi di seluruh situs.',
        ],

        'app_debug' => [
            'config' => 'app.debug',
            'label' => 'Mode Debug',
            'help' => 'Wajib mati di situs publik.',
            'type' => 'boolean',
            'rules' => 'boolean',
            'group' => 'Umum',
            'warn' => 'Bila menyala, pesan error menampilkan isi konfigurasi ke siapa pun yang membuka situs.',
        ],

        'app_maintenance_driver' => [
            'config' => 'app.maintenance.driver',
            'label' => 'Penyimpan Status Maintenance',
            'help' => 'file untuk hosting biasa. Jangan diubah tanpa alasan.',
            'type' => 'select',
            'options' => ['file', 'cache'],
            'rules' => 'required|in:file,cache',
            'group' => 'Umum',
        ],

        // ------------------------------------------------------- Bahasa & Waktu

        'app_locale' => [
            'config' => 'app.locale',
            'label' => 'Bahasa Utama',
            'help' => 'Bahasa bawaan situs bila pengunjung belum memilih.',
            'type' => 'select',
            'options' => ['my', 'id', 'en'],
            'rules' => 'required|in:my,id,en',
            'group' => 'Bahasa',
        ],

        'app_fallback_locale' => [
            'config' => 'app.fallback_locale',
            'label' => 'Bahasa Cadangan',
            'help' => 'Dipakai bila sebuah teks belum diterjemahkan.',
            'type' => 'select',
            'options' => ['en', 'id', 'my'],
            'rules' => 'required|in:en,id,my',
            'group' => 'Bahasa',
        ],

        // --------------------------------------------------------------- Email

        'mail_mailer' => [
            'config' => 'mail.default',
            'label' => 'Metode Pengiriman',
            'help' => 'smtp untuk pengiriman sungguhan. log hanya mencatat, tidak mengirim.',
            'type' => 'select',
            'options' => ['smtp', 'log', 'sendmail', 'array'],
            'rules' => 'required|in:smtp,log,sendmail,array',
            'group' => 'Email',
            'warn' => 'Selain smtp berarti invoice TIDAK sampai ke pelanggan.',
        ],

        'mail_host' => [
            'config' => 'mail.mailers.smtp.host',
            'label' => 'Server SMTP',
            'help' => 'Alamat server email keluar, mis. smtp.hostinger.com',
            'type' => 'text',
            'rules' => 'nullable|string|max:255',
            'group' => 'Email',
        ],

        'mail_port' => [
            'config' => 'mail.mailers.smtp.port',
            'label' => 'Port SMTP',
            'help' => 'Umumnya 465 (SSL) atau 587 (TLS).',
            'type' => 'number',
            'rules' => 'nullable|integer|min:1|max:65535',
            'group' => 'Email',
        ],

        'mail_scheme' => [
            'config' => 'mail.mailers.smtp.scheme',
            'label' => 'Enkripsi SMTP',
            'help' => 'Cocokkan dengan port: smtps untuk 465, smtp untuk 587.',
            'type' => 'select',
            'options' => ['smtps', 'smtp'],
            'rules' => 'nullable|in:smtps,smtp',
            'group' => 'Email',
        ],

        'mail_from_address' => [
            'config' => 'mail.from.address',
            'label' => 'Email Pengirim',
            'help' => 'Alamat yang tampil sebagai pengirim invoice dan notifikasi.',
            'type' => 'email',
            'rules' => 'required|email|max:255',
            'group' => 'Email',
        ],

        'mail_from_name' => [
            'config' => 'mail.from.name',
            'label' => 'Nama Pengirim',
            'help' => 'Nama yang tampil di kotak masuk pelanggan.',
            'type' => 'text',
            'rules' => 'required|string|max:60',
            'group' => 'Email',
        ],

        // ----------------------------------------------------- Sesi & Keamanan

        'session_driver' => [
            'config' => 'session.driver',
            'label' => 'Penyimpan Sesi',
            'help' => 'database paling andal di hosting bersama.',
            'type' => 'select',
            'options' => ['database', 'file', 'cookie', 'array'],
            'rules' => 'required|in:database,file,cookie,array',
            'group' => 'Sesi & Keamanan',
            'warn' => 'Mengubah ini mengeluarkan semua admin yang sedang login, termasuk Anda.',
        ],

        'session_lifetime' => [
            'config' => 'session.lifetime',
            'label' => 'Durasi Sesi (menit)',
            'help' => 'Berapa lama admin tetap login tanpa aktivitas.',
            'type' => 'number',
            'rules' => 'required|integer|min:5|max:10080',
            'group' => 'Sesi & Keamanan',
        ],

        'session_encrypt' => [
            'config' => 'session.encrypt',
            'label' => 'Enkripsi Sesi',
            'help' => 'Menambah lapisan perlindungan pada data sesi.',
            'type' => 'boolean',
            'rules' => 'boolean',
            'group' => 'Sesi & Keamanan',
            'warn' => 'Mengubah ini mengeluarkan semua yang sedang login.',
        ],

        'bcrypt_rounds' => [
            'config' => 'hashing.bcrypt.rounds',
            'label' => 'Kekuatan Hash Password',
            'help' => '12 seimbang. Makin tinggi makin aman tapi login makin lambat.',
            'type' => 'number',
            'rules' => 'required|integer|min:10|max:14',
            'group' => 'Sesi & Keamanan',
        ],

        // ------------------------------------------------ Penyimpanan & Antrian

        'filesystem_disk' => [
            'config' => 'filesystems.default',
            'label' => 'Penyimpanan Berkas',
            'help' => 'public menyimpan di server ini.',
            'type' => 'select',
            'options' => ['public', 'local', 's3'],
            'rules' => 'required|in:public,local,s3',
            'group' => 'Penyimpanan & Antrian',
            'warn' => 'Mengubah ini membuat gambar yang sudah diunggah tidak ditemukan.',
        ],

        'queue_connection' => [
            'config' => 'queue.default',
            'label' => 'Antrian Tugas',
            'help' => 'sync mengerjakan langsung. database menunda lewat worker.',
            'type' => 'select',
            'options' => ['sync', 'database'],
            'rules' => 'required|in:sync,database',
            'group' => 'Penyimpanan & Antrian',
            'warn' => 'database hanya berfungsi bila ada queue worker yang berjalan.',
        ],

        'cache_store' => [
            'config' => 'cache.default',
            'label' => 'Penyimpan Cache',
            'help' => 'database aman untuk hosting bersama.',
            'type' => 'select',
            'options' => ['database', 'file', 'array'],
            'rules' => 'required|in:database,file,array',
            'group' => 'Penyimpanan & Antrian',
        ],

        // ----------------------------------------------------------------- Log

        'log_channel' => [
            'config' => 'logging.default',
            'label' => 'Saluran Log',
            'help' => 'stack menulis ke berkas harian.',
            'type' => 'select',
            'options' => ['stack', 'single', 'daily', 'errorlog'],
            'rules' => 'required|in:stack,single,daily,errorlog',
            'group' => 'Log',
        ],

        'log_level' => [
            // Kanal stack mendelegasikan ke single/daily, jadi level-nya
            // harus dipasang di keduanya agar benar-benar berlaku.
            'config' => ['logging.channels.single.level', 'logging.channels.daily.level'],
            'label' => 'Level Log',
            'help' => 'debug mencatat paling banyak. Di produksi pakai error agar log tidak membengkak.',
            'type' => 'select',
            'options' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'],
            'rules' => 'required|in:debug,info,notice,warning,error,critical,alert,emergency',
            'group' => 'Log',
        ],

        'log_deprecations_channel' => [
            'config' => 'logging.deprecations.channel',
            'label' => 'Saluran Peringatan Usang',
            'help' => 'null mengabaikan peringatan fitur yang akan dihapus.',
            'type' => 'select',
            'options' => ['null', 'stack', 'daily'],
            'rules' => 'nullable|in:null,stack,daily',
            'group' => 'Log',
        ],
    ],
];
