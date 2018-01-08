<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月9日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月9日 上午12:59:44
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

class SessionMemcache extends SessionAbstract {
	
	protected $lifeTime     = 3600;
	protected $sessionName  = '';
	protected $handle       = null;
	
    /**
     * 打开Session 
     * @access public 
     * @param string $savePath 
     * @param mixed $sessName  
     */
	public function open($savePath, $sessName) {
		if( !class_exists(Memcache) ){
			exit ( 'Class Memcache Not Defined.Class is Memcache.' );
		}
		$this->handle = new Memcache;
		$server = & App::getConfig('Session', 'server');
		if(!is_array($server)){
			$server = array($server);
		}
		foreach($server as $v ){
			$v = explode(',', $v);
			$this->handle->addserver (isset($v[0])?$v[0]:'127.0.0.1',isset($v[1])?$v[1]:'11211',true, 1, isset($v[2])?$v[2]:ini_get('life_time') );
		}
		return true;
	}

    /**
     * 关闭Session 
     * @access public 
     */
	public function close() {
		$this->gc(ini_get('session.gc_maxlifetime'));
		$this->handle->close();
		$this->handle = null;
		return true;
	}

    /**
     * 读取Session 
     * @access public 
     * @param string $sessID 
     */
	public function read($sessID) {
        return $this->handle->get($this->sessionName.$sessID);
	}

    /**
     * 写入Session 
     * @access public 
     * @param string $sessID 
     * @param String $sessData  
     */
	public function write($sessID, $sessData) {
		return $this->handle->set($this->sessionName.$sessID, $sessData, 0, $this->lifeTime);
	}

    /**
     * 删除Session 
     * @access public 
     * @param string $sessID 
     */
	public function destroy($sessID) {
		return $this->handle->delete($this->sessionName.$sessID);
	}

    /**
     * Session 垃圾回收
     * @access public 
     * @param string $sessMaxLifeTime 
     */
	public function gc($sessMaxLifeTime) {
		return true;
	}
}
