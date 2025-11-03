<?php

namespace laocc\alipay;

use laocc\alipay\library\PayFace;
use function esp\helper\rnd;

class Payment extends _AliBase implements PayFace
{

    /**
     * https://opendocs.alipay.com/mini/080p65?pathHash=216e5c27
     *
     * @return array|string
     */
    public function notify()
    {
        $post = $this->decodePost();
        if (is_string($post)) return $post;
        $this->debug($post);

        if ($post['trade_status'] === 'WAIT_BUYER_PAY') return 'waiting';

        if (isset($post['refund_fee'])) {
            return [
                'type' => 'refund',//通知类型
                'success' => true,//是否支付成功
                'status' => '',//状态
                'number' => $post['out_biz_no'],//本平台订单号
                'waybill' => '',//支付宝单号
                'amount' => intval(floatval($post['refund_fee']) * 100),//金额
                'time' => strtotime($post['gmt_refund']),
            ];
        }

        $state = [
            'WAIT_BUYER_PAY' => '等待买家付款',
            'TRADE_CLOSED' => '未付款交易超时关闭',
            'TRADE_SUCCESS' => '交易支付成功',
            'TRADE_FINISHED' => '交易结束且不可退款',
        ];

        return [
            'type' => 'payment',//通知类型

            'success' => ($post['trade_status'] === 'TRADE_SUCCESS'),//是否支付成功
            'status' => $post['trade_status'],//状态
            'number' => $post['out_trade_no'],//本平台订单号
            'waybill' => $post['trade_no'],//支付宝单号
            'amount' => intval(floatval($post['total_amount']) * 100),//金额
            'buyer' => $post['buyer_logon_id'],
            'desc' => $state[$post['trade_status']] ?? $post['trade_status'],
            'time' => strtotime($post['gmt_payment']),
        ];
    }

    /**
     * https://opendocs.alipay.com/mini/83b6c9a9_alipay.trade.query?scene=common&pathHash=354e8be3
     *
     * @param array $params
     * @return array|string
     */
    public function query(array $params): array|string
    {
        $model = array();
        $model['out_trade_no'] = $params['number'];// 设置商户订单号

        $params['verify'] = false;//暂不验证签名

        $params['key'] = 'alipay_trade_query_response';
        $data = $this->post('alipay.trade.query', $params, $model);
        if (is_string($data)) return $data;

        $state = [
            'WAIT_BUYER_PAY' => '等待买家付款',
            'TRADE_CLOSED' => '未付款交易超时关闭',
            'TRADE_SUCCESS' => '交易支付成功',
            'TRADE_FINISHED' => '交易结束且不可退款',
        ];

        return [
            'success' => ($data['trade_status'] === 'TRADE_SUCCESS'),//是否支付成功
            'status' => $data['trade_status'],//状态
            'number' => $data['out_trade_no'],//本平台订单号
            'waybill' => $data['trade_no'],//支付宝单号
            'amount' => intval(floatval($data['total_amount']) * 100),//金额
            'buyer' => $data['buyer_logon_id'],
            'desc' => $state[$data['trade_status']] ?? $data['trade_status'],
            'time' => strtotime($data['send_pay_date'] ?? ''),
        ];
    }


