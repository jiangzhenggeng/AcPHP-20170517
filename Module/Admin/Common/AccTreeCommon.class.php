<?php
class AccTreeCommon {
	
	//执行父类构造方法
	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}		
	}
	
	
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
	
	//创建以table方式显示的树形菜单
	static public function createTreeAccTable($parme1, $parme2){
		$temp = explode(',',$parme1 ['arrparentid']);
		$style = 'style="display:none"';
		if(count($temp)<=2) $style = 'style="display:table-row"';
		
		$tree = '<tr parentid="'.$parme1['parentid'].'" accelementsid="'.$parme1['accelementsid'].'" arrchildid="'.$parme1 ['arrchildid'].'" '.$style.'>
			<td align="center"><input type="checkbox" name="accelements[]" value="' . $parme1 ['accelementsid'] . '"></td>
			<td align="center">' . $parme1 ['accelementsid'] . '</td>
			<td><input type="text" name="listorder[' . $parme1 ['accelementsid'] . ']" value="' . $parme1 ['listorder'] . '" class="listorder"></td>
			<td>' . $parme2 . $parme1 ['accelementsname'] . '</td>
			<td align="center">' . $parme1 ['number']. '</td>
			<td title="'.$parme1 ['description'].'">' . cutStr($parme1 ['description']) . '</td>
			<td align="center">
				<a href="'.U('add',array('accelementsid' => $parme1 ['accelementsid'])).'">添加子类</a> |
				<a href="'.U('edit',array('accelementsid' => $parme1 ['accelementsid'])).'">编辑</a> |
				<a href="'.U('delete', array('accelementsid'=>$parme1 ['accelementsid'])).'" data-delete>删除</a>
			</td>
		</tr>';
		return $tree;
	}
}









