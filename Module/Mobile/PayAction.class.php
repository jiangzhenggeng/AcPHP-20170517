<?php

class PayAction extends MobileCommon{

    private function _checkPay($majorid){
        $member_major = $this->getMemberMajor($majorid);

        if( $member_major['buyed']==1 ){
            $order = M('order')->where(array('order_no'=>$member_major['order_no']))->getOne();
            if($order['pay_status']==4){
                $this->showMessage('你已支付，不需再次支付');
            }
        }
    }
    //购买专业
    public function buyMajor(){
        $majorid = intval(xInput::request('majorid'));
        $major = $this->getMajor($majorid);
        $subject = $this->getMajorSubjectList($majorid);

        if($majorid<=0 || count($major)<=0 || count($subject)<=0){
            $this->showMessage('参数错误');
        }

        $this->_checkPay($majorid);

        $this->assign('subject',$subject);

        $this->assign('major',$major);
        $this->assign('majorid',$majorid);
        $this->display('pay/buy_major.html');
    }

	public function pay(){

        //生成订单，用订单号调用支付接口
        $majorid = intval(xInput::request('majorid'));
        $major = $this->getMajor($majorid);
        $subject = $this->getMajorSubjectList($majorid);

        $this->_checkPay($majorid);

        //生成订单号
        $order_no = $this->radomOrderNo();

        $originalprice = 0.00;
        foreach ($subject as $k => $v ){
            $originalprice += $v['price'];
        }

        $originalprice = sc_retain_decimal($originalprice);
        $couponprice = $originalprice;
        $actualprice = $originalprice;

        $insertDataOrder = array(
            'type'          =>1,//题库订单
            'orderstatus'   =>1,
            'memberid'      =>$this->memberid,
            'username'      =>get_username(),
            'order_no'      =>$order_no,
            'ordername'     =>'购买'.$major['majorname'],
            'validity'      =>C_TIME + 10 * 24 * 60 * 60,
            'originalprice' =>0.01,
            'couponprice'   =>0,
            'actualprice'   =>0.01,
            'realprice'     =>0,
            'addtime'       =>C_TIME,
            'pay_status'    =>0,
            'pay_type'      =>'ali'
        );

        $orderid = M('order')->insert($insertDataOrder);
        if($orderid){
            $order = M('order')->field('order_no')->where(array('orderid'=>$orderid))->getOne();
            $count = M('member_major')->where(array('memberid'=>$this->memberid,'majorid'=>$majorid))->count();
            if($count<=0){
                M('member_major')->insert(array(
                    'memberid'=>$this->memberid,
                    'majorid'=>$majorid,
                    'buyed'=>0,
                    'starttime'=>C_TIME,
                    'endtime'=>C_TIME + 2 * 365 * 24 * 60 * 60,
                    'majorname'=>$major['majorname'],
                    'test'=>0,
                    'order_no'=>$order['order_no']
                ));
            }else{
                M('member_major')->where(array(
                    'memberid'=>$this->memberid,'majorid'=>$majorid
                ))->update(array('order_no'=>$order['order_no']));
            }
            header('Location: '.U('api/payapi/aLiPay',array('order_no'=>$order['order_no']) ));
        }else{
            $this->showMessage('系统错误');
        }
	}
	
}