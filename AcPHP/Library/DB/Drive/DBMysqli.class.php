<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月15日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月15日 下午8:37:15
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

final class DBMysqli extends DBAbstract {

    protected $database_config = array();
    protected $curr_hander = null;
    protected $sql = '';
    protected $lastqueryid = null;

	public function connect($database_config) {
		$this->database_config = $_config = $database_config;
		$this->curr_hander = new mysqli($_config ['db_host'], $_config ['db_user'], $_config ['db_password'],$_config ['db_select']);		
		if($this->curr_hander->connect_error) {
			$this->curr_hander = false;
			die ( '<h2>数据库连接错误:'.$this->curr_hander->connect_error.'</h2>' );
		}
		$this->curr_hander->set_charset(str_replace ( '-', '', $_config ['charset'] ));
		return $this->curr_hander;
	}
	
	// +----------------------------------------------------------------------------------
	// + 释放查询资源
	// +----------------------------------------------------------------------------------
	private function _freeRsult() {
		if ( $this->lastqueryid ) {
			mysqli_free_result ( $this->lastqueryid );
			$this->lastqueryid = null;
		}
	}
	
	// +----------------------------------------------------------------------------------
	// + 执行SQL语句
	// +----------------------------------------------------------------------------------
	public function query($sql = '', $link_type = MYSQLI_ASSOC) {
		$sql = trim ( $sql );
		$this->sql = $sql != '' ? $sql : $this->sql;
		if($this->sql == ''){
			E('没有sql语句！',E_USER_ERROR);
		}
		$this->lastqueryid = $this->curr_hander->query ( $this->sql );
		if ($this->lastqueryid===false) {
			trigger_error(mysqli_errno($this->curr_hander ).mysqli_error($this->curr_hander ).',SQL:'.$this->sql,E_USER_ERROR);
			exit;
		}
		$fix = strtoupper ( substr ( $this->sql, 0, 6 ) );
		if (in_array ( $fix, array ('UPDATE','DELETE','REPLAC' ) )) {
			return $this->getRows ();
		}if (in_array ( $fix, array ('INSERT') )) {
			if($this->insertId())
				return $this->insertId();
			else
				return $this->getRows ();
		}
		$result = array ();
		while ( ! ! $row = mysqli_fetch_array ( $this->lastqueryid, $link_type ) ) {
			$result [] = $row;
		}
		$this->_freeRsult ();
		return $result;
	}
	
	/**
	 * 获取最后一次添加记录的主键号
	 * @return int
	 */
	public function insertId() {
		return mysqli_insert_id ( $this->curr_hander );
	}
	
	/**
	 * 获取执行SQL语句影响的行数
	 * @return number
	 */
	public function getRows() {
		if ( $this->curr_hander ) {
			return mysqli_affected_rows ( $this->curr_hander );
		}
		return 0;
	}
	
	/**
	 * 获取执行SQL语句影响的行数
	 * @return number
	 */
	public function close() {
		if ( $this->curr_hander ) {
			return mysqli_close ( $this->curr_hander );
		}
		return true;
	}
	
}









