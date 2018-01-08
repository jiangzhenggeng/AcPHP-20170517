<?php
abstract class DBAbstract{

	protected static $instance=null;
	
	public $clusterWhere = array();
	// +----------------------------------------------------------------------------------
	// + 数据库连接句柄
	// +----------------------------------------------------------------------------------
	private $curr_hander = '';
	// +----------------------------------------------------------------------------------
	// + 当前执行的SQL语句
	// +----------------------------------------------------------------------------------
	private $sql = '';
	// +----------------------------------------------------------------------------------
	// + 最近一次查询资源句柄
	// +----------------------------------------------------------------------------------
	private $lastqueryid = '';
	// +----------------------------------------------------------------------------------
	// + 数据库操作耗时
	// +----------------------------------------------------------------------------------
	private $query_time = NULL;
	// +----------------------------------------------------------------------------------
	// + 数据库配置
	// +----------------------------------------------------------------------------------
	private $database_config = array ();
	
	// +----------------------------------------------------------------------------------
	// + 打包查询条件
	// +----------------------------------------------------------------------------------
	protected function _packageWhere() {
		return $this->clusterWhere['join'] . ' '.$this->clusterWhere['where'] . ' ' . $this->clusterWhere['order'] . ' ' . $this->clusterWhere['limit'];
	}
	
	
	/**
	 * 更新数据库
	 * @param unknown $keyValu
	 * @return Ambigous <number, boolean, multitype:unknown, multitype:multitype: >
	 */
	public function update($keyValu) {
		// 判断更新键值对
		if (is_array ( $keyValu )) {
			$_keyValu = xTool::arrayToStrKeyUpdate ( $keyValu );
		}
		$this->sql = 'UPDATE '.$this->clusterWhere['table'] .' SET '.$_keyValu.' '.$this->clusterWhere['where'];
		return $this->query($this->sql);
	}
	
	/**
	 * 插入数据
	 * @param unknown $keyValu
	 * @param string $valu
	 * @return Ambigous <number, boolean, multitype:unknown, multitype:multitype: >
	 */
	public function insert($keyValu, $valu = '') {
		// 判断更新键值对
		$op = $valu !== true ? 'INSERT' : 'REPLACE';
		if (is_array ( $keyValu )) {
			//多条的插入方式
			if (is_array ( current($keyValu) )) {
				$_insert_data = '';
				foreach($keyValu as $k => $v ){
					$_keyValuAll = xTool::arrayToStrKeyInsert ( $v );
					$_insert_data .= ',('.$_keyValuAll[1].')';
				}
				$this->sql = $op . ' INTO '.$this->clusterWhere['table'].'('.$_keyValuAll[0].')VALUES'.substr($_insert_data,1).';';
			}
			//一条记录的插入方式
			else{
				$_keyValuAll = xTool::arrayToStrKeyInsert ( $keyValu );
				$this->sql = $op . ' INTO '.$this->clusterWhere['table'].'('.$_keyValuAll[0].')VALUE('.$_keyValuAll[1].');';
			}
		} else {
			$this->sql = $op . ' INTO '.$this->clusterWhere['table'].'('.$keyValu.')VALUE('.$valu.');';
		}
		return $this->query($this->sql);
	}
	
	
	public function replace($keyValu) {
		return $this->insert ( $keyValu, true );
	}
	
	/**
	 * 删除操作
	 * @return Ambigous <number, boolean, multitype:unknown, multitype:multitype: >
	 */
	public function delete() {
		if(trim($this->clusterWhere['where'])==''){
			trigger_error('删除数据必须设置条件！',E_USER_ERROR);
			exit;
		}
		$this->sql = 'DELETE FROM '.$this->clusterWhere['table'].' '.$this->clusterWhere['where'];
		return $this->query($this->sql);
	}
	
	/**
	 * 获取所有记录
	 * @return Ambigous <number, boolean, multitype:unknown, multitype:multitype: >
	 */
	public function getAll() {
		$this->sql = 'SELECT '.$this->clusterWhere['field'].' FROM '.$this->clusterWhere['table'].' '. $this->_packageWhere ();
		return $this->query($this->sql);
	}
	
	/**
	 * 获取条件的第一条记录
	 * @return Ambigous <multitype:>|multitype:
	 */
	public function getOne() {
		$this->clusterWhere['limit'] = 'LIMIT 0,1';
		$this->sql = 'SELECT '.$this->clusterWhere['field'].' FROM '.$this->clusterWhere['table'].' ' . $this->_packageWhere ();
		$sql_data = $this->query($this->sql);
		return isset($sql_data[0])?$sql_data[0]:array();
	}
	
	/**
	 * 获取总记录
	 * @return unknown number
	 */
	public function getTotal() {
		$field = '*';
		$asfield = 'count';
		
		$this->sql = 'SELECT COUNT('.$field.') AS ' . $asfield . ' FROM ' .$this->clusterWhere['table']. ' ' . $this->_packageWhere ();
		$sql_data = $this->query($this->sql);
		return isset($sql_data[0])?$sql_data[0][$asfield]:0;;
	}
	/**
	 * 获取总记录
	 * @return unknown number
	 */
	public function count() {
		return $this->getTotal();
	}
	/**
	 * 获取下一条记录的自增id
	 * @return number Ambigous
	 */
	public function getNextId() {
		$this->sql = $sql = 'SHOW TABLE STATUS LIKE "'.$this->clusterWhere['table'].'"';
		$tempR = $this->query($this->sql);
		return ($tempR [0] ['Auto_increment'] == 0) ? 1 : $tempR [0] ['Auto_increment'];
	}
	
	/**
	 * 获取当前执行的SQL语句
	 */
	public function getSql() {
		return $this->sql;
	}
	
	//获取表主键
	public function getKey() {
		$res = $this->query('DESC '.$this->clusterWhere['table'] );
		foreach($res as $k => $v ){
			if($v['Key']=='PRI'){
				return $v['Field'];
				break;
			}
		}
	}
	
	
}





















