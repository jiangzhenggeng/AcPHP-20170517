<?php
class TreeCommon {
	
	//执行父类构造方法
	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}		
	}
	
	
	//创建以select方式显示的树形菜单
	static public function createTreeMenuOption($parme1, $parme2){
		$parentid = xInput::request('parentid',0);
		$select = '';
		if ($parentid == $parme1 ['priv_id'])$select = 'selected';
		$tree = '<option value="' . $parme1 ['priv_id'] . '" ' . $select . '>' . $parme2 . $parme1 ['priv_name'] . '</option>';
		return $tree;
	}
	
	//创建以table方式显示的树形菜单
	static public function createTreeMenuTable($parme1, $parme2){
		
		$ismenu = '显示';
		if ($parme1 ['ismenu'] != 1) {
			$ismenu = '隐藏';
		}
		$type = '非自动';
		$istype = '';
		if ($parme1 ['type'] == 1) {
			$type = '<font color="green">自动</font>';
			$istype = 'disabled';
		}
		$parme1 ['has_priv'] = isset($parme1 ['has_priv'])?$parme1 ['has_priv']:NULL;
		
		$temp = explode(',',$parme1 ['arrparentid']);
		$style = 'style="display:none"';
		if(count($temp)<=2) $style = 'style="display:table-row"';
		
		$tree = '<tr parentid="'.$parme1['parentid'].'" priv_id="'.$parme1['priv_id'].'" arrchildid="'.$parme1 ['arrchildid'].'" '.$style.'>
			<td align="center"><input type="checkbox" name="group_priv[]" value="' . $parme1 ['priv_id'] . '" '.(($parme1 ['has_priv']>0)?'checked':'').' '.$istype.'></td>
			<td align="center">' . $parme1 ['priv_id'] . '</td>
			<td class="ac-hidden"><input type="text" name="listorder[' . $parme1 ['priv_id'] . ']" value="' . $parme1 ['listorder'] . '" class="listorder"></td>
			<td class="ac-hidden"><input type="text" name="group[' . $parme1 ['priv_id'] . ']" value="' . $parme1 ['group'] . '" class="listorder"></td>
			<td>' . $parme2 . $parme1 ['priv_name'] . '</td>
			<td align="center">' . $ismenu . '</td>
			<td align="center">' . $type . '</td>
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
	static public function createTreeChapterTable($parme1, $parme2){
		$temp = explode(',',$parme1 ['arrparentid']);
		$style = 'style="display:none"';
		if(count($temp)<=2) $style = 'style="display:table-row"';
		
		$tree = '<tr parentid="' . $parme1 ['parentid'] . '" treeid="' . $parme1 ['treeid'] . '" arrchildid="' . $parme1 ['arrchildid'] . '" '.$style.' >
			<td align="center"><input type="checkbox" name="subject_tree[]" value="' . $parme1 ['treeid'] . '"></td>
			<td align="center">' . $parme1 ['treeid'] . '</td>
			<td><input type="text" name="listorder[' . $parme1 ['treeid'] . ']" value="' . $parme1 ['listorder'] . '" class="listorder"></td>
			<td>' . $parme2 . cutStr($parme1 ['chaptername'],22) . '</td>
			<td align="center">' . $parme1 ['number'].'['.$parme1 ['subnumber'].']'. '</td>
			<td title="'.$parme1 ['keywords'].'">' . cutStr($parme1 ['keywords']) . '</td>
			<td title="'.$parme1 ['description'].'">' . cutStr($parme1 ['description']) . '</td>
			<td align="center">
				<a href="'.U('add',array('treeid' => $parme1 ['treeid'],'subjectid' => $parme1 ['subjectid'])).'">添加子类</a> |
				<a href="'.U('edit',array('treeid' => $parme1 ['treeid'],'subjectid' => $parme1 ['subjectid'])).'">编辑</a> |
				<a href="'.U('delete', array('treeid'=>$parme1 ['treeid'])).'" data-delete>删除</a>
			</td>
		</tr>';
		return $tree;
	}
	
	static public function createTreeChapterOption($parme1, $parme2){
		$parentid = xInput::request('parentid',0);
		$select = '';
		if ($parentid == $parme1 ['treeid'])$select = 'selected';
		$tree = '<option value="' . $parme1 ['treeid'] . '" ' . $select . '>' . $parme2 . $parme1 ['chaptername'] . '</option>';
		return $tree;
	}
}









