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

class AccelementsModel extends xModel{
	public function __construct($database = NULL){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct($database);
		}
	}
	
	public function repair($parentid=0,$arrparentid=0){
		set_time_limit(0);
		$this->clearWhere()->update(['arrparentid'=>'0']);
		$this->repairArrParentid($parentid,$arrparentid);
		$this->repairArrChildid();
	}
	
	private function repairArrParentid($parentid,$arrparentid){
		$data_top = $this->where(array('parentid'=>$parentid))->getAll();
		foreach($data_top as $k => $v ){
			unset($data_top[$k]);
			$this->clearWhere()->where(array('accelementsid'=>$v['accelementsid']))->update(['arrparentid'=>$arrparentid]);
			$sub_tree_data = $this->clearWhere()->where(array('parentid'=>$v['accelementsid']))->getAll();
			foreach($sub_tree_data as $k2 => $v2 ){
				unset($sub_tree_data[$k2]);
				$this->repairArrParentid($v['accelementsid'],$v['arrparentid'].','.$v['accelementsid']);
			}
		}
	}
	
	private function repairArrChildid(){
		$this->update(['arrchildid='=>'accelementsid']);
		$data_top = $this->getAll();
		
		foreach($data_top as $k => $v ){
			unset($data_top[$k]);
			$this->clearWhere()->where('accelementsid in ('.$v['arrparentid'].')')
				->update(['arrchildid='=>'CONCAT(arrchildid,",'.$v['accelementsid'].'")']);
		}
	}
	
}