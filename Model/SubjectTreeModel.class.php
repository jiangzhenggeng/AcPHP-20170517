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
// + Release Date: 2015年11月8日 下午10:28:56
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

class SubjectTreeModel extends xModel{
	public function __construct($database = NULL){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct($database);
		}
	}
	
	public function repair($subjectid,$parentid=0,$arrparentid=0){
		set_time_limit(0);
		$this->repairArrParentid($subjectid,$parentid,$arrparentid);
		$this->repairArrChildid($subjectid);
	}
	
	private function repairArrParentid($subjectid,$parentid,$arrparentid){
		$subject_top = $this->where(array('subjectid'=>$subjectid,'parentid'=>$parentid))->getAll();
		foreach($subject_top as $k => $v ){
			unset($subject_top[$k]);
			$this->clearWhere()->where(array('treeid'=>$v['treeid']))->update(['arrparentid'=>$arrparentid]);
			$sub_tree_data = $this->clearWhere()->where(array('parentid'=>$v['treeid']))->getAll();
			foreach($sub_tree_data as $k2 => $v2 ){
				unset($sub_tree_data[$k2]);
				$this->repairArrParentid($subjectid,$v['treeid'],$v['arrparentid'].','.$v['treeid']);
			}
		}
	}
	
	private function repairArrChildid($subjectid){
		$this->where(array('subjectid'=>$subjectid))->update(['arrchildid='=>'treeid']);
		$subject_top = $this->where(array('subjectid'=>$subjectid))->getAll();
		
		foreach($subject_top as $k => $v ){
			unset($subject_top[$k]);
			$this->clearWhere()->where('treeid in ('.$v['arrparentid'].')')
				->update(['arrchildid='=>'CONCAT(arrchildid,",'.$v['treeid'].'")']);
		}
	}

}