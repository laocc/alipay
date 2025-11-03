<?php

namespace laocc\alipay;

use function esp\helper\rnd;

class Refund extends _AliBase
{

    /**
     * https://opendocs.alipay.com/mini/824da765_alipay.trade.refund?scene=common&pathHash=b18b975d
     * https://opendocs.alipay.com/support/01rawa
     * @param array $params
     * @return array|string
     */
    public function submit(array $params): array|string
    {
        $model = array();
        $model['refund_amount'] = rnd($params['amount'] / 100);// 金额
//        $model['out_trade_no'] = $params['order'];// 支付时商户单号
        $model['trade_no'] = $params['waybill'];// 支付单号
        $model['refund_reason'] = $params['reason'];// 原因
        $model['out_request_no'] = $params['number'];// 退款单号
        $model['query_options'] = ['deposit_back_info', 'refund_detail_item_list'];

        $params['verify'] = false;//暂不验证签名
        $params['key'] = 'alipay_trade_refund_response';
        $data = $this->post('alipay.trade.refund', $params, $model);
        if (is_string($data)) return $data;

        return [
            'success' => ($data['fund_change'] === 'Y'),
            'state' => $data['fund_change'],
            'waybill' => $data['trade_no'],
            'number' => $data['out_trade_no'],
            'time' => strtotime($data['gmt_refund_pay']),
            'amount' => intval(floatval($data['refund_fee']) * 100),
        ];
    }

    /**
     * @param array $params
     * @return array|string
     */
    public function query(array $params): array|string
    {
        $model = array();
        $model['out_request_no'] = $params['number'];// 本平台退款单号
        $model['trade_no'] = $params['pay_waybill'];// 支付时单号
        $model['query_options'] = ["refund_detail_item_list", 'gmt_refund_pay'];// 设置商户订单号

        $params['verify'] = false;//暂不验证签名
        $params['key'] = 'alipay_trade_fastpay_refund_query_response';
        $data = $this->post('alipay.trade.fastpay.refund.query', $params, $model);
        if (is_string($data)) return $data;

        if (!isset($data['refund_status'])) {
            return [
                'success' => false,
                'status' => 'null',
                'desc' => '退款未成功',
            ];
        }

        $state = ['REFUND_SUCCESS' => '退款成功',];
        return [
            'success' => ($data['refund_status'] === 'REFUND_SUCCESS'),//是否支付成功
            'status' => $data['refund_status'],//状态
            'number' => $data['out_trade_no'],//本平台订单号
            'waybill' => $data['trade_no'],//支付宝单号
            'amount' => intval(floatval($data['refund_amount']) * 100),//金额
            'desc' => $state[$data['refund_status']] ?? $data['refund_status'],
            'time' => strtotime($data['gmt_refund_pay'] ?? ''),
        ];
    }

}