<?php


class QuestionAction extends QuestionThisCommon  {
    
    
    public function accelements(){

		$this->tree_obj = xTree::getInstance ();
		$this->tree_obj->tree_id = 'accelementsid';

        $accelementsData = M('accelements')
            ->order('listorder desc,accelementsid desc')->getAll();
        
        $accelementsData = $this->tree_obj->createTree ( $accelementsData );
        xOut::json(outSuccess($accelementsData));
    }
}



















