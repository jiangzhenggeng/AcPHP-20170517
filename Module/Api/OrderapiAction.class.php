<?php
/**
 * Created by PhpStorm.
 * User: jiangzg
 * Date: 16/10/15
 * Time: 18:01
 */
class OrderapiAction extends Common {

    public function add(){
        $order_no = trim(xInput::request('order_no'));
        $order_no=='' || xOut::json(outError('参数错误'));
        $order = xInput::post('order');
        $order_info = xInput::post('order_info');

        $time = time();
        $order_no = $this->radomOrderNo();

        $originalprice = $order['originalprice'];
        $couponprice = $order['couponprice'];
        $actualprice = $order['actualprice'];

        $insertDataOrder = array(
            'type'          =>intval($order['type']),
            'orderstatus'   =>0,
            'memberid'      =>$this->memberid,
            'order_no'       =>$order_no,
            'ordername'     =>$order['ordername'],
            'validity'      =>$time + 10 * 24 * 60 * 60,
            'originalprice' =>$originalprice,
            'couponprice'   =>$couponprice,
            'actualprice'   =>$actualprice,
            'addtime'       =>$time,
            'pay_status'    =>0,
            'pay_type'      =>$order['pay_type']
        );

        $orderid = M('order')->insert($insertDataOrder);
        if($orderid){
            $province = intval(xInput::request('province'));
            $city = intval(xInput::request('city'));
            $county = intval(xInput::request('county'));
            $regionid_array = array(0);
            if($province>=0) $regionid_array[] = $province;
            if($city>=0) $regionid_array[] = $city;
            if($county>=0) $regionid_array[] = $county;

            $regionData = M('region')->where(array('regionid'=>array(implode(',',$regionid_array),'in')))->getAll();
            $provincename = $cityname = $countyname = '';
            foreach ($regionData as $k => $v ){
                if($v['type']==1) $provincename = $v['regionname'];
                if($v['type']==2) $cityname = $v['regionname'];
                if($v['type']==3) $countyname = $v['regionname'];
            }
            $insertDataOrderInfo = array(
                'orderid'       =>$orderid,
                'province'      =>$province,
                'city'          =>$city,
                'county'        =>$county,
                'provincename'  =>$provincename,
                'cityname'      =>$cityname,
                'countyname'    =>$countyname,
                'address'       =>$provincename.$cityname.$countyname.xInput::request('address'),
                'phone'         =>$order_info['phone'],
                'zipcode'       =>$order_info['zipcode'],
                'consignee'     =>$order_info['consignee'],
                'message'       =>$order_info['message'],
                'orderdetail'   =>$order_info['orderdetail']
            );
            $result = M('order_info')->insert($insertDataOrderInfo);
            if($result){
                xOut::json(outSuccess('提交成功'));
            }else{
                xOut::json(outStatus(-1,'详细信息错误'));
            }
        }else{
            xOut::json(outError('系统错误'));
        }
    }

    public function edit(){
        $order_no = trim(xInput::request('order_no'));
        if(!$this->vOrder($order_no)){
            xOut::json(outError('订单号错误'));
        }
        if(xInput::request('query')==''){
            $orderModel = M('order');
            $orderData = $orderModel->where(array('order_no'=>$order_no))->getOne();
            if(!count($orderData)){
                $orderInfoData = M('order_info')->where(array('orderid'=>$orderData['orderid']))->getOne();
                array_merge($orderInfoData,$orderData);
                xOut::json(outSuccess($orderInfoData));
            }else{
                xOut::json(outError('订单不存在'));
            }
        }elseif(xInput::request('query')=='insert'){

        }
    }

    public function cancel(){
        $order_no = trim(xInput::request('order_no'));
        if(!$this->vOrder($order_no)){
            xOut::json(outError('订单号错误'));
        }
        $orderModel = M('order');
        $result = $orderModel->where(array('order_no'=>$order_no))->update(array('orderstatus'=>-1));
        if($result){
            xOut::json(outError('取消成功'));
        }else{
            xOut::json(outError('订单不存在'));
        }
    }

    public function delete(){
        $order_no = trim(xInput::request('order_no'));
        if(!$this->vOrder($order_no)){
            xOut::json(outError('订单号错误'));
        }
        $orderModel = M('order');
        $result = $orderModel->where(array('order_no'=>$order_no))->delete(array('orderstatus'=>-9));
        if($result){
            xOut::json(outError('删除成功'));
        }else{
            xOut::json(outError('订单不存在'));
        }
    }
}