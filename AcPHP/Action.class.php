<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月4日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月4日 下午11:09:52
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

class Action{
	static private $isinitial = false;
	public function __construct() {
		/*
		if(self::$isinitial===true){
			E('*Action类控制器不允许手动实例化，或你多次继承Action类');
		}
		self::$isinitial = true;
		*/
	}

	
	/**
	 * 获取模板解析对象
	 * @return Template
	 */
	protected function getTplObj(){
		return xTemplate::getInstance();
	}
	
	/**
	 * 注入变量
	 * @param unknown $name
	 * @param unknown $var
	 */
	protected function assign($name,$var){
		$this->getTplObj()->assign($name,$var);
	}
	/**
	 * 模板显示
	 * @param unknown $tpl
	 */
	protected function display($tpl='index.html',$cache='auto',$cacheTime=3600){
		$this->getTplObj()->display($tpl,$cache,$cacheTime);
		exit;
	}
	
	//分页
	protected function page($_total,$page_size=null,$page_both=null) {
		$_route = & App::getConfig('Route');
		$_config = & App::getConfig('Config');
		if(is_null($page_size)) $page_size = $_config['page_size'];
		if(is_null($page_both)) $page_both = $_config['page_both'];
		
		return new xPage($_total,$page_size,$page_both,$_route['url_fix'],$_route['url_suffix']);
	}
	
	//生成系统令牌
	protected function token() {
		$token = uniqid();
		xSession::set(App::getConfig('Config','token_key'),$token);
		return $token;
	}
	
	//验证令牌
	protected function verifyToken($token,$destroy=true) {
		$r = true;
		if(xSession::get(App::getConfig('Config','token_key'))!=$token){
			$r = false;
		}
		if($destroy) $this->destroyToken();
		return $r;
	}
	
	//销毁令牌
	protected function destroyToken() {
		xSession::_unset(App::getConfig('Config','token_key'));
		return true;
	}
	
	//信息提示页面
	protected function showMessage($msg='',$url='',$time=NULL){
		$this->assign('msg',$msg);
		$this->assign('url',$url);
		$time!==NULL OR $time = App::getConfig('Config','tips_time');
		$this->assign('time',$time);
	    $this->display('message.html');
		exit;
	}
	
	//操作错误快捷错误信息提示页面
	protected function error($url=''){
	    $this->showMessage('操作失败',$url,3);
	}
	
	//操作成功快捷错误信息提示页面
	protected function success($url=''){
	    $this->showMessage('操作成功',$url,3);
	}
	
	/*
     * 异步调用
     * get参数，status是否返回执行结果，host
     */
    protected function AsyExe($get){
		$host = 'ac.jiangzg.com';
        $fp = fsockopen($host, 80, $errno, $errstr, 30);
        $out = 'GET '.$get.' HTTP/1.1'.PHP_EOL;
        $out .= 'Host: '.$host.PHP_EOL;
        $out .= 'Connection: Close'.PHP_EOL.PHP_EOL;
        fwrite($fp, $out);
        fclose($fp);
    }
	
}







