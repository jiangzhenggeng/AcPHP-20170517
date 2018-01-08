<?php

class AccelementsAction extends ApiCommon {

	public function getAccelements(){
		$tree_obj = xTree::getInstance ();
		$tree_obj->tree_id = 'accelementsid';
		$_GET['parentid'] = intval(xInput::request('b'));
		
		$accelements_tree = M('accelements')->getAll();
		$create_obj = $tree_obj->createTree ( $accelements_tree );
		$accelements_tree = $tree_obj->createTreeMenu ($create_obj,'AccTreeCommon::createTreeAccOption');
		$accelements_tree = '<option value="0">分录元素</option>'.$accelements_tree;
		xOut::json(array('status'=>'success','data'=>$accelements_tree));
	}
	
}