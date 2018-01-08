<?php
require_once dirname(__FILE__).'/wxpay.Api.php';

class native{
    function getPayUrl(array $orderInfor){
        $input = new WxPayUnifiedOrder();
        $input->SetBody($orderInfor['name']);// 设置商品或支付单简要描述
        $input->SetAttach("jiguo");// 设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
        $input->SetOut_trade_no($orderInfor['orderid']);// 设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
        $input->SetTotal_fee($orderInfor['price']);//设置订单总金额，只能为整数，详见支付金额
        $input->SetTime_start(date("YmdHis"));//设置订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
        $input->SetTime_expire(date("YmdHis", time() + 7200));//设置订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。其他详见时间规则
        $input->SetGoods_tag("jiguo");//设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
        $input->SetNotify_url($orderInfor['callurl']);//设置接收微信支付异步通知回调地址
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($orderInfor['productid']);//设置trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
        $result = WxPayApi::unifiedOrder($input);
        return $result;
    }
}




