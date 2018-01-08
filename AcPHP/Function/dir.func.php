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
// + Release Date: 2015年11月8日 下午10:28:56
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');
/**
* 转化 \ 为 /
* 
* @param	string	$path	路径
* @return	string	路径
*/
function dir_path($path) {
	$path = str_replace('\\', '/', $path);
	if(substr($path, -1) != '/') $path = $path.'/';
	return $path;
}
/**
* 创建目录
* 
* @param	string	$path	路径
* @param	string	$mode	属性
* @return	string	如果已经存在则返回true，否则为flase
*/
function dir_create($path, $mode = 0777) {
	if(is_dir($path)) return TRUE;
	$ftp_enable = 0;
	$path = dir_path($path);
	$temp = explode('/', $path);
	$cur_dir = '';
	$max = count($temp) - 1;
	for($i=0; $i<$max; $i++) {
		$cur_dir .= $temp[$i].'/';
		if (@is_dir($cur_dir)) continue;
		@mkdir($cur_dir, 0777,true);
		@chmod($cur_dir, 0777);
	}
	return is_dir($path);
}
/**
* 拷贝目录及下面所有文件
* 
* @param	string	$fromdir	原路径
* @param	string	$todir		目标路径
* @return	string	如果目标路径不存在则返回false，否则为true
*/
function dir_copy($fromdir, $todir) {
	$fromdir = dir_path($fromdir);
	$todir = dir_path($todir);
	if (!is_dir($fromdir)) return FALSE;
	if (!is_dir($todir)) dir_create($todir);
	$list = glob($fromdir.'*');
	if (!empty($list)) {
		foreach($list as $v) {
			$path = $todir.basename($v);
			if(is_dir($v)) {
				dir_copy($v, $path);
			} else {
				copy($v, $path);
				@chmod($path, 0777);
			}
		}
	}
    return TRUE;
}
/**
* 转换目录下面的所有文件编码格式
* 
* @param	string	$in_charset		原字符集
* @param	string	$out_charset	目标字符集
* @param	string	$dir			目录地址
* @param	string	$fileexts		转换的文件格式
* @return	string	如果原字符集和目标字符集相同则返回false，否则为true
*/
function dir_iconv($in_charset, $out_charset, $dir, $fileexts = 'php|html|htm|shtml|shtm|js|txt|xml') {
	if($in_charset == $out_charset) return false;
	$list = dir_list($dir);
	foreach($list as $v) {
		if (pathinfo($v, PATHINFO_EXTENSION) == $fileexts && is_file($v)){
			file_put_contents($v, iconv($in_charset, $out_charset, file_get_contents($v)));
		}
	}
	return true;
}
/**
* 列出目录下所有文件
* 
* @param	string	$path		路径
* @param	string	$exts		扩展名
* @param	array	$list		增加的文件列表
* @return	array	所有满足条件的文件
*/
function dir_list($path, $exts = '', $list= array()) {
	$path = dir_path($path);
	$files = glob($path.'*');
	foreach($files as $v) {
		if (!$exts || pathinfo($v, PATHINFO_EXTENSION) == $exts) {
			$list[] = $v;
			if (is_dir($v)) {
				$list = dir_list($v, $exts, $list);
			}
		}
	}
	return $list;
}
/**
* 设置目录下面的所有文件的访问和修改时间
* 
* @param	string	$path		路径
* @param	int		$mtime		修改时间
* @param	int		$atime		访问时间
* @return	array	不是目录时返回false，否则返回 true
*/
function dir_touch($path, $mtime = TIME, $atime = TIME) {
	if (!is_dir($path)) return false;
	$path = dir_path($path);
	if (!is_dir($path)) touch($path, $mtime, $atime);
	$files = glob($path.'*');
	foreach($files as $v) {
		is_dir($v) ? dir_touch($v, $mtime, $atime) : touch($v, $mtime, $atime);
	}
	return true;
}
/**
* 目录列表
* 
* @param	string	$dir		路径
* @param	int		$parentid	父id
* @param	array	$dirs		传入的目录
* @return	array	返回目录列表
*/
function dir_tree($dir, $parentid = 0, $dirs = array()) {
	global $id;
	if ($parentid == 0) $id = 0;
	$list = glob($dir.'*');
	foreach($list as $v) {
		if (is_dir($v)) {
            $id++;
			$dirs[$id] = array('id'=>$id,'parentid'=>$parentid, 'name'=>basename($v), 'dir'=>$v.'/');
			$dirs = dir_tree($v.'/', $id, $dirs);
		}
	}
	return $dirs;
}

/**
* 删除目录及目录下面的所有文件
* 
* @param	string	$dir		路径
* @return	bool	如果成功则返回 TRUE，失败则返回 FALSE
*/
function dir_delete($dir) {
	$dir = dir_path($dir);
	if (!is_dir($dir)) return FALSE;
	$list = glob($dir.'*');
	foreach($list as $v) {
		is_dir($v) ? dir_delete($v) : @unlink($v);
	}
    return @rmdir($dir);
}


