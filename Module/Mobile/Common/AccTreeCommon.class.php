<?php
class AccTreeCommon {
	//创建以select方式显示的树形菜单
	static public function createTreeAccOption($parme1, $parme2){
		$parentid = xInput::request('parentid',0);
		$select = '';
		if($parme1 ['number']!='')
			$parme1 ['accelementsname'] .= '（'.$parme1 ['number'].'）';
			
		if ($parentid == $parme1 ['accelementsid'])$select = 'selected';
		$tree = '<option value="' . $parme1 ['accelementsid'] . '" ' . $select . '>' . $parme2 . $parme1 ['accelementsname'] . '</option>';
		return $tree;
	}
}









