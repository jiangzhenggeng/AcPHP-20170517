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
// + Author: AcPHP http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月9日 上午12:59:28
// +--------------------------------------------------------------------------------------
defined ( 'C_CA' ) or exit ( 'Server error does not pass validation test.' );

/**
 * 数据库方式Session驱动
 * CREATE TABLE session (
 * session_id varchar(255) NOT NULL,
 * session_expire int(11) NOT NULL,
 * session_data blob,
 * UNIQUE KEY `session_id` (`session_id`)
 * );
 */
class SessionDb extends SessionAbstract {
	
	/**
	 * Session有效时间
	 */
	protected $lifeTime = '180';
	
	/**
	 * session保存的数据库名
	 */
	protected $sessionTable = '';
	
	/**
	 * 数据库句柄
	 */
	protected $hander = array ();
	
	/**
	 * 打开Session
	 *
	 * @access public
	 * @param string $savePath        	
	 * @param mixed $sessName        	
	 */
	public function open($savePath, $sessName) {
		$this->hander = new xModel();
		$this->sessionTable = xTool::tableFix('session');
		$this->lifeTime = $this->lifeTime*60;
	}
	
	/**
	 * 关闭Session
	 *
	 * @access public
	 */
	public function close() {
		$this->gc ( $this->lifeTime );
		if($this->hander){
			return mysql_close ( $this->hander );
			$this->hander = NULL;
		}
		return false;
	}
	
	/**
	 * 读取Session
	 *
	 * @access public
	 * @param string $sessID        	
	 */
	public function read($sessID) {
		$res = $this->hander->query ( "SELECT session_data AS data FROM " . $this->sessionTable . " WHERE session_id = '$sessID'   AND session_expire >" . time () );
		if (count($res)>0) {
			return $res [0]['data'];
		}
		return "";
	}
	
	/**
	 * 写入Session
	 *
	 * @access public
	 * @param string $sessID        	
	 * @param String $sessData        	
	 */
	public function write($sessID, $sessData) {
		$expire = time () + $this->lifeTime;
		$res = $this->hander->query ( "REPLACE INTO  " . $this->sessionTable . " (  session_id, session_expire, session_data)  VALUES( '$sessID', '$expire',  '$sessData')" );
		if ($res)return true;
		return false;
	}
	
	/**
	 * 删除Session
	 *
	 * @access public
	 * @param string $sessID        	
	 */
	public function destroy($sessID) {
		$res = $this->hander->query ( "DELETE FROM " . $this->sessionTable . " WHERE session_id = '$sessID'" );
		if ($res)
			return true;
		return false;
	}
	
	/**
	 * Session 垃圾回收
	 *
	 * @access public
	 * @param string $sessMaxLifeTime        	
	 */
	public function gc($sessMaxLifeTime) {
		$res = $this->hander->query ( "DELETE FROM " . $this->sessionTable . " WHERE session_expire < " . time () );
		return $res;
	}
}