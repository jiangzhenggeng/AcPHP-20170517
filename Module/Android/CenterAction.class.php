<?php

class CenterAction extends ApiCommon  {
	
    public function MyCenterSubjectList(){

		$memberid = intval(xInput::request('memberid'));
		$assign = ['my_subject'=>[],'subject'=>[]];
        if($memberid){
			$mySubjectData = M('member_subject')->where([
			    'memberid'=>$memberid,
                'buyed'=>1,
                'status'=>2,
            ])->getAll();

			$my_subjectid = [];
			foreach($mySubjectData as $k => $v ){
				$my_subjectid[] = $v['subjectid'];
			}
			$assign['my_subject'] = subject_class($mySubjectData);
		}
        $subjectModel = M('subject');
		if(count($my_subjectid)){
			$subjectModel->where(['subjectid'=>[$my_subjectid,'not in']]);
		}
        $subjectData = $subjectModel->order('listorder desc,subjectid desc')->getAll();
		$assign['subject'] = subject_class($subjectData);
        xOut::json(outSuccess( $assign ));
    }
	
	public function myCenterSubjectPay(){
        $subject_ids = xInput::request('subject_ids');
        $memberid = intval(xInput::request('memberid'));
        if( $memberid<=0 ){
        	xOut::json(outError( '请先登录' ));
        }
		
		if(!is_array($subject_ids) || count($subject_ids)<=0 ){
        	xOut::json(outError( '请先选择购买项目' ));
        }
		$subject_ids = array_map(function($item){
			return intval($item);
		},$subject_ids);
		
		if(count($subject_ids)<=0){
        	xOut::json(outError( '请先选择购买项目' ));
		}
		//判断是否已经购买过
        $count = M('member_subject')->where([
            'memberid'=>$memberid,
            'buyed'=>1,
            'status'=>2,
            'subjectid'=>[$subject_ids,'in']
        ])->count();

        if($count){
            xOut::json(outError( '你已经购买过' ));
        }

        $subject = M('subject')->where(['subjectid'=>[$subject_ids,'in']])->getAll();


		$major_ids = [];
		$subject_name_array = [];
        $originalprice = 0.00;
        foreach ($subject as $k => $v ){
            $originalprice += $v['price'];
			$subject_name_array[] = $v['subjectname'];
			if(!in_array($v['majorid'],$major_ids)){
				$major_ids[] = $v['majorid'];
			}
        }

        $major = M('major')->where(['majorid'=>[$major_ids,'in']])->getAll();
        $major_name = [];
        foreach ($major as $k => $v ){
            $major_name[$v['majorid']] = $v['majorname'];
        }
        $ordername = implode('，',$major_name);

        $note = implode(',',$major_ids).'|'.implode(',',$subject_ids);
		$description = '购买科目：'.implode('，',$subject_name_array);
		
        $originalprice = 0.01;//sc_retain_decimal($originalprice);
        $couponprice = $originalprice;
        $actualprice = $originalprice;
		$member = M('member')->where(['memberid'=>$memberid])->getOne();
		//生成订单号
        $order_no = $this->radomOrderNo();
        $insertDataOrder = array(
            'type'          =>1,
            'orderstatus'   =>1,
            'memberid'      =>$memberid,
            'username'      =>$member['nickname'],
            'order_no'      =>$order_no,
            'ordername'     =>$ordername,
			'description'	=>$description,
            'validity'      =>C_TIME + 10 * 24 * 60 * 60,
            'originalprice' =>$couponprice,
            'couponprice'   =>0,
            'actualprice'   =>$actualprice,
            'realprice'     =>0,
            'addtime'       =>C_TIME,
            'pay_status'    =>0,
            'pay_type'      =>'ali',
            'note'          =>$note
        );

        $orderid = M('order')->insert($insertDataOrder);
		
        if($orderid){
            xOut::json(outSuccess( [
                'url'=> C_HTTP.U('api/payapi/aLiPay',array('order_no'=>$order_no) )
            ] ));
        }else{
            xOut::json(outError( '购买失败' ));
        }

		
	}

	//个人中心基本信息
    public function getCenterInfo(){
        $memberid = intval(xInput::request('memberid'));
        if( $memberid<=0 ){
            xOut::json(outError( '请先登录' ));
        }
        $assign = [];

        //我的考试记录
        $count = M('paper_me')->where([
            'memberid'=>$memberid,
        ])->count();
        $assign['paper_number'] = $count;

        //我的模拟记录
        $count = M('paper_me')->where([
            'memberid'=>$memberid,
        ])->count();
        $assign['paper_number'] = $count;

        //我的练习记录
        $count = M('member_practice')->where([
            'memberid'=>$memberid,
            'type'=>2,
        ])->count();
        $assign['practice_number'] = $count;

        //我的强化记录
        $count = M('member_practice')->where([
            'memberid'=>$memberid,
            'type'=>3,
        ])->count();
        $assign['strong_number'] = $count;

        //我的收藏记录
        $count = M('favorite')->where([
            'memberid'=>$memberid,
        ])->count();
        $assign['favorite_number'] = $count;

        //我的错题记录
        $count = M('member_error')->where([
            'memberid'=>$memberid,
        ])->count();
        $assign['error_number'] = $count;


        //我的科目数量
        $count = M('member_subject')->where([
            'memberid'=>$memberid,
        ])->count();
        $assign['subject_number'] = $count;
        //我的订单数量
        $count = M('order')->where([
            'memberid'=>$memberid,
        ])->count();
        $assign['order_number'] = $count;

        xOut::ajax( outSuccess( $assign ));
    }

}




















