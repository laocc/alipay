<?php

namespace laocc\alipay;

use esp\core\Library;
use esp\http\Http;
use laocc\alipay\extend\SignUtil;

class _AliBase extends Library
{
    public Entity $entity;
    private string $api = 'https://openapi.alipay.com/gateway.do?';

    public function _init(Entity $entity)
    {
        $this->entity = $entity;
    }


    protected function decodePost()
    {
        $str = file_get_contents('php://input');

        parse_str($str, $post);
        $verify = SignUtil::verifySign($post, $post['sign'], $this->entity->publicSerial);
        if (!$verify) return '验签失败';
        return $post;
    }

    protected function post(string $method, array $params, array $model): array|string
    {
        $urlParams = [
            'app_id' => $this->entity->mchid,
            'method' => $method,
            'format' => 'json',
            'charset' => 'UTF-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'sign' => '',
            'version' => '1.0',
//            'app_auth_token' => '',//采用第三方接入时需要
//            'biz_content' => '',//采用第三方接入时需要
        ];

        if (isset($params['notify'])) {
            $urlParams['notify_url'] = $params['notify'];
        }
        $this->debug($this->entity);

        $postData = [];
        $postData['biz_content'] = json_encode($model, 320);

        $urlParams['sign'] = SignUtil::generateSign($urlParams + $postData, $this->entity->privateSerial);
        $this->debug($urlParams);
        $fullUrl = $this->api . http_build_query($urlParams);

        $Option = [];
        $Option['header'] = true;
        $http = new Http($Option);
        $request = $http->data($postData)->post($fullUrl);
        $this->debug($request);
        if ($error = $request->error()) return $error;

        $data = $request->data();
        if (_CLI and ($this->entity->debug)) print_r($data);

        if ($params['verify'] ?? 1) {
//            $data[$params['key']]['sign'] = $data['sign'];
            $verify = SignUtil::verifySign($data[$params['key']], $data['sign'], $this->entity->publicSerial);
            $this->debug(['验签' => $verify]);
        }

        $code = intval($data[$params['key']]['code'] ?? 0);
        if ($code != 10000) return $data[$params['key']]['sub_msg'];

        return $data[$params['key']];
    }

}