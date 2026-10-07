<?php

namespace Jauhar\CaptchaGenerator;

class CaptchaSolver
{
    private string $sessionKey;

    public function __construct(string $sessionKey = 'captcha_code')
    {
        $this->sessionKey = $sessionKey;

        // Pastikan session sudah berjalan
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Memvalidasi input pengguna terhadap CAPTCHA yang tersimpan
     *
     * @param string $userInput Input dari pengguna
     * @param bool $caseSensitive Apakah validasi bersifat case sensitive
     * @return bool True jika valid, False jika tidak
     */
    public function validate(string $userInput, bool $caseSensitive = false): bool
    {
        if (empty($userInput)) {
            return false;
        }

        $storedCaptcha = $_SESSION[$this->sessionKey] ?? '';

        if (empty($storedCaptcha)) {
            return false;
        }

        // Bandingkan berdasarkan pengaturan case sensitivity
        if ($caseSensitive) {
            $isValid = ($userInput === $storedCaptcha);
        } else {
            $isValid = (strtoupper(trim($userInput)) === strtoupper(trim($storedCaptcha)));
        }

        return $isValid;
    }

    /**
     * Menghapus CAPTCHA dari session setelah divalidasi (berhasil atau gagal)
     * Ini penting untuk mencegah Replay Attack
     */
    public function clear(): void
    {
        if (isset($_SESSION[$this->sessionKey])) {
            unset($_SESSION[$this->sessionKey]);
        }
    }
}
