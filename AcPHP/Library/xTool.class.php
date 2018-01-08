<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月8日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月8日 下午12:17:41
// +--------------------------------------------------------------------------------------

class xTool{
	
	/**
	 * 组装sql条件语句
	 * @param unknown $where
	 * @param unknown $condition
	 * @return string
	 */
	static function toolWhere($where,$condition=''){
		$WHERE = array('AND','OR');
		
		$condition = strtoupper(trim($condition));
			
		$temp = '';
		$where = is_string($where)?trim($where):$where;
		if( $where=='' ){
			$temp = '';
		}elseif (is_array($where)){
			foreach ( $where as $key => $val ) {
				$operate = array('<','>','=','<>','!=','LIKE','IN','NOT IN','BETWEEN');
				$_operate = '=';
				
				if(is_array($val) && isset($val[1]) && in_array(strtoupper($val[1]), $operate)){
					$_operate = strtoupper($val[1]);
					$_val = $val[0];
				}elseif( is_array($val) && isset($val[0]) ){
					$_val = $val[0];
				}else {
					$_val = $val;
				}
				
				$_condition = $condition;
				$_where = '';
				if( isset($val[2]) && in_array(strtoupper($val[2]),$WHERE) ){
					$_condition = strtoupper($val[2]);
				}
				
				$like = strtoupper( (isset($val[1])?$val[1]:'') );
				if( in_array($like, array('LIKE','%LIKE','LIKE%','%LIKE%')) ){
					switch ( $like ){
						case '%LIKE':
							$_where = $key.' LIKE "%' . $_val . '"';
							break;
						case 'LIKE%':
							$_where = $key.' LIKE "' . $_val . '%"';
							break;
						case '%LIKE%':
							$_where = $key.' LIKE "%' . $_val . '%"';
							break;
						default:
							$_where = $key.' LIKE "%' . $_val . '%"';
							break;
					}
				}elseif ($like=='IN' || $like=='NOT IN') {
                    $_where = $key.' '.$_operate.'('.(is_array($_val)?implode(',', $_val):$_val).')';
				}elseif ($like=='BETWEEN') {
					$_where = $key.' '.$_operate.' '.(isset($_val[0])?intval($_val[0]):0).' AND '.(isset($_val[1])?intval($_val[1]):0);
				}else {
					$_where = $key.' '.$_operate.' "'.$_val.'"';
				}
				
				$_condition = $_condition==''?'AND':$_condition;
				if($temp==''){
					$temp .= $condition == ''?$_where:($_condition!=''?$_condition:$condition).' '.$_where;
				}else{
					$temp .=  ' ' . $_condition .' '. $_where;
				}
			}
		} else {
			$temp = $condition == ''?$where:$condition.' '.$where;
		}
		return $temp;
	}
	
	/**
	 * 获取完整的表名
	 * @param unknown $table
	 * @return string
	 */
	static function tableFix($table,$table_fix = '') {
		if($table_fix===''){
			$table_fix = App::getConfig('Database','table_prefix');
		}
		$table = explode ( ',', $table );
		$table_temp = '';
		foreach ( $table as $key => $val ) {
			$val = trim ( $val );
			if (substr ( $val, 0, strlen ( $table_fix ) ) != $table_fix) {
				$table_temp .= ',' . $table_fix . $val;
			} else {
				$table_temp .= ',' . $val;
			}
		}
		return substr ( $table_temp, 1 );
	}
	
	/**
	 * 将键值对数组转换为键值对字符串,专用于更新数据
	 * @param unknown $keyValu
	 * @return multitype:|string
	 */
	static function arrayToStrKeyUpdate($keyValu) {
		$math_query = array('-','+','/','%','*');
		if (is_string($keyValu)){
			return $keyValu;
		}elseif (is_array($keyValu)){
			$temp = '';
			$i = 0;
			$_count = count($keyValu);
			foreach ( $keyValu as $key => $val ) {
				$key = '`'.str_replace([' ','`'],'',$key).'`';
				$key = str_replace('.','`.`',$key);
				
				$fix = '';
				if ($i < $_count - 1){
					$fix = ',';
				}
				if(in_array(substr($key,-2,1),$math_query)){
					$temp = $temp . substr($key,0,-2).'`' . '='.substr($key,0,strlen($key)-2).'`'.substr($key,-2,1).'('.$val.')'.$fix;
				}elseif(substr($key,-2,1)=='='){
					$temp = $temp . substr($key,0,-2).'`' . '='.'('.$val.')'.$fix;
				}else{
					$temp = $temp . $key . '="'.$val.'"'.$fix;
				}
				$i ++;
			}
			return $temp;
		}else {
			return $keyValu;
		}
	}

	
	/**
	 * 将键值对数组转换为键值对字符串,专门用于插入数据
	 * @param unknown $keyValu
	 * @return multitype:|string
	 */
	static function arrayToStrKeyInsert($keyValu) {
		if (is_string($keyValu)){
			return $keyValu;
		}elseif (is_array($keyValu)){
			$temp1 = $temp2 = '';
			$i = 0;
			$_count = count($keyValu);
			foreach ( $keyValu as $key => $val ) {
				$key = '`'.str_replace([' ','`'],'',$key).'`';
				$key = str_replace('.','`.`',$key);
				
				if ($i < $_count - 1) {
					$temp1 = $temp1 . $key . ',';
					$temp2 = $temp2 . '"' . $val . '"' . ',';
				} else {
					$temp1 = $temp1 . $key;
					$temp2 = $temp2 . '"' . $val . '"';
				}
				$i ++;
			}
			return array($temp1,$temp2);
		}
	}
}