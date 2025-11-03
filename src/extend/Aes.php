<?php

namespace laocc\alipay\extend;

use Exception;

/**
 * https://opendocs.alipay.com/common/02mse3
 */
class Aes
{

    /**
     * AES加密方法
     * @param string $encrypt_key AES密钥（Base64编码）
     * @param string $content 待加密文案
     * @return string 加密后Base64编码内容
     * @throws Exception
     */
    public static function encrypt(string $content, string $encrypt_key)
    {
        // 解码Base64编码的密钥
        $key = base64_decode($encrypt_key);
        if ($key === false) {
            throw new Exception("无效的Base64编码密钥");
        }
        // 解析算法/模式/补码信息
        $algorithmParts = explode('/', 'AES/CBC/PKCS5Padding');
        $algorithm = $algorithmParts[0];
        $mode = $algorithmParts[1];

        // 验证密钥长度（AES支持128/192/256位）
        $keyLength = strlen($key) * 8;
        $validLengths = [128, 192, 256];
        if (!in_array($keyLength, $validLengths)) {
            throw new Exception("AES密钥长度必须为" . implode('/', $validLengths) . "位");
        }

        // 构建OpenSSL支持的算法模式（如'AES-128-CBC'）
        $cipherMethod = strtolower("{$algorithm}-{$keyLength}-{$mode}");
        if (!in_array($cipherMethod, openssl_get_cipher_methods())) {
            throw new Exception("不支持的加密模式: {$cipherMethod}");
        }

        // 生成全0初始向量（IV），AES固定16字节（128位）
        $blockSize = 16;
        $iv = str_repeat("\0", $blockSize);

        // 将内容转换为指定字符集
        $contentBytes = mb_convert_encoding($content, 'utf-8');
        if ($contentBytes === false) {
            throw new Exception("无法将内容转换为指定字符集: utf8");
        }

        // 执行加密（获取原始二进制结果）
        $encrypted = openssl_encrypt(
            $contentBytes,
            $cipherMethod,
            $key,
            OPENSSL_RAW_DATA, // 返回原始二进制，不自动Base64
            $iv
        );

        if ($encrypted === false) {
            $error = openssl_error_string();
            throw new Exception("加密失败: {$error}");
        }

        // 对加密结果进行Base64编码
        return base64_encode($encrypted);
    }


    /**
     * AES解密方法
     * @param string $content 密文（Base64编码）
     * @param string $key AES密钥（Base64编码）
     * @return string 解密后的原文
     * @throws Exception
     */
    public static function decrypt(string $content, string $key)
    {
        // 1. 解码Base64编码的密钥（对应Java的Base64.decodeBase64(key.getBytes())）
        $keyDecoded = base64_decode($key);
        if ($keyDecoded === false) {
            throw new Exception("密钥Base64解码失败");
        }

        // 验证密钥长度（AES支持16/24/32字节，对应128/192/256位）
        $keyLength = strlen($keyDecoded);
        $validLengths = [16, 24, 32];
        if (!in_array($keyLength, $validLengths)) {
            throw new Exception("AES密钥长度必须为" . implode('/', $validLengths) . "字节（对应128/192/256位）");
        }

        // 2. 构建OpenSSL算法模式（Java中固定为AES/CBC/PKCS5Padding）
        $cipherMethod = strtolower("AES-" . ($keyLength * 8) . "-CBC"); // 如AES-128-CBC
        $sure = openssl_get_cipher_methods();
//        print_r($sure);

        if (!in_array($cipherMethod, $sure)) {
            throw new Exception("不支持的加密模式: {$cipherMethod}");
        }

        // 3. 创建16字节全零IV向量（对应Java的128bit全零IV）
        $iv = str_repeat("\0", 16); // 16个空字节组成的IV

        // 4. 处理密文：先按指定字符集转换，再Base64解码（对应Java的content.getBytes(charset) + Base64.decodeBase64）
        $contentBytes = mb_convert_encoding($content, 'utf-8'); // 转换为指定字符集的字节序列
        if ($contentBytes === false) {
            throw new Exception("密文转换为指定字符集失败: utf8");
        }
        $encryptedData = base64_decode($contentBytes);
        if ($encryptedData === false) {
            throw new Exception("密文Base64解码失败");
        }

        // 5. 执行解密（对应Java的deCipher.doFinal）
        $decrypted = openssl_decrypt(
            $encryptedData,
            $cipherMethod,
            $keyDecoded,
            OPENSSL_RAW_DATA, // 输入为原始二进制数据（非Base64）
            $iv
        );

        if ($decrypted === false) {
            $error = openssl_error_string();
            throw new Exception("解密失败: {$error}");
        }

        // 6. 将解密后的二进制数据转换为指定字符集的字符串（对应Java的new String(bytes)）
        return mb_convert_encoding($decrypted, 'utf-8');
    }


}