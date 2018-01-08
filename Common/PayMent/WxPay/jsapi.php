<?php
ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);
require_once dirname(__FILE__)."/wxpay.Api.php";

require_once dirname(__FILE__)."/WxPay.JsApiPay.php";
require_once dirname(__FILE__).'/log.php';


//初始化日志
/*
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);
*/
//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
class jsapi{
	function jsApiParameters(array $orderInfor){
		//①、获取用户openid
		$tools = new JsApiPay();
		//$openId = $tools->GetOpenid();
		$openId = 'oHY3KjgPRgEapVR44WxCznop7Cyo';

		//②、统一下单
		$input = new WxPayUnifiedOrder();
		$input->SetBody($orderInfor['name']);
		$input->SetAttach('jiguo');
		$input->SetOut_trade_no($orderInfor['orderid']);
		$input->SetTotal_fee($orderInfor['price']);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("jiguo");
		$input->SetNotify_url($orderInfor['callurl']);
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = WxPayApi::unifiedOrder($input);
//		if(!array_key_exists("appid", $order) 
//			|| !array_key_exists("prepay_id", $order)
//        	|| $order['prepay_id'] == "")
//         {
//			             return FALSE;
//         }
		$jsApiParameters = @$tools->GetJsApiParameters($order);
		return $jsApiParameters;
		
	}
}
