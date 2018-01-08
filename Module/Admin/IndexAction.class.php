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
// + Release Date: 2015年11月8日 上午1:09:25
// +--------------------------------------------------------------------------------------

// +--------------------------------------------------------------------------------------
// + 系统入口模块
// +--------------------------------------------------------------------------------------
class IndexAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
	}
	//系统入口
	public function init(){
		
		//强制取消调试模式
		App::setConfig('Config','debug',false);
		
		if(xInput::request('query')=='isonline' && C_IS_AJAX){
			$where = array('admin_name'=>xSession::get('admin.admin_name'));
			$admin_user = M('admin')->where($where)->getOne();
			if( $admin_user['logsession']!=xSession::get('admin.logsession') ){
				xOut::json(['message'=>'你的账号在别处登录，你逼迫下线','status'=>'offline']);
				xSession::destroy();
				exit;
			}else{
				M('admin')->where($where)->update(['querytime'=>time()]);
				xOut::json(['message'=>'在线','status'=>'online']);
				exit;
			}
			xOut::json(['message'=>'系统错误','status'=>'error']);
			xSession::destroy();
			exit;
		}
		
		//如果是超级管理员也不需要检测权限
		if(intval(xSession::get('admin.group_id'))==1){
			$top_menu = M('admin_group_priv_data apd')
				->order('apd.listorder asc,apd.priv_id desc')
				->where(['apd.parentid'=>0])
				->getAll();
		}else{
			//获取顶级菜单
			$top_menu = M('admin_group_priv_data apd')
				->field('apd.*,ap.group_id')
				->leftJoin('admin_group_priv ap','ap.priv_id=apd.priv_id')
				->order('apd.listorder asc,apd.priv_id desc')
				->where(['apd.parentid'=>0,'ap.group_id'=>xSession::get('admin.group_id')])
				->getAll();
		}
		$this->assign('top_menu',$top_menu);
		$this->display('index.html');
	}
	
	//系统主页
	public function main(){
		$xModel = new xModel();
		$mysql_v = $xModel->query('select VERSION()');
	    defined('MYSQL_VERSION') OR define('MYSQL_VERSION',$mysql_v[0]['VERSION()']);
		$this->display('main.html');
	}
}

