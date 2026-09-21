<?php

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $base = defined('BASE_URL') ? BASE_URL : '';
        $path = ltrim($path, '/');
        return $base !== '' ? rtrim($base, '/') . '/' . $path : '/' . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path = ''): string {
        return url('public/assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(?string $token = null): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $submittedToken = $token ?? ($_POST['_csrf_token'] ?? '');
        $storedToken = $_SESSION['_csrf_token'] ?? '';
        return !empty($submittedToken) && !empty($storedToken) && hash_equals($storedToken, $submittedToken);
    }
}

if (!function_exists('set_flash')) {
    function set_flash(string $key, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'][$key] = $message;
    }
}

if (!function_exists('flash')) {
    function flash(string $key): ?string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }
}

if (!function_exists('set_toast')) {
    function set_toast(string $type, string $title, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['toasts'])) {
            $_SESSION['toasts'] = [];
        }
        $_SESSION['toasts'][] = [
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'time'    => date('h:i A')
        ];
    }
}

if (!function_exists('get_toasts')) {
    function get_toasts(): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $toasts = $_SESSION['toasts'] ?? [];
        $_SESSION['toasts'] = [];

        // Also harvest standard flash errors/success for backward-compatibility
        if (!empty($_SESSION['flash']['error'])) {
            $toasts[] = [
                'type'    => 'error',
                'title'   => 'Attention',
                'message' => $_SESSION['flash']['error'],
                'time'    => date('h:i A')
            ];
            unset($_SESSION['flash']['error']);
        }
        if (!empty($_SESSION['flash']['success'])) {
            $toasts[] = [
                'type'    => 'success',
                'title'   => 'Success',
                'message' => $_SESSION['flash']['success'],
                'time'    => date('h:i A')
            ];
            unset($_SESSION['flash']['success']);
        }
        if (!empty($_SESSION['flash']['info'])) {
            $toasts[] = [
                'type'    => 'info',
                'title'   => 'Notice',
                'message' => $_SESSION['flash']['info'],
                'time'    => date('h:i A')
            ];
            unset($_SESSION['flash']['info']);
        }

        return $toasts;
    }
}

if (!function_exists('auth_admin')) {
    function auth_admin(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['admin'] ?? null;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/* =============================================================================
   UNIVERSAL ENCRYPTION & DECRYPTION HELPERS (AES-256-CBC + HMAC)
   ============================================================================= */

if (!function_exists('app_key')) {
    /**
     * Retrieve a 32-byte encryption key from environment or fallback hash.
     */
    function app_key(): string {
        $rawKey = getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? '');
        if (strpos($rawKey, 'base64:') === 0) {
            $decoded = base64_decode(substr($rawKey, 7), true);
            if ($decoded !== false && strlen($decoded) === 32) {
                return $decoded;
            }
        }
        // Deterministic 32-byte key derived from secret salt
        return hash('sha256', $rawKey ?: 'JiyajiLX_Enterprise_Encryption_Secret_Salt_2026', true);
    }
}

if (!function_exists('encrypt_data')) {
    /**
     * Encrypt any plaintext string using AES-256-CBC with HMAC signature.
     * Returns a URL-safe Base64 encoded string.
     */
    function encrypt_data(string $plainText): string {
        $key = app_key();
        $cipher = 'AES-256-CBC';
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $ciphertext = openssl_encrypt($plainText, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);

        $payload = $iv . $hmac . $ciphertext;
        // Convert to URL-safe Base64
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }
}

if (!function_exists('decrypt_data')) {
    /**
     * Decrypt a URL-safe Base64 ciphertext token.
     * Verifies HMAC signature before decryption. Returns null on failure.
     */
    function decrypt_data(string $token): ?string {
        if (empty($token)) {
            return null;
        }

        $key = app_key();
        $cipher = 'AES-256-CBC';
        $ivLength = openssl_cipher_iv_length($cipher);
        $hmacLength = 32; // sha256 output length

        // Decode URL-safe Base64
        $b64 = strtr($token, '-_', '+/');
        $padding = (4 - strlen($b64) % 4) % 4;
        $b64 .= str_repeat('=', $padding);
        $raw = base64_decode($b64, true);

        if ($raw === false || strlen($raw) < ($ivLength + $hmacLength + 1)) {
            return null;
        }

        $iv = substr($raw, 0, $ivLength);
        $hmac = substr($raw, $ivLength, $hmacLength);
        $ciphertext = substr($raw, $ivLength + $hmacLength);

        $expectedHmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);
        if (!hash_equals($expectedHmac, $hmac)) {
            return null; // Tampered or invalid signature
        }

        $decrypted = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted !== false ? $decrypted : null;
    }
}

if (!function_exists('encrypt_id')) {
    /**
     * Universal helper to encrypt any numeric database ID into a secure, URL-safe token.
     */
    function encrypt_id(int|string $id): string {
        return encrypt_data((string)$id);
    }
}

if (!function_exists('decrypt_id')) {
    /**
     * Universal helper to decrypt an encrypted token back into an integer database ID.
     * Returns null if token is invalid or tampered.
     */
    function decrypt_id(?string $encryptedId): ?int {
        if ($encryptedId === null || $encryptedId === '') {
            return null;
        }
        $plain = decrypt_data($encryptedId);
        if ($plain !== null && is_numeric($plain)) {
            return (int)$plain;
        }
        return null;
    }
}

if (!function_exists('image_url')) {
    /**
     * Resolve image URL whether it is a local upload path, relative path, or external URL.
     */
    function image_url(?string $path): string {
        if (empty($path)) {
            return "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='1.5'><rect x='3' y='3' width='18' height='18' rx='2'/><circle cx='8.5' cy='8.5' r='1.5'/><polyline points='21 15 16 10 5 21'/></svg>";
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//') || str_starts_with($path, 'data:')) {
            return $path;
        }
        return url(ltrim($path, '/'));
    }
}

if (!function_exists('format_price')) {
    /**
     * Format a price or monetary value as a whole number without decimals (e.g. 21,279).
     */
    function format_price(float|int|string|null $amount): string {
        return number_format((int)round((float)($amount ?? 0)));
    }
}

if (!function_exists('currency')) {
    /**
     * Format currency in Indian Rupees as a whole number (e.g. ₹21,279).
     */
    function currency(float|int|string|null $amount): string {
        return '₹' . number_format((int)round((float)($amount ?? 0)));
    }
}

if (!function_exists('validTs')) {
    /**
     * Guard against MySQL zero-dates and negative Unix timestamps.
     */
    function validTs(?string $d): int|false {
        if (empty($d) || $d === '0000-00-00 00:00:00') return false;
        $ts = strtotime($d);
        return ($ts && $ts > 946684800) ? $ts : false;
    }
}