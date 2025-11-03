<?php

namespace laocc\alipay\extend;

use Exception;

/**
 * 支付宝签名/验签工具类（适配PHP 8.x，基于SHA256withRSA算法）
 */
class SignUtil
{

    /**
     * 生成签名（私钥签名）
     * @param array $params 待签名的参数数组（如['out_trade_no'=>'123456', 'total_amount'=>'99.00']）
     * @param string $privateKey 商户私钥（PKCS#8格式，需去除首尾标识和换行符）
     * @return string 签名结果（Base64编码），失败返回false
     */
    public static function generateSign(array $params, string $privateKey): string
    {
        try {
            // 1. 过滤空值参数（排除空字符串、null、false）
            $filteredParams = self::filterEmptyParams($params);
            if (empty($filteredParams)) {
                throw new Exception("待签名参数不能为空");
            }

            // 2. 按参数名ASCII升序排序（PHP 8.x中ksort默认行为与低版本一致）
            ksort($filteredParams, SORT_STRING);

            // 3. 拼接参数为"key=value&key=value"格式
            $paramStr = self::joinParams($filteredParams);

            // 4. 加载私钥（PKCS#8格式，补全PEM头尾部标识）
            $privateKeyPem = "-----BEGIN PRIVATE KEY-----\n" .
                wordwrap($privateKey, 64, "\n", true) .
                "\n-----END PRIVATE KEY-----";
            $privKey = openssl_pkey_get_private($privateKeyPem);
            if (!$privKey) {
                throw new Exception("私钥格式错误，请检查是否为PKCS#8格式");
            }

            // 5. 使用SHA256withRSA算法签名（PHP 8.x中OPENSSL_ALGO_SHA256常量兼容）
            $signature = '';
            $signSuccess = openssl_sign($paramStr, $signature, $privKey, OPENSSL_ALGO_SHA256);
            if (!$signSuccess) {
                throw new Exception("签名生成失败");
            }

            return base64_encode($signature);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 验证签名（公钥验签）
     * @param array $params 待验签的参数数组（含签名字段，如['sign'=>'xxx', 'out_trade_no'=>'123456']）
     * @param string $sign
     * @param string $alipayPublicKey 支付宝公钥（从开放平台获取，需去除首尾标识和换行符）
     * @return bool 验签成功返回true，失败返回false
     *
     * https://opendocs.alipay.com/open-v3/054d0z
     * , string $signField = 'sign'
     */
    public static function verifySign(array $params, string $sign, string $alipayPublicKey): bool
    {
        try {
            unset($params['sign']);

            // 3. 过滤空值参数并排序（与签名流程保持一致）
            $filteredParams = self::filterEmptyParams($params);
            ksort($filteredParams, SORT_STRING);

            // 4. 拼接参数为"key=value&key=value"格式
            $paramStr = self::joinParams($filteredParams);

            // 5. 加载支付宝公钥（补全PEM头尾部标识）
            $publicKeyPem = "-----BEGIN PUBLIC KEY-----\n" .
                wordwrap($alipayPublicKey, 64, "\n", true) .
                "\n-----END PUBLIC KEY-----";
            $pubKey = openssl_pkey_get_public($publicKeyPem);
            if (!$pubKey) {
                throw new Exception("支付宝公钥格式错误，请检查公钥有效性");
            }

            $verifyResult = openssl_verify($paramStr, $sign, $pubKey, OPENSSL_ALGO_SHA256);
            if ($verifyResult !== 1) {
                // openssl_verify返回1=成功，0=失败，-1=异常
                throw new Exception("验签失败，verifyResult: {$verifyResult}");
            }

            return true;

        } catch (Exception $e) {
//            error_log("验签异常：" . $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * 辅助方法：过滤空值参数
     * @param array $params 参数数组
     * @return array 过滤后的参数数组
     */
    private static function filterEmptyParams(array $params): array
    {
        return array_filter($params, function ($value) {
            return $value !== '' && $value !== null && $value !== false;
        });
    }

    /**
     * 辅助方法：拼接参数为"key=value&key=value"格式
     * @param array $params 排序后的参数数组
     * @return string 拼接后的字符串
     */
    private static function joinParams(array $params): string
    {
        $paramParts = [];
        foreach ($params as $key => $value) {
            // 若文档要求URL编码，可添加：$value = urlencode($value);
            $paramParts[] = "{$key}={$value}";
        }
        return implode('&', $paramParts);
    }
}