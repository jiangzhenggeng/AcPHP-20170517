<?php
//图像处理类
class xImageThumb {
	public $file;				//图片地址
	private $width;			//图片长度
	private $height;			//图片长度
	private $type;				//图片类型
	private $img;				//原图的资源句柄
	private $new;				//新图的资源句柄
	
	//构造方法，初始化
	public function __construct($_file) {
		$this->file = $_file;
		list($this->width, $this->height, $this->type) = getimagesize($this->file);
		$this->img = $this->getFromImg($this->file, $this->type);
	}

	//cke专用图像处理
	public function ckeImg($new_width = 0,$new_height = 0,$position=1,$target = 5,$alpha=50) {
		$alpha = ceil($alpha);
		list($_water_width,$_water_height,$_water_type) = getimagesize(MARK);
		$_water = $this->getFromImg(MARK,$_water_type);
		
		if (empty($new_width) && empty($new_height)) {
			$new_width = $this->width;
			$new_height = $this->height;
		}
		
		if (!is_numeric($new_width) || !is_numeric($new_height)) {
			$new_width = $this->width;
			$new_height = $this->height;
		}
		
		if ($this->width > $new_width) { //通过指定长度，获取等比例的高度
			$new_height = ($new_width / $this->width) * $this->height;
		} else {
			$new_width = $this->width;
			$new_height = $this->height;
		}
		
		if ($this->height > $new_height) { //通过指定高度，获取等比例长度
			$new_width = ($new_height / $this->height) * $this->width;
		} else {
			$new_width = $this->width;
			$new_height = $this->height;
		}
		
		if($position==1){
			$_water_x = $new_width - $_water_width - $target;
			$_water_y = $new_height - $_water_height - $target;
		}elseif($position==2){
			$_water_x = $new_width - $_water_width - $target;
			$_water_y = $_water_height + $target;
		}elseif($position==3){
			$_water_x = $target;
			$_water_y = $target;
		}elseif($position==4){
			$_water_x = $target;
			$_water_y = $new_height - $_water_height - $target;
		}elseif($position==5){
			$_water_x = ceil( ($new_width/2) - ($_water_width/2) );
			$_water_y = ceil( ($new_height/2) - ($_water_height/2) );
		}elseif($position==6){
			$_water_x = ceil( ($new_width/2) - ($_water_width/2) );
			$_water_y = ceil( $new_height - $_water_height - $target );
		}elseif($position==7){
			$_water_x = ceil( ($new_width) - ($_water_width) - $target );
			$_water_y = ceil( ($new_height/2) - ($_water_height/2) );
		}elseif($position==8){
			$_water_x = ceil( ($new_width/2) - ($_water_width/2) );
			$_water_y = ceil( $target );
		}elseif($position==9){
			$_water_x = ceil( $target );
			$_water_y = ceil( ($new_height/2) - ($_water_height/2) );
		}elseif($position==10){
			$_water_x = ceil( rand($target,$new_width - $_water_width - $target) );
			$_water_y = ceil( rand($target,$new_height - $_water_height - $target) );
		}
		
		$this->new = imagecreatetruecolor($new_width,$new_height);
		imagecopyresampled($this->new,$this->img,0,0,0,0,$new_width,$new_height,$this->width,$this->height);
		if ($new_width > $_water_width && $new_height > $_water_height) {
			//imagecopy($this->new,$_water,$_water_x,$_water_y,0,0,$_water_width,$_water_height);
			imagecopymerge($this->new,$_water,$_water_x,$_water_y,0,0,$_water_width,$_water_height,$alpha);
		}
		imagedestroy($_water);
	}

	//缩略图(固定长高容器，图像等比例，扩容填充，裁剪)[固定了大小，不失真，不变形]
	public function thumb($new_width = 0,$new_height = 0) {
		
		if (empty($new_width) && empty($new_height)) {
			$new_width = $this->width;
			$new_height = $this->height;
		}
		
		if (!is_numeric($new_width) || !is_numeric($new_height)) {
			$new_width = $this->width;
			$new_height = $this->height;
		}
		
		//创建一个容器
		$_n_w = $new_width;
		$_n_h = $new_height;
		
		//创建裁剪点
		$_cut_width = 0;
		$_cut_height = 0;
		
		if ($this->width < $this->height) {
			$new_width = ($new_height / $this->height) * $this->width;
		} else {
			$new_height = ($new_width / $this->width) * $this->height;
		}
	
		
		
		
		if ($new_width < $_n_w) { //如果新高度小于新容器高度
			$r = $_n_w / $new_width; //按长度求出等比例因子
			$new_width *= $r; //扩展填充后的长度
			$new_height *= $r; //扩展填充后的高度
			$_cut_height = ($new_height - $_n_h) / 2; //求出裁剪点的高度
		}
		
		if ($new_height < $_n_h) { //如果新高度小于容器高度
			$r = $_n_h / $new_height; //按高度求出等比例因子
			$new_width *= $r; //扩展填充后的长度
			$new_height *= $r; //扩展填充后的高度
			$_cut_width = ($new_width - $_n_w) / 2; //求出裁剪点的长度
		}
			
		
		$this->new = imagecreatetruecolor($_n_w,$_n_h);
		imagecopyresampled($this->new,$this->img,0,0,$_cut_width,$_cut_height,$new_width,$new_height,$this->width,$this->height);
	}
	
	//加载图片，各种类型，返回图片的资源句柄
	private function getFromImg($_file, $_type) {
		switch ($_type) {
			case 1 :
				$img = imagecreatefromgif($_file);
				break;
			case 2 :
				$img = imagecreatefromjpeg($_file);
				break;
			case 3 : 
				$img = imagecreatefrompng($_file);
				break;
			default:
				$img = imagecreatefromjpeg($_file);
		}
		return $img;
	}
	
	//图像输出
	public function out($path='') {
		if($path!=''){
			$temp = explode('/',$this->file);
			$dir = dirname($this->file).'/'.$path.'/';
			if( !is_dir($dir) )
				mkdir($dir);
			$this->file = dirname($this->file).'/'.$path.'/thumb_'.$temp[count($temp)-1];
		}
		imagepng($this->new,$this->file);
		imagedestroy($this->img);
		imagedestroy($this->new);
	}
}
?>