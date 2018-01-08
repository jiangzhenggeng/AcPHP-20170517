<?php

class MajorAction extends MobileCommon{

	public function init(){

		$member_major_temp = M('member_major')->where(array(
			'memberid'=>$this->memberid
		))->getAll();
		$majorid_array = $member_major = [];

		if(count($member_major_temp)){
			foreach ($member_major_temp as $k =>$v ){
				$majorid_array[] = $v['major'];
				$member_major[$v['majorid']] = $v;
			}
			$subject = M('subject')->where(array(
				'majorid'=>[implode(',',$majorid_array),'IN']
			))->getAll();
			foreach ($subject as $k =>$v ){
				if(!isset($member_major[$v['majorid']]['subject']))
					$member_major[$v['majorid']]['subject'] = [];
				$member_major[$v['majorid']]['subject'][] = $v;
			}
		}
		$this->assign('member_major',$member_major);
		$this->display('member/member_major.html');
	}
	
}