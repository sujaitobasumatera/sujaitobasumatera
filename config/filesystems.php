<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            // Urutan prioritas (dari paling spesifik ke paling umum):
            // 1. STORAGE_ROOT_PATH di .env — set ini secara eksplisit di server
            //    produksi sehingga tidak perlu menebak struktur folder hosting.
            //    Contoh: STORAGE_ROOT_PATH=/home/u754986547/persistent_uploads
            // 2. str_contains fallback untuk server Rumah Web / cPanel yang
            //    menaruh proyek di dalam public_html — tetap jalan tanpa harus
            //    mengubah .env server yang sudah ada.
            // 3. public/storage untuk lokal dan lingkungan standar.
            'root' => env('STORAGE_ROOT_PATH')
                        ?: (str_contains(base_path(), 'public_html')
                            ? dirname(base_path()) . '/persistent_uploads'
                            : public_path('storage')),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | The 'public' disk writes directly into public/storage, so no symbolic link
    | is required. Leaving this empty prevents `php artisan storage:link --force`
    | from replacing the real public/storage directory with a broken symlink.
    |
    */

    'links' => [],

];
