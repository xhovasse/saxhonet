<?php
/**
 * TOTP (Time-based One-Time Password) — RFC 6238
 * Implementation autonome, sans dependance Composer.
 */
class TOTP
{
    /**
     * Alphabet Base32 (RFC 4648)
     */
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generer un secret aleatoire encode en Base32
     */
    public static function generateSecret(int $length = 20): string
    {
        $bytes = random_bytes($length);
        return self::base32Encode($bytes);
    }

    /**
     * Calculer le code TOTP pour un instant donne
     */
    public static function getCode(string $secret, ?int $time = null, int $period = 30, int $digits = 6): string
    {
        $time = $time ?? time();
        $counter = (int) floor($time / $period);

        // Counter en 8 octets big-endian
        $binary = pack('N*', 0) . pack('N*', $counter);

        // HMAC-SHA1
        $key = self::base32Decode($secret);
        $hash = hash_hmac('sha1', $binary, $key, true);

        // Dynamic truncation
        $offset = ord($hash[19]) & 0x0F;
        $code = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) <<  8) |
             (ord($hash[$offset + 3]) & 0xFF)
        ) % pow(10, $digits);

        return str_pad((string) $code, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Verifier un code TOTP avec fenetre de tolerance
     *
     * @param string $secret  Secret Base32
     * @param string $code    Code saisi par l'utilisateur
     * @param int    $window  Nombre de periodes de tolerance (+/-)
     */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $time = time();
        $code = trim($code);

        for ($i = -$window; $i <= $window; $i++) {
            $checkTime = $time + ($i * 30);
            if (hash_equals(self::getCode($secret, $checkTime), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generer l'URI otpauth:// pour provisioning (QR code)
     */
    public static function getProvisioningUri(string $secret, string $email, string $issuer = ''): string
    {
        $issuer = $issuer ?: (defined('MFA_ISSUER') ? MFA_ISSUER : 'Saxho');
        $label = rawurlencode($issuer) . ':' . rawurlencode($email);

        return 'otpauth://totp/' . $label
            . '?secret=' . rawurlencode($secret)
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1'
            . '&digits=6'
            . '&period=30';
    }

    /**
     * Generer des codes de secours
     *
     * @return array Liste de codes en clair (a afficher UNE FOIS, puis stocker les hashes)
     */
    public static function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            // 8 caracteres alphanumeriques lisibles (pas de 0/O/l/1 ambigus)
            $pool = '23456789abcdefghjkmnpqrstuvwxyz';
            $code = '';
            $bytes = random_bytes(8);
            for ($j = 0; $j < 8; $j++) {
                $code .= $pool[ord($bytes[$j]) % strlen($pool)];
            }
            $codes[] = $code;
        }
        return $codes;
    }

    /**
     * Hasher un code de secours pour stockage en BDD
     */
    public static function hashBackupCode(string $code): string
    {
        return hash('sha256', strtolower(trim($code)));
    }

    /**
     * Verifier un code de secours contre une liste de hashes
     *
     * @return int|false L'index du code trouve, ou false
     */
    public static function verifyBackupCode(string $code, array $hashedCodes): int|false
    {
        $hash = self::hashBackupCode($code);
        foreach ($hashedCodes as $i => $stored) {
            if (hash_equals($stored, $hash)) {
                return $i;
            }
        }
        return false;
    }

    // ======================================
    // Base32 encoding / decoding (RFC 4648)
    // ======================================

    /**
     * Encoder des bytes en Base32
     */
    private static function base32Encode(string $data): string
    {
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $result = '';
        $chunks = str_split($binary, 5);
        foreach ($chunks as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $result .= self::BASE32_CHARS[bindec($chunk)];
        }

        return $result;
    }

    /**
     * Decoder du Base32 en bytes
     */
    private static function base32Decode(string $data): string
    {
        $data = strtoupper(rtrim($data, '='));
        $binary = '';

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $pos = strpos(self::BASE32_CHARS, $data[$i]);
            if ($pos === false) {
                continue; // Ignorer les caracteres invalides
            }
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $result = '';
        $chunks = str_split($binary, 8);
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 8) break;
            $result .= chr(bindec($chunk));
        }

        return $result;
    }
}
