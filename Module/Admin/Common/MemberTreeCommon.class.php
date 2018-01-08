<?php
class MemberTreeCommon {
	
	//执行父类构造方法
	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}		
	}
	
	
	//创建以select方式显示的树形菜单
	static public function createTreeMemberOption($parme1, $parme2){
		$parentid = xInput::request('parentid',0);
		$select = '';
		if ($parentid == $parme1 ['priv_id'])$select = 'selected';
		$tree = '<option value="' . $parme1 ['priv_id'] . '" ' . $select . '>' . $parme2 . $parme1 ['priv_name'] . '</option>';
		return $tree;
	}
	
	//创建以table方式显示的树形菜单
	static public function createTreeMemberTable($parme1, $parme2){
		
		$parme1 ['has_priv'] = isset($parme1 ['has_priv'])?$parme1 ['has_priv']:NULL;
		
		$temp = explode(',',$parme1 ['arrparentid']);
		$style = 'style="display:none"';
		if(count($temp)<=2) $style = 'style="display:table-row"';
		
		$tree = '<tr parentid="'.$parme1['parentid'].'" priv_id="'.$parme1['priv_id'].'" arrchildid="'.$parme1 ['arrchildid'].'">
			<td align="center"><input type="checkbox" name="group_priv[]" value="' . $parme1 ['priv_id'] . '" '.(($parme1 ['has_priv']>0)?'checked':'').'></td>
			<td align="center">' . $parme1 ['priv_id'] . '</td>
			<td class="ac-hidden"><input type="text" name="listorder[' . $parme1 ['priv_id'] . ']" value="' . $parme1 ['listorder'] . '" class="listorder"></td>
			<td>' . $parme2 . $parme1 ['priv_name'] . '</td>
			<td>' . $parme1 ['module'].'/'.$parme1 ['controller'].'/'.$parme1 ['action'].'/'.$parme1 ['data'] . '</td>
			<td align="center">' . cutStr($parme1 ['description']) . '</td>
			<td align="center" class="ac-hidden">
				<a href="' . U ( 'edit', array (
					'priv_id' => $parme1 ['priv_id'] 
			) ) . '">编辑</a> |
				<a href="' . U ( 'delete', array (
					'priv_id' => $parme1 ['priv_id'] 
			) ) . '" data-delete>删除</a>
			</td>
		</tr>';
		return $tree;
	}
	
	//创建以table方式显示的树形菜单
	static public function createTreeMemberTableSetting($parme1, $parme2){
		
		$parme1 ['has_priv'] = isset($parme1 ['has_priv'])?$parme1 ['has_priv']:NULL;
		
		$temp = explode(',',$parme1 ['arrparentid']);
		$style = 'style="display:none"';
		if(count($temp)<=2) $style = 'style="display:table-row"';
		
		$tree = '<tr parentid="'.$parme1['parentid'].'" priv_id="'.$parme1['priv_id'].'" arrchildid="'.$parme1 ['arrchildid'].'">
			<td align="center"><input type="checkbox" name="group_priv[]" value="' . $parme1 ['priv_id'] . '" '.(($parme1 ['has_priv']>0)?'checked':'').'></td>
			<td align="center">' . $parme1 ['priv_id'] . '</td>
			<td class="ac-hidden"><input type="text" name="listorder[' . $parme1 ['priv_id'] . ']" value="' . $parme1 ['listorder'] . '" class="listorder"></td>
			<td>' . $parme2 . $parme1 ['priv_name'] . '</td>
			<td>' . $parme1 ['module'].'/'.$parme1 ['controller'].'/'.$parme1 ['action'].'/'.$parme1 ['data'] . '</td>
			<td align="center">' . cutStr($parme1 ['description']) . '</td>
			<td align="center" class="ac-hidden">
				<a href="' . U ( 'edit', array (
					'priv_id' => $parme1 ['priv_id'] 
			) ) . '">编辑</a> |
				<a href="' . U ( 'delete', array (
					'priv_id' => $parme1 ['priv_id'] 
			) ) . '" data-delete>删除</a>
			</td>
		</tr>';
		return $tree;
	}
}









