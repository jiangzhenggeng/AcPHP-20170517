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
// + Release Date: 2015年11月15日 下午11:11:43
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

// +--------------------------------------------------------------------------------------
// + 分页类
// +--------------------------------------------------------------------------------------

class xPage {
	private $total; // 总记录
	private $pagesize; // 每页显示多少条
	private $page; // 当前页码
	private $pagenum; // 总页码
	private $url; // 地址
	private $bothnum; // 两边保持数字分页的量
	private $last; // 网址？后面部分 尾巴部分
	private $url_fix = '/';
	private $url_suffix = '.html';
	
	public $limit; // limit
	                
	// 构造方法初始化
	public function __construct($_total, $_pagesize, $both_page_num = 3, $url_fix='/',$url_suffix='.html') {
		$this->total = $_total ? $_total : 0;
		$this->pagesize = $_pagesize ? $_pagesize : 1;
		$this->pagenum = ceil ( $this->total / $this->pagesize );
		$this->bothnum = $both_page_num;
		$this->url_fix = $url_fix;
		$this->url_suffix = $url_suffix;
		
		$this->page = $this->setPage ();
		$temp = ($this->page - 1) * $this->pagesize;
		$this->limit = ($temp<=0?0:$temp) . ",$this->pagesize";
		$this->url = $this->setUrl ();
	}
	
	public function getLimit(){
		return $this->limit;
	}
	
	// 分页信息
	public function show() {
		if ($this->total > 0) {
			$_page = ' <a class="a1">共' . $this->pagenum . '页/' . $this->total . '条</a> ';
			$_page .= $this->first ();
			$_page .= $this->pageList ();
			$_page .= $this->last ();
			$_page .= $this->prev ();
			$_page .= $this->next ();
			$_page .= ' <a class="a1">每页显示' . $this->pagesize . '条</a> ';
		} else {
			$_page = ' <span class="disabled">没有数据...</span> ';
		}
		return $_page;
	}
	// 获取当前页码
	private function setPage() {
		if (! empty ( $_GET ['page'] )) {
			if ($_GET ['page'] > 0) {
				return $_GET ['page'];
			} else {
				return 1;
			}
		} else {
			return 1;
		}
	}
	
	// 获取地址
	private function setUrl() {
		$_url_temp = explode ( '?', $_SERVER ["REQUEST_URI"] );
		$this->last = count ( $_url_temp ) > 1 ? '?' . $_url_temp [1] : '';
		$_url_temp [0] = preg_replace ( array('/\\' . $this->url_fix . '{0,1}page\\' . $this->url_fix . '[\d]{0,}/','/\/{2,}/'), array('',$this->url_fix), $_url_temp [0] );
		$_url_temp [0] = preg_replace ( '/(' . $this->url_suffix . '){1,}$/', '', $_url_temp [0] );
		$_url = $_url_temp [0];
		return $_url;
	}
	// 数字目录
	private function pageList() {
		$_pagelist = '';
		$k = 0; // 记录循环次数
		for($i = $this->bothnum; $i >= 1; $i --) {
			$_page = $this->page - $i;
			if ($_page < 1)
				continue;
			$k ++;
			$_pagelist .= ' <a href="' . $this->url . $this->url_fix . 'page' . $this->url_fix . $_page . $this->url_suffix . $this->last . '">' . $_page . '</a> ';
		}
		$_pagelist .= ' <span class="current">' . $this->page . '</span> ';
		for($i = 1; $i <= $this->bothnum; $i ++) {
			$_page = $this->page + $i;
			if ($_page > $this->pagenum)
				break;
			$_pagelist .= ' <a href="' . $this->url . $this->url_fix . 'page' . $this->url_fix . $_page . $this->url_suffix . $this->last . '">' . $_page . '</a> ';
		}
		return $_pagelist;
	}
	
	// 首页
	private function first() {
		if ($this->page > $this->bothnum + 1) {
			return ' <a href="' . $this->url . $this->url_suffix . $this->last . '">1</a> ... ';
		}
	}
	
	// 上一页
	private function prev() {
		if ($this->page == 1) {
			return ' <span class="disabled pn">上一页</span> ';
		}
		return ' <a class="a1" href="' . $this->url . $this->url_fix . 'page' . $this->url_fix . ($this->page - 1) . $this->url_suffix . $this->last . '">上一页</a> ';
	}
	
	// 下一页
	private function next() {
		if ($this->page == $this->pagenum) {
			return ' <span class="a1 disabled pn">下一页</span> ';
		}
		return ' <a class="a1" href="' . $this->url . $this->url_fix . 'page' . $this->url_fix . ($this->page + 1) . $this->url_suffix . $this->last . '">下一页</a> ';
	}
	
	// 尾页
	private function last() {
		if ($this->pagenum - $this->page > $this->bothnum) {
			return ' ...　<a href="' . $this->url . $this->url_fix . 'page' . $this->url_fix . $this->pagenum . $this->url_suffix . $this->last . '">' . $this->pagenum . '</a> ';
		}
	}
}