    /**
     * @param array $params
     * @return array|string
     *
     * 统一下单：
     * https://opendocs.alipay.com/mini/6039ed0c_alipay.trade.create?scene=de4d6a1e0c6e423b9eefa7c3a6dcb7a5&pathHash=779dc517
     * https://opendocs.alipay.com/mini/6039ed0c_alipay.trade.create?scene=de4d6a1e0c6e423b9eefa7c3a6dcb7a5&pathHash=779dc517
     *
     * 调起支付：https://opendocs.alipay.com/mini/05xhsr?pathHash=d4709298&ref=api
     */
    public function jsapi(array $params): array|string
    {
        $model = array();
        $model['out_trade_no'] = $params['number'];// 设置商户订单号
        $model['total_amount'] = rnd($params['total'] / 100);// 设置订单总金额
        $model['subject'] = $params['subject'];// 设置订单标题
        $model['product_code'] = "JSAPI_PAY";// 设置产品码
        $model['op_app_id'] = $this->entity->appid;// 设置小程序支付中

        $model['buyer_open_id'] = $params['openid'];

        // 设置买家支付宝用户唯一标识（商户实际经营主体的小程序应用关联的买家open_id）
        $model['op_buyer_open_id'] = $params['openid'];

//        $model['seller_id'] = "2021005155656206";// 签约应用，即收款账号
//        $model['seller_id'] = $params['appid'];// 签约应用，即收款账号

        $model['body'] = $params['nonce_str'];// 设置订单附加信息
//        $model['time_expire'] = date('Y-m-d H:i:s', time() + 3600);// 设置订单绝对超时时间
        $model['timeout_express'] = "1h";// 设置订单相对超时时间，取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。
//        $model['passback_params'] = $params['nonce_str'];// 设置公用回传参数


        $goodsDetail = array();// 设置订单包含的商品列表信息
        $goodsDetail0 = array();
        $goodsDetail0['out_sku_id'] = "outSku_01";
        $goodsDetail0['goods_name'] = "ipad";
        $goodsDetail0['alipay_goods_id'] = "20010001";
        $goodsDetail0['quantity'] = 10;
        $goodsDetail0['price'] = "2000";
        $goodsDetail0['out_item_id'] = "outItem_01";
        $goodsDetail0['goods_id'] = "apple-01";
        $goodsDetail0['goods_category'] = "34543238";
        $goodsDetail0['categories_tree'] = "124868003|126232002|126252004";
        $goodsDetail0['body'] = "特价手机";
        $goodsDetail0['show_url'] = "http://www.alipay.com/xxx.jpg";
        $goodsDetail[] = $goodsDetail0;
//        $model['goods_detail'] = $goodsDetail;

        $extendParams = array();// 设置业务扩展参数
        $extendParams['sys_service_provider_id'] = "2088511833207846";
        $extendParams['hb_fq_seller_percent'] = "100";
        $extendParams['hb_fq_num'] = "3";
        $extendParams['trade_component_order_id'] = "2023060801502300000008810000005657";
//        $model['extend_params'] = $extendParams;


        $businessParams = array();// 设置商户传入业务信息
        $businessParams['enterprise_pay_info'] = "{\"category_list\":[{\"price\":\"10.24\",\"name\":\"餐饮服务\",\"category\":\"3070401000000000000\"}]}";
        $businessParams['enterprise_pay_amount'] = "10.00";
        $businessParams['tiny_app_merchant_biz_type'] = "KX_SHOPPING";
        $businessParams['mc_create_trade_ip'] = "127.0.0.1";
//        $model['business_params'] = $businessParams;

//        $model['discountable_amount'] = "80.00";// 设置可打折金额
//        $model['undiscountable_amount'] = "8.88";// 设置不可打折金额
//        $model['store_id'] = "NJ_001";// 设置商户门店编号
//        $model['alipay_store_id'] = "2016041400077000000003314986";// 设置支付宝店铺编号

//        $model['disable_pay_channels'] = "pcredit,moneyFund,debitCardExpress";// 设置禁用渠道
//        $model['enable_pay_channels'] = "pcredit,moneyFund,debitCardExpress";// 设置指定支付渠道

        $queryOptions = array();// 设置返回参数选项
        $queryOptions[] = "enterprise_pay_info";
        $queryOptions[] = "hyb_amount";
//        $model['query_options'] = $queryOptions;

        $agreementSignParams = array();// 设置签约参数
        $subMerchant = array();
        $subMerchant['sub_merchant_name'] = "滴滴出行";
        $subMerchant['sub_merchant_service_name'] = "滴滴出行免密支付";
        $subMerchant['sub_merchant_service_description'] = "免密付车费，单次最高500";
        $subMerchant['sub_merchant_id'] = "2088123412341234";
        $agreementSignParams['sub_merchant'] = $subMerchant;
        $accessParams = array();
        $accessParams['channel'] = "ALIPAYAPP";
        $agreementSignParams['access_params'] = $accessParams;
        $periodRuleParams = array();
        $periodRuleParams['period'] = 3;
        $periodRuleParams['total_amount'] = "600";
        $periodRuleParams['execute_time'] = "2019-01-23";
        $periodRuleParams['single_amount'] = "10.99";
        $periodRuleParams['total_payments'] = 12;
        $periodRuleParams['period_type'] = "DAY";
        $agreementSignParams['period_rule_params'] = $periodRuleParams;
        $agreementSignParams['sign_notify_url'] = "http://www.merchant.com/receiveSignNotify";
        $agreementSignParams['external_logon_id'] = "13888888888";
        $agreementSignParams['personal_product_code'] = "CYCLE_PAY_AUTH_P";
        $agreementSignParams['external_agreement_no'] = "test20190701";
        $agreementSignParams['product_code'] = "GENERAL_WITHHOLDING";
        $agreementSignParams['sign_scene'] = "INDUSTRY|DIGITAL_MEDIA";
//        $model['agreement_sign_params'] = $agreementSignParams;

        $params['key'] = 'alipay_trade_create_response';
        $data = $this->post('alipay.trade.create', $params, $model);
        if (is_string($data)) return $data;

        return [
            'number' => $data['out_trade_no'],
            'waybill' => $data['trade_no'],
        ];

    }

    public function app(array $params)
    {
    }

    public function h5(array $params)
    {
    }

}