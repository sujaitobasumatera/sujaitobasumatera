<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Satu sumber kebenaran untuk nomor WhatsApp perusahaan.
 *
 * Sebelumnya nomor ini tersebar di tiga kunci setting berbeda
 * (contact_whatsapp, contact_wa_1, specialist_wa) dengan DUA nilai bawaan yang
 * saling bertentangan: '6282277848855' dipakai untuk tautan, sementara
 * '+62 813-2388-8207' dipakai untuk teks yang terbaca. Akibatnya satu tombol
 * bisa menampilkan satu nomor dan menghubungi nomor lain — di halaman
 * pembayaran, itu terbaca sebagai tanda penipuan.
 *
 * Sekarang teks dan tautan sama-sama berasal dari sini, jadi keduanya tidak
 * bisa lagi berbeda.
 */
class ContactHelper
{
    /** Nomor resmi, dipakai bila belum ada apa pun di pengaturan. */
    public const DEFAULT_WHATSAPP = '6282277848855';

    /** Nomor WhatsApp kedua / cadangan. */
    public const DEFAULT_WHATSAPP_2 = '6281397606622';

    /**
     * Nomor WhatsApp dalam bentuk digit saja, siap dipakai di URL wa.me.
     *
     * Urutan sumber: cms_tour -> general -> config -> nilai bawaan.
     *
     * @return string
     */
    public static function whatsappDigits()
    {
        return Cache::rememberForever('contact_whatsapp_digits', function () {
            $settings = self::settings();

            $raw = $settings['cms_tour']['contact_whatsapp']
                ?? $settings['general']['contact_whatsapp']
                ?? $settings['general']['contact_wa_1']
                ?? config('services.whatsapp.phone')
                ?? self::DEFAULT_WHATSAPP;

            $digits = preg_replace('/[^0-9]/', '', (string) $raw);

            // Nomor lokal (08xx) tidak bisa dipakai wa.me — harus format
            // internasional. Nomor yang salah bentuk lebih baik jatuh ke
            // nilai bawaan daripada menghasilkan tautan yang mati.
            if (str_starts_with($digits, '0')) {
                $digits = '62'.substr($digits, 1);
            }

            return strlen($digits) >= 10 ? $digits : self::DEFAULT_WHATSAPP;
        });
    }

    /**
     * Nomor untuk dibaca manusia, mis. "+62 822-7784-8855".
     *
     * Diturunkan dari digit yang sama dengan tautannya — bukan disimpan
     * terpisah — sehingga mustahil menampilkan nomor yang berbeda dari yang
     * dituju.
     *
     * @return string
     */
    public static function whatsappDisplay()
    {
        $digits = self::whatsappDigits();

        // 62 + 3 + 4 + 4  ->  +62 822-7784-8855
        if (preg_match('/^62(\d{3})(\d{4})(\d{4,})$/', $digits, $m)) {
            return "+62 {$m[1]}-{$m[2]}-{$m[3]}";
        }

        return '+'.$digits;
    }

    /**
     * Tautan wa.me lengkap, dengan pesan awal opsional.
     *
     * @param  string|null  $message
     * @return string
     */
    public static function whatsappLink($message = null)
    {
        $url = 'https://wa.me/'.self::whatsappDigits();

        return $message ? $url.'?text='.urlencode($message) : $url;
    }

    /**
     * Nomor WhatsApp kedua dalam bentuk digit.
     *
     * @return string
     */
    public static function whatsappDigits2()
    {
        return Cache::rememberForever('contact_whatsapp_digits_2', function () {
            $settings = self::settings();

            $raw = $settings['general']['contact_whatsapp_2']
                ?? $settings['general']['contact_wa_2']
                ?? self::DEFAULT_WHATSAPP_2;

            $digits = preg_replace('/[^0-9]/', '', (string) $raw);

            if (str_starts_with($digits, '0')) {
                $digits = '62'.substr($digits, 1);
            }

            return strlen($digits) >= 10 ? $digits : self::DEFAULT_WHATSAPP_2;
        });
    }

    /**
     * Nomor WhatsApp kedua untuk tampilan.
     *
     * @return string
     */
    public static function whatsappDisplay2()
    {
        $digits = self::whatsappDigits2();

        if (preg_match('/^62(\d{3})(\d{4})(\d{4,})$/', $digits, $m)) {
            return "+62 {$m[1]}-{$m[2]}-{$m[3]}";
        }

        return '+'.$digits;
    }

    /**
     * Tautan wa.me untuk nomor kedua.
     *
     * @param  string|null  $message
     * @return string
     */
    public static function whatsappLink2($message = null)
    {
        $url = 'https://wa.me/'.self::whatsappDigits2();

        return $message ? $url.'?text='.urlencode($message) : $url;
    }

    /** Alamat email resmi bila belum ada apa pun di pengaturan. */
    public const DEFAULT_EMAIL = 'info@sujaitobasumatera.com';

    /**
     * Alamat email perusahaan.
     *
     * @return string
     */
    public static function email()
    {
        $settings = self::settings();

        $raw = $settings['cms_tour']['contact_email']
            ?? $settings['general']['contact_email']
            ?? null;

        $raw = is_string($raw) ? trim($raw) : '';

        return filter_var($raw, FILTER_VALIDATE_EMAIL) ? $raw : self::DEFAULT_EMAIL;
    }

    /**
     * Alamat kantor resmi perusahaan.
     *
     * @return string
     */
    public static function officeAddress(): string
    {
        $settings = self::settings();

        return $settings['general']['office_address']
            ?? 'Jl. Trimurti 109, Berastagi, Kabupaten Karo';
    }

    /**
     * Nomor spesialis bila diatur; kalau tidak, jatuh ke nomor kedua / nomor utama.
     *
     * @return string
     */
    public static function specialistDigits()
    {
        $settings = self::settings();

        $raw = $settings['cms_tour']['specialist_wa']
            ?? $settings['general']['specialist_wa']
            ?? self::whatsappDigits2();

        if (! $raw) {
            return self::whatsappDigits();
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $raw);
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return strlen($digits) >= 10 ? $digits : self::whatsappDigits();
    }

    /**
     * Tautan spesialis dengan pesan awal.
     *
     * @param  string|null  $message
     * @return string
     */
    public static function specialistLink($message = null)
    {
        $url = 'https://wa.me/'.self::specialistDigits();

        return $message ? $url.'?text='.urlencode($message) : $url;
    }

    /**
     * @return array
     */
    protected static function settings()
    {
        $rows = Setting::whereIn('key', ['general', 'cms_tour'])->pluck('value', 'key')->toArray();

        foreach ($rows as $key => $value) {
            if (is_string($value)) {
                $rows[$key] = json_decode($value, true) ?: [];
            }
        }

        return $rows;
    }
}
