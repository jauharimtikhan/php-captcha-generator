<?php

namespace Jauhar\CaptchaGenerator;

class Core
{
    private int $width;
    private int $height;
    private int $length;
    private string $captchaText = '';
    private string $fontPath;

    public function __construct(int $width = 180, int $height = 65, int $length = 6, string $fontPath = __DIR__ . '/fonts/static/Roboto-Bold.ttf')
    {
        $this->width = $width;
        $this->height = $height;
        $this->length = $length;
        $this->fontPath = $fontPath;
    }

    public function generate(): void
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        // 1. Generate RGB random untuk BACKGROUND (Range 180-255 = Warna terang/pastel)
        $bgColor = imagecolorallocate($image, rand(180, 255), rand(180, 255), rand(180, 255));
        imagefill($image, 0, 0, $bgColor);

        if (!file_exists($this->fontPath)) {
            die("Error: File font tidak ditemukan di {$this->fontPath}");
        }

        $this->captchaText = $this->generateRandomString();

        // 2. Tambahkan Background Noise: Lengkungan (Arcs) acak
        for ($i = 0; $i < 4; $i++) {
            // Warna arc agak terang biar gak terlalu mengganggu huruf utama
            $arcColor = imagecolorallocate($image, rand(120, 200), rand(120, 200), rand(120, 200));
            imagesetthickness($image, rand(1, 3)); // Ketebalan bervariasi
            imagearc(
                $image,
                rand(10, $this->width),
                rand(10, $this->height), // Titik tengah X, Y
                rand(50, 150),
                rand(30, 100),                    // Lebar, Tinggi elips
                rand(0, 360),
                rand(0, 360),                      // Derajat mulai, selesai
                $arcColor
            );
        }

        // 3. Tambahkan Background Noise: Garis acak menyilang
        for ($i = 0; $i < 6; $i++) {
            $lineColor = imagecolorallocate($image, rand(100, 220), rand(100, 220), rand(100, 220));
            imagesetthickness($image, rand(1, 2));
            imageline($image, 0, rand(0, $this->height), $this->width, rand(0, $this->height), $lineColor);
        }

        // Reset ketebalan garis ke default
        imagesetthickness($image, 1);

        // 4. Cetak Teks (Tiap huruf warna-warni, ukuran beda, kemiringan beda)
        $boxWidth = $this->width / $this->length;

        for ($i = 0; $i < $this->length; $i++) {
            $char = $this->captchaText[$i];

            $fontSize = rand(20, 28); // Ukuran font acak
            $angle = rand(-30, 30);   // Kemiringan acak -30 sampai 30 derajat

            // Posisi X dan Y
            $x = ($i * $boxWidth) + rand(2, 8);
            $y = ($this->height / 2) + ($fontSize / 2) + rand(-8, 8);

            // Generate warna gelap untuk tiap huruf (Range 0-90 = Gelap dan kontras)
            $charColor = imagecolorallocate($image, rand(0, 90), rand(0, 90), rand(0, 90));

            imagettftext($image, $fontSize, $angle, (int)$x, (int)$y, $charColor, $this->fontPath, $char);
        }

        // 5. Tambahkan Foreground Noise: Titik-titik piksel menyebar
        for ($i = 0; $i < 400; $i++) {
            $pixelColor = imagecolorallocate($image, rand(50, 150), rand(50, 150), rand(50, 150));
            imagesetpixel($image, rand(0, $this->width), rand(0, $this->height), $pixelColor);
        }

        // 6. Simpan hasil ke session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['captcha_code'] = $this->captchaText;

        // 7. Output ke browser
        header('Content-Type: image/jpeg');
        // Kualitas JPEG 70-80% menghasilkan efek noise alami yang menyulitkan OCR
        imagejpeg($image, null, 75);

        imagedestroy($image);
    }

    private function generateRandomString(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $str = '';
        for ($i = 0; $i < $this->length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $str;
    }
}
