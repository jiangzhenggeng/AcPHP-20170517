<?php 
class MajorAction extends PcsCommon{
	
	 public function init(){
	     $memberMajorModel = M('member_major');

         $count = $memberMajorModel->where(array(
             'memberid'=>$this->memberid
         ))->count(false);
         $page = $this->page($count);
         $major = $memberMajorModel->limit($page->getLimit())->getAll();

         $this->assign('page', $page->show());
         $this->assign('major',$major);
		 $this->display('my_major.html');
    }

    public function add(){
        if(xInput::request('query')!='insert'){

            $memberMajorModel = M('member_major');
            $member_major = $memberMajorModel->where(array(
                'memberid'=>$this->memberid
            ))->field('majorid')->getAll();

            $member_major = array_map(function ($v){
                return $v['majorid'];
            },$member_major);

            $majorModel = M('major');

            $count = $majorModel->where(
                array('majorid' => array(implode(',',$member_major),'NOT IN'))
            )->count(false);
            $page = $this->page($count);
            $major = $majorModel->limit($page->getLimit())->getAll();

            $this->assign('page', $page->show());
            $this->assign('major',$major);
            $this->display('list.html');
        }
        $majorid = xInput::request('majorid');
        if(!is_array($majorid) && count($majorid)<=0){
            xOut::json(array(
                'code'=>-1,
                'status'=>'error',
                'message'=>'没有选择专业'
            ));
        }
        $majorid = array_filter($majorid,function ($v){
            return intval($v);
        });
        $major_data = M('major')->where(array(
            'majorid'=>[implode(',',$majorid),'in']
        ))->getAll();

        $memberMajorModel = M('member_major');
        $member_major = $memberMajorModel->where(array(
            'memberid'=>$this->memberid
        ))->field('majorid')->getAll();

        $member_major = array_map(function ($v){
            return $v['majorid'];
        },$member_major);

        $insertData = [];
        foreach ($major_data as $k => $v ){
            if(in_array($v['majorid'],$member_major)){
                continue;
            }
            $insertData[] = array(
                'memberid'=>$this->memberid,
                'majorid'=>$v['majorid'],
                'starttime'=>time(),
                'majorname'=>$v['majorname'],
                'validity'=>1
            );
        }
        if(count($insertData)){
            $result = M('member_major')->insert($insertData);
        }else{
            $result = true;
        }
        if($result){
            xOut::json(array(
                'code'=>0,
                'status'=>'success',
                'message'=>'添加成功'
            ));
        }else{
            xOut::json(array(
                'code'=>-1,
                'status'=>'erroe',
                'message'=>'添加失败'
            ));
        }
    }

}