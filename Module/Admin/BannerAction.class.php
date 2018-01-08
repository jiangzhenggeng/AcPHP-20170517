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
// + Banner模块管理
// +--------------------------------------------------------------------------------------
class BannerAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
	}

	public function lists(){
		$model = M('banner');
        $model->where(['`delete`'=>0]);
        $count = $model->count(false);
        $page = $this->page($count);
        $model->limit($page->getLimit());
        $banner = $model->getAll();
        $this->assign('page', $page->show());
        $this->assign('banner', $banner);
		$this->display('banner/banner_list.html');
	}

    public function add(){
        if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
            $this->assign('token', $this->token() );
            $this->display('banner/banner_add.html');
        }else{
            $banner = xInput::request('banner');

            if(!$this->verifyToken(xInput::request('token'))){
                $this->error('令牌校验错误');
            }

            $banner['starttime'] = trim($banner['starttime']);
            if( $banner['starttime'] ){
                $banner['starttime'] = strtotime($banner['starttime']);
            }else{
                $banner['starttime'] = 0;
            }
            $banner['endtime'] = trim($banner['endtime']);
            if( $banner['endtime'] ){
                $banner['endtime'] = strtotime($banner['endtime']);
            }else{
                $banner['endtime'] = 0;
            }

            $banner['addtime'] = time();
            $result = M('banner')->insert($banner);
            if($result){
                $this->success();
            }
            $this->error();
        }
    }

    public function edit(){

        $bannerid = intval(xInput::request('bannerid'));
        $bannerid >0 OR $this->showMessage('非法操作');

        if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
            $banner = M('banner')->where('bannerid='.$bannerid)->getOne();

            if( $banner['starttime']>0 ){
                $banner['starttime'] = date('Y-m-d H:i:s',$banner['starttime']);
            }else{
                $banner['starttime'] = 0;
            }
            if( $banner['endtime'] ){
                $banner['endtime'] = date('Y-m-d H:i:s',$banner['endtime']);
            }else{
                $banner['endtime'] = 0;
            }

            $this->assign('banner', $banner);
            $this->assign('bannerid', $bannerid);
            $this->display('banner/banner_edit.html');
        }else{
            $banner = xInput::request('banner');

            $banner['starttime'] = trim($banner['starttime']);
            if( $banner['starttime'] ){
                $banner['starttime'] = strtotime($banner['starttime']);
            }else{
                $banner['starttime'] = 0;
            }
            $banner['endtime'] = trim($banner['endtime']);
            if( $banner['endtime'] ){
                $banner['endtime'] = strtotime($banner['endtime']);
            }else{
                $banner['endtime'] = 0;
            }

            $model = M('banner');
            $result = $model->where('bannerid='.$bannerid)->update($banner);

            if($result){
                $this->success();
            }
            $this->error();
        }
    }

    public function delete(){

        $bannerid = intval(xInput::request('bannerid'));
        $bannerid >0 OR $this->showMessage('非法操作');
        $result = M('banner')->where('bannerid='.$bannerid)->update([
            '`delete`'=>1
        ]);;
        if($result){
            $this->success();
        }
        $this->error();
    }


    public function listorder($model=NULL){
        if(parent::listorder(M('banner'))){
            $this->success();
        }
        $this->error();
    }

}

