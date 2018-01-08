<?php
/**
 * Created by PhpStorm.
 * User: jiangzg
 * Date: 16/7/1
 * Time: 13:42
 */

class TestAction extends Action{

    public function init(){
        $this->display('index.html');
    }
}