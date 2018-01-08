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
// + 管理员组管理模块
// +--------------------------------------------------------------------------------------
class UplaodAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		//关闭调试信息
		App::setConfig ( 'Config', 'debug',false);
		//关闭内存显示信息
		$_GET['memory'] = 'hide';
	}
	
	//管理员列表
	public function init(){
		switch (strtolower(xInput::request('action'))) {
			case 'config': $this->config(); break;
			/* 上传图片 */
			case 'uploadimage':
			/* 上传涂鸦 */
			case 'uploadscrawl':
			/* 上传视频 */
			case 'uploadvideo':
			/* 上传文件 */
			case 'uploadfile':$this->uploadfile(); break;
			/* 列出图片 */
			case 'listimage':
			/* 列出文件 */
			case 'listfile': $this->listfile(); break;
			/* 抓取远程文件 */
			case 'catchimage': $this->catchimage(); break;
			
			default: xOut::json(array( 'state'=> '请求地址出错' ));
		}
	}
	
	private function config(){
		xOut::json(UeditorConfig::getConfig());
	}
	
	private function uploadfile(){
		$action = htmlspecialchars(strtolower(xInput::request('action')));
		$CONFIG = UeditorConfig::getConfig();
		$base64 = "upload";
		switch ($action) {
			case 'uploadimage':
				$config = array(
					"pathFormat" => $CONFIG['imagePathFormat'],
					"maxSize" => $CONFIG['imageMaxSize'],
					"allowFiles" => $CONFIG['imageAllowFiles']
				);
				$fieldName = $CONFIG['imageFieldName'];
				break;
			case 'uploadscrawl':
				$config = array(
					"pathFormat" => $CONFIG['scrawlPathFormat'],
					"maxSize" => $CONFIG['scrawlMaxSize'],
					"allowFiles" => $CONFIG['scrawlAllowFiles'],
					"oriName" => "scrawl.png"
				);
				$fieldName = $CONFIG['scrawlFieldName'];
				$base64 = "base64";
				break;
			case 'uploadvideo':
				$config = array(
					"pathFormat" => $CONFIG['videoPathFormat'],
					"maxSize" => $CONFIG['videoMaxSize'],
					"allowFiles" => $CONFIG['videoAllowFiles']
				);
				$fieldName = $CONFIG['videoFieldName'];
				break;
			case 'uploadfile':
			default:
				$config = array(
					"pathFormat" => $CONFIG['filePathFormat'],
					"maxSize" => $CONFIG['fileMaxSize'],
					"allowFiles" => $CONFIG['fileAllowFiles']
				);
				$fieldName = $CONFIG['fileFieldName'];
				break;
		}
		$up = new Uploader($fieldName, $config, $base64);
		xOut::json($up->getFileInfo());
	}
	
	private function listfile(){
		$action = htmlspecialchars(strtolower(xInput::request('action')));
		$CONFIG = UeditorConfig::getConfig();
		/* 判断类型 */
		switch ($action) {
			/* 列出文件 */
			case 'listfile':
				$allowFiles = $CONFIG['fileManagerAllowFiles'];
				$listSize = $CONFIG['fileManagerListSize'];
				$path = $CONFIG['fileManagerListPath'];
				break;
			/* 列出图片 */
			case 'listimage':
			default:
				$allowFiles = $CONFIG['imageManagerAllowFiles'];
				$listSize = $CONFIG['imageManagerListSize'];
				$path = $CONFIG['imageManagerListPath'];
		}
		$allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
		/* 获取参数 */
		$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
		$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
		$end = $start + $size;
		/* 获取文件列表 */
		$files = $this->getfiles($path, $allowFiles);
		if (!count($files)) {
			$out = array(
				"state" => "no match file",
				"list" => array(),
				"start" => $start,
				"total" => count($files)
			);
			xOut::json($out);
		}
		
		/* 获取指定范围的列表 */
		$len = count($files);
		for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
			$list[] = $files[$i];
		}
		//倒序
		//for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
		//    $list[] = $files[$i];
		//}
		
		$out = array(
			"state" => "SUCCESS",
			"list" => $list,
			"start" => $start,
			"total" => count($files)
		);
		xOut::json($out);
	}
	
	private function catchimage(){
		set_time_limit(0);
		$CONFIG = UeditorConfig::getConfig();
		/* 上传配置 */
		$config = array(
			"pathFormat" => $CONFIG['catcherPathFormat'],
			"maxSize" => $CONFIG['catcherMaxSize'],
			"allowFiles" => $CONFIG['catcherAllowFiles'],
			"oriName" => "remote.png"
		);
		$fieldName = $CONFIG['catcherFieldName'];
		
		/* 抓取远程图片 */
		$list = array();
		if (isset($_POST[$fieldName])) {
			$source = $_POST[$fieldName];
		} else {
			$source = $_GET[$fieldName];
		}
		foreach ($source as $imgUrl) {
			$item = new Uploader($imgUrl, $config, "remote");
			$info = $item->getFileInfo();
			array_push($list, array(
				"state" => $info["state"],
				"url" => $info["url"],
				"size" => $info["size"],
				"title" => htmlspecialchars($info["title"]),
				"original" => htmlspecialchars($info["original"]),
				"source" => htmlspecialchars($imgUrl)
			));
		}
		
		/* 返回抓取数据 */
		$out = array(
			'state'=> count($list) ? 'SUCCESS':'ERROR',
			'list'=> $list
		);
		xOut::json($out);
	}
	
	
	/**
	 * 遍历获取目录下的指定类型的文件
	 * @param $path
	 * @param array $files
	 * @return array
	 */
	private function getfiles($path, $allowFiles, &$files = array()){
		if (!is_dir($path)) return null;
		if(substr($path, strlen($path) - 1) != '/') $path .= '/';
		$handle = opendir($path);
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				$path2 = $path . $file;
				if (is_dir($path2)) {
					$this->getfiles($path2, $allowFiles, $files);
				} else {
					if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
						$files[] = array(
							'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
							'mtime'=> filemtime($path2)
						);
					}
				}
			}
		}
		return $files;
	}
	
}

