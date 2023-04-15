<?php

namespace laocc\alipay\library;

interface PayFace
{
    public function app(array $params);

    public function jsapi(array $params);

    public function h5(array $params);

    public function query(array $params);

}