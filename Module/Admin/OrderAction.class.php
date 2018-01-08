<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月8日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月8日 上午1:09:25
// +--------------------------------------------------------------------------------------

// +--------------------------------------------------------------------------------------
// + 订单管理模块
// +--------------------------------------------------------------------------------------
class OrderAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
	}
	
	//元素列表
	public function lists($orderstatus=null){
        $orderModel = M('order');
        if( !is_null($orderstatus) ){
            $orderModel->where(array('orderstatus'=>$orderstatus));
        }

        $pay_status = intval(xInput::get('pay_status','999999'));
        if( $pay_status!=999999 ){
            $orderModel->where(array('pay_status'=>$pay_status));
            $this->assign('pay_status', $pay_status);
        }

        $search = xInput::get('search');
        if( isset($search['order_no']) && $search['order_no']!='' ){
            $orderModel->where(array('order_no'=>$search['order_no']));
        }
        if( isset($search['username']) && $search['username']!='' ){
            $orderModel->where(array('username'=>array($search['username'],'%LIKE%')));
        }
        if( isset($search['start_addtime']) && $search['start_addtime']!='' ){
            $orderModel->where(array('addtime'=>array(strtotime($search['start_addtime']),'>=')));
        }
        if( isset($search['end_addtime']) && $search['end_addtime']!='' ){
            $orderModel->where(array('addtime'=>array(strtotime($search['end_addtime']),'<=')));
        }
        if( isset($search['pay_type']) && $search['pay_type']!='' ){
            $orderModel->where(array('pay_type'=>$search['pay_type']));
        }

        $count = $orderModel->count(false);
        $page = $this->page($count);
        $orderModel->limit($page->getLimit());
        $order = $orderModel->order('orderid desc')->getAll();
        $this->assign('order', $order);
        $this->assign('search', $search);
        $this->assign('page', $page->show());
        $this->display('order/order_list.html');
	}

    //完成订单
    public function complete(){ $this->lists(5); }
    public function waitpay(){ $this->lists(1); }
    public function waitgoods(){ $this->lists(3); }
    public function waitsend(){ $this->lists(2); }
    public function waitreimburse(){ $this->lists(4); }
    public function deleteorder(){ $this->lists(-9); }
    public function refundorder(){ $this->lists(-4); }
    public function invalidorder(){ $this->lists(-3); }
    public function closeorder(){ $this->lists(-2); }
    public function cancelorder(){ $this->lists(-1); }

	
	public function add(){

	}

	public function edit(){

	}
	
	public function delete(){
        $orderid = intval(xInput::get('orderid'));
        if($orderid<=0) $this->error();
        $orderModel = M('order')->where(array('orderid'=>$orderid));
        $result = $orderModel->update(array('orderstatus'=>-9));
        if($result){
            $this->success();
        }
        $this->error();
	}
	//排序
	public function listorder($model){
		if(parent::listorder(M('accelements'))){
			$this->success();
		}
		$this->error();
	}
}

