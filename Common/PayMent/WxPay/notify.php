<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once dirname(__FILE__)."/wxpay.Api.php";
require_once dirname(__FILE__)."/wxpay.Notify.php";
require_once dirname(__FILE__)."/wxpay.Config.php";
require_once dirname(__FILE__)."/log.php";
require_once dirname(dirname(__FILE__))."/Db.php";


//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);
class PayNotifyCallBack extends WxPayNotify
{
    //数据对象
    private $db = null;
    const MCHID = '1259955201';
    public function __construct(){
        if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
            parent::__construct($id='');
        }
    }
    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        Log::DEBUG("query:" . json_encode($result));
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            //设置支付订单状态

            //判断收款方id是否正确

            if($result['mch_id'] == self::MCHID){
                //判断收款金额是否正确
                $db = Db::getInstance();
                $result['out_trade_no'] = preg_replace('/[^0-9]/','',$result['out_trade_no']);
                $sql1 = 'SELECT `meta`,`orderid`,`payment` FROM `event_order` where `orderid`="'.$result['out_trade_no'].'"';
                $order = $db->query($sql1);
                $price = json_decode($order[0]['meta'],TRUE);
                //支付金额相等
                $time = time();
                //if(($price['price']*100 == ($result['total_fee'])) && ($order[0]['payment']=='wx')){
                    //设置支付订单状态
                    $db1 = Db::getInstance();
                    $sql2 = 'UPDATE `event_order` SET `status`=1,`paytime`='.$time.' WHERE `orderid`="'.$result['out_trade_no'].'"';
                    $db1->query($sql2);
                //}
                //插入订单信息
                $db2 = Db::getInstance();
                $time = time();
                $total_fee = $result['total_fee']/100;
                $sql3 = "INSERT INTO `event_order_list`(`orderid`,`meta`,`type`,`paytime`,`price`) VALUE('{$result['out_trade_no']}','".json_encode($result)."','wx',$time,'$total_fee')";
                $db2->query($sql3);
            }


            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        Log::DEBUG("call back:" . json_encode($data));
        $notfiyOutput = array();

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }
        return true;
    }
}

Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false);