<?php 
class IndexAction extends PcsCommon{
	
	 public function init(){
		 $this->display('index.html');
    }

    //获取专业及科目列表
    public function getmajorlist(){
        $memberMajorModel = M('member_major');
        $member_major = $memberMajorModel->where(array(
            'memberid'=>$this->memberid
        ))->field('majorid')->getAll();
        if(!count($member_major)){
            xOut::json(array(
                'data'=>null,
                'code'=>0,
                'status'=>'success',
                'message'=>'获取数据成功'
            ));
        }
        $member_major_id = array_map(function ($v){
            return $v['majorid'];
        },$member_major);
        unset($member_major);

        $majorModel = M('major');

        $member_major_temp = $majorModel->where(
            array('majorid' => array(implode(',',$member_major_id),'IN'))
        )->getAll();

        $member_major = [];
        foreach($member_major_temp as $k => $v ){
            $member_major[$v['majorid']] = $v;
        }

        $subject = M('subject')->where(
            array('majorid' => array(implode(',',$member_major_id),'IN'))
        )->getAll();

        foreach($subject as $k => $v ){
            $member_major[$v['majorid']]['subject'][] = $v;
        }

        xOut::json(array(
            'data'=>$member_major,
            'code'=>0,
            'status'=>'success',
            'message'=>'获取数据成功'
        ));
    }
}