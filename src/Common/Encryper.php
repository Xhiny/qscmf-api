<?php
namespace QscmfApiCommon;

use RuntimeException;

class Encryper
{
    private const ALGORITHM = 'aes-256-gcm';
    private string $master_key;

    public function __construct()
    {
        $master_key = env('QSCMF_API_ENCRYPTION_KEY') ?: '';

        // 确保主密钥是期望的长度 (32字节, 64个十六进制字符)
        if (strlen(hex2bin($master_key)) !== 32) {
            throw new RuntimeException("Master encryption key must be 32 bytes (64 hex characters).");
        }
        $this->master_key = hex2bin($master_key);
    }

    /**
     * 加密字符串
     * @param string $plaintext
     * @return string (格式: base64(iv):base64(tag):base64(ciphertext))
     */
    public function encrypt(string $plaintext): string
    {
        $ivLength = openssl_cipher_iv_length(self::ALGORITHM);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::ALGORITHM,
            $this->master_key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag // GCM模式会生成一个认证标签
        );

        if ($ciphertext === false) {
            throw new RuntimeException("Encryption failed.");
        }

        return base64_encode($iv) . ':' . base64_encode($tag) . ':' . base64_encode($ciphertext);
    }

    /**
     * 解密字符串
     * @param string $encrypted_str (格式: base64(iv):base64(tag):base64(ciphertext))
     * @return string|null
     */
    public function decrypt(string $encrypted_str): ?string
    {
        $parts = explode(':', $encrypted_str);
        if (count($parts) !== 3) {
            return null; // 格式无效
        }

        [$iv, $tag, $ciphertext] = array_map('base64_decode', $parts);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::ALGORITHM,
            $this->master_key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return $plaintext === false ? null : $plaintext;
    }
    
}