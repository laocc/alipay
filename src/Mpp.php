<?php

namespace laocc\alipay;

use laocc\alipay\extend\Aes;

class Mpp extends _AliBase
{

    /**
     * @param array $signed
     * @param string $sessionKey
     * @return array|string
     */
    public function parseMobile(array $signed, string $sessionKey = ''): array|string
    {
        $aes = 'tD5pGmQHxWDUlBxwmMoOVg==';
        $mobile = Aes::decrypt($signed['encryptedData'], $aes);
        if (is_string($mobile)) return $mobile;

        return ['phone' => $mobile['mobile']];
    }


    /**
     * https://opendocs.alipay.com/mini/84bc7352_alipay.system.oauth.token?scene=common&pathHash=c6cfe1ef
     * @param array $params
     * @return string|array
     */
    public function getOpenid(array $params): array|string
    {
        /**
         * 用授权码来换取授权令牌: authorization_code
         * 用刷新令牌来换取一个新的授权令牌: refresh_token
         */
        $postData = [
            'grant_type' => 'authorization_code',
//            'refresh_token' => '实际的refresh_token',
            'code' => $params['code'],
        ];

        $params['key'] = 'alipay_system_oauth_token_response';
        $data = $this->post('alipay.system.oauth.token', $params, $postData);
        if (is_string($data)) return $data;

        return [
            'access_token' => $data['access_token'],
            'refresh' => $data['refresh_token'],
            'expires' => $data['expires_in'],
            'openid' => $data['open_id'],
            'unionid' => $data['union_id'],
        ];
    }

    public function qrcode(array $params): array|string
    {
        $query = $params['query'] ?? '';
        if (is_array($query)) $query = http_build_query($query);

        $postData = [
            'url_param' => $params['page'] ?? 'init',
            'query_param' => $query,
            'describe' => $params['desc'] ?? date('Y-m-d H:i:s'),
            'color' => $params['color'] ?? '0x000000',
            'size' => $params['size'] ?? 's',
        ];

        $params['key'] = 'alipay_open_app_qrcode_create_response';
        $data = $this->post('alipay.open.app.qrcode.create', $params, $postData);
        if (is_string($data)) return $data;

        return [
            'access_token' => $data['access_token'],
            'refresh' => $data['refresh_token'],
            'expires' => $data['expires_in'],
            'openid' => $data['open_id'],
            'unionid' => $data['union_id'],
        ];
    }

}