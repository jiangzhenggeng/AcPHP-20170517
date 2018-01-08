<?php
/**
 * Created by PhpStorm.
 * User: jiangzg
 * Date: 16/10/15
 * Time: 18:01
 */

App::setConfig('Config','debug',false);

class PayapiAction extends Common {

    public function test(){

        $this->assign('message','订单异常');
        $this->display('pay_return.html');

    }

    private function _setMajorSubjectHasMy( $order_no ){
        $orderData = xTempCache::get('order_no'.md5($order_no));
        if(!$orderData){
            $orderData = M('order')->where(array('order_no'=>$order_no))->getOne();
        }
        $memberid = $orderData['memberid'];
        $note = explode('|',$orderData['note']);
        $major_ids = $note[0];
        $subject_ids = $note[1];
        if(!$subject_ids || !$major_ids)return;

        $subject = M('subject')->where(['subjectid'=>[$subject_ids,'in']])->getAll();

        $major = M('major')->where(['majorid'=>[$major_ids,'in']])->getAll();
        $major_name = [];
        foreach ($major as $k => $v ){
            $major_name[$v['majorid']] = $v['majorname'];
        }

        $insertData = [];
        foreach ($subject as $k => $v ){
            $insertData[] = array(
                'memberid'=>$memberid,
                'majorid'=>$v['majorid'],
                'buyed'=>1,
                'starttime'=>C_TIME,
                'endtime'=>C_TIME + 2 * 365 * 24 * 60 * 60,
                'majorname'=>$major_name[$v['majorid']],
                'order_no'=>$order_no,
                'status'=>2,
                'subjectid'=>$v['subjectid'],
                'subjectname'=>$v['subjectname']
            );
        }
        M('member_subject')->insert($insertData);
    }

    public function aLiPay(){
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $order_no = trim(xInput::get('order_no'));
        if(!$this->vOrder($order_no)){
            $this->showMessage('订单号不存在1');exit;
        }

        $orderModel = M('order');
        $orderData = $orderModel->where(array('order_no'=>$order_no))->getOne();

        if(count($orderData)<=0){
            $this->showMessage('订单号不存在2');exit;
        }
        if($orderData['orderstatus']<1){
            $this->showMessage('订单无效');exit;
        }
        if($orderData['orderstatus']>1){
            $this->showMessage('订单正在进行中');exit;
        }
        if($orderData['pay_status']==4){
            $this->showMessage('订单已支付');exit;
        }
        //订单名称，必填
        $subject = $orderData['ordername']?$orderData['ordername']:'系统订单';
        //付款金额，必填
        $total_fee = $orderData['actualprice']?$orderData['actualprice']:'0.01';
        //商品描述，可空
        $body = $orderData['description'];
        //付款超时时间
        $time = '1c';

        require_once C_COMMON_PATH.'PayMent/AliPay/alipay_submit.class.php';
        $alipay_config = & App::getConfig('Alipay');
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service"       => $alipay_config['service'],
            "partner"       => $alipay_config['partner'],
            "seller_id"     => $alipay_config['seller_id'],
            "payment_type"	=> $alipay_config['payment_type'],
            "notify_url"	=> $alipay_config['notify_url'],
            "return_url"	=> $alipay_config['return_url'],

            "anti_phishing_key"=>$alipay_config['anti_phishing_key'],
            "exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
            "out_trade_no"	=> $order_no,
            "subject"	=> $subject,
            "total_fee"	=> $total_fee,
            "body"	=> $body,
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset'])),
            "it_b_pay"=>$time//超时间时间1分钟
            //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
            //如"参数名"=>"参数值"
        );
        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        $orderModel->where(array('order_no'=>$order_no))->update(array('pay_status'=>2));
        echo $html_text;
    }


    public function aLiReturnUrl(){
        require_once C_COMMON_PATH.'PayMent/AliPay/alipay_notify.class.php';
        $alipay_config = & App::getConfig('Alipay');

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        //验证成功
        if($verify_result) {
            //商户订单号
            $out_trade_no = $_GET['out_trade_no'];
            //支付宝交易号
            $trade_no = $_GET['trade_no'];
            //交易状态
            $trade_status = $_GET['trade_status'];

            $order_no = preg_replace('/[^0-9]/','',$out_trade_no);
            if(strlen($order_no)!=22){
                $this->assign('message','订单异常');
                $this->display('pay_return.html');
                exit;
            }

            $orderModel = M('order');

            $_update = array();
            if($trade_no!='') $_update['trade_no'] = $trade_no;
            if($trade_status!='') $_update['trade_status'] = $trade_status;

            M('order_pay_info')->insert(array('order_no'=>$order_no, 'type'=>1, 'detail'=>var_export($_GET,TRUE)));

            $orderData = $orderModel->where(array('order_no'=>$order_no))->getOne();
            xTempCache::set('order_no'.md5($order_no),$orderData);

            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                if( $orderData['pay_status']==2 ){
                    $_update['pay_status'] = 3;
                    $orderModel->where(array('order_no'=>$order_no))->update($_update);
                }

                //查询科目
                if( $orderData['type']==1 ){
                    $this->_setMajorSubjectHasMy($order_no);
                }
                $this->assign('message','支付成功');
                $this->display('pay_return.html');

            } else {
                if(!empty($_update)) {
                    $orderModel->where(array('order_no'=>$order_no))->update($_update);
                }
                $this->assign('message','系统异常');
                $this->display('pay_return.html');
            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            $this->assign('message','验证失败');
            $this->display('pay_return.html');
        }
    }


    public function aLiNotifyUrl(){
        App::setConfig('Config','debug',false);
        require_once C_COMMON_PATH.'PayMent/AliPay/alipay_notify.class.php';
        $alipay_config = & App::getConfig('Alipay');

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        //验证成功
        if($verify_result) {

            $order_no = $out_trade_no = preg_replace('/[^0-9]/','',xInput::post('out_trade_no'));//商户订单号
            $trade_no = xInput::post('trade_no');//支付宝交易号
            $trade_status = xInput::post('trade_status');//交易状态

            if(xInput::post('trade_status') == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            } else if (xInput::post('trade_status') == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //付款完成后，支付宝系统发送该交易状态通知
                //判断收款方id是否正确
                if(xInput::post('seller_id')==$alipay_config['seller_id']){

                    M('order_pay_info')->insert(array('order_no'=>$order_no, 'type'=>2, 'detail'=>var_export($_POST,TRUE)));
                    //判断收款金额是否正确

                    $orderModel = M('order');
                    $orderData = $orderModel->where(array('order_no'=>$order_no))->getOne();
                    $_update = array();

                    if($trade_no!='') $_update['trade_no'] = $trade_no;
                    if($trade_status!='') $_update['trade_status'] = $trade_status;

                    $_update['realprice'] = $_POST['total_fee'];
                    $_update['pay_status'] = 4;
                    $_update['orderstatus'] = 6;
                    //支付金额相等
                    if($orderData['actualprice'] == $_POST['total_fee']){
                        //设置支付订单状态
                        $_update['orderstatus'] = 5;
                    }
                    $orderModel->where(array('order_no'=>$order_no))->update($_update);

                    //member_major
                    M('member_major')->where(array('order_no'=>$order_no))->update(['buyed'=>1]);
                }
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            echo "success";		//请不要修改或删除
        } else {
            //验证失败
            echo "fail";
            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }
}