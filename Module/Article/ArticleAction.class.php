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
// + 文章管理模块
// +--------------------------------------------------------------------------------------
class ArticleAction extends ArticleCommon
{

    public function __construct()
    {
        if (get_parent_class() != '' && method_exists(get_parent_class(), '__construct')) {
            parent::__construct();
        }
    }

    /**
     * 文章列表
    */
    public function lists(){

        $m = M('content c');
        $m->where(array('c.content_status'=>1));
        $count = $m->count();
        $page = $this->page($count);
        $m->limit($page->getLimit());
        $content = $m->field('c.*,a.admin_name')->leftJoin('admin a','a.admin_id=c.uid')
            ->order('c.listorder desc,c.aid desc')->getAll();
        $this->assign('content', $content);
        $this->assign('page', $page->show());
        $this->display('article/article_list.html');
    }

    /**
     * 添加文章
    */
    public function add(){
        if ( xInput::request('query')!='insert' ){
            //生成系统令牌
            $this->assign('token', $this->token() );
            //获取管理员组
            $major = M('major')->getAll();
            $this->assign('major', $major );
            $this->display('article/article_add.html');
        }else{
            //校验系统令牌
            $this->verifyToken(xInput::request('token')) or $this->showMessage('校验系统令牌失败');
            $subject = xInput::request('subject');
            $subjectid = M('subject')->getNextId();
            $result = M('subject')->insert($subject);
            if($result){
                $subject_tree = array(
                    'subjectid'=>$subjectid,
                    'parentid'=>0,
                    'arrparentid'=>0,
                    'arrchildid'=>M('subject_tree')->getNextId(),
                    'chaptername'=>$subject['subjectname'],
                    'keywords'=>'',
                    'description'=>''
                );
                $treeid = M('subject_tree')->insert($subject_tree);
                if($treeid) $this->showMessage('添加成功',U('lists'));
            }
            $this->error();
        }
    }



}