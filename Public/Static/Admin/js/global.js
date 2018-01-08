/**
 +----------------------------------------------------------
 * 窗口全局变量
 +----------------------------------------------------------
 */
window.W_HEIGHT = $(window).height();
window.W_WIDTH = $(window).width();
$(window).resize(function(){
	window.W_HEIGHT = $(window).height();
	window.W_WIDTH = $(window).width();
});



function getLocalTime(nS) {     
	return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");      
}
/**
 +----------------------------------------------------------
//自定义简单模板引擎函数 增强版
//如果没有设置传递data参数则会返回一个将模板编译好的函数
//如果传入data就直接返回生成好的模板
 +----------------------------------------------------------
 */
function newTplEngine(tpl, data) {
	var _match = null,
		start_index = 0,
		LEFT_DELIMITER = '<%',
		RIGHT_DELIMITER = '%>',
		function_body = null;
	
	//构造函数头部
	function_body= '\nvar r=[];\nvar fn = (function(__data__){\n',
	function_body += "var _template_varName='';\n";
	function_body += "for(var _name in __data__){\n";
	function_body += "_template_varName+=('var '+_name+'=__data__[\"'+_name+'\"];');\n";
	function_body += "};\neval(_template_varName);\n";
		
	//HTML转义
    newTplEngine._encodeHTML = function (source) {
        return String(source)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/\\/g,'&#92;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&#39;');
    };

    //转义影响正则的字符
    newTplEngine._encodeReg = function (source) {
        return String(source).replace(/([.*+?^=!:${}()|[\]/\\])/g,'\\$1');
    };
	
	//取得分隔符
	var _left_ = LEFT_DELIMITER;
	var _right_ = RIGHT_DELIMITER;

	//对分隔符进行转义，支持正则中的元字符，可以是HTML注释 <!  !>
	var _left = newTplEngine._encodeReg(_left_);
	var _right = newTplEngine._encodeReg(_right_);
	//创建匹配规则
	var _RegExp = new RegExp(_left+'([^('+_left+'|'+_right+')].*?)'+_right,'g');
	tpl.replace(_RegExp,function(match_all,match_target,index,resource){
		//构造函数体
		function_body += 'r.push("'+tpl.substr(start_index,index-start_index).replace(new RegExp("[\\r\\t]","g"), "").replace(/"/g,'\\"').replace(/\n/g,'\\n')+'");\n';
		var _match_target = match_target.toString().replace(/(^\s*)|(\s*$)/g,'');
		if(match_target.substr(0,1)=='='){
			//如果是变量并且不转意
			function_body += 'r.push(typeof('+_match_target.substr(1)+') === "undefined"?"":'+_match_target.substr(1)+');\n';
		}else if(match_target.substr(0,1)=='@'){
			//如果是变量并且不转意
			function_body += 'r.push(typeof('+_match_target.substr(1).replace(/\((.*?)\)(.*)/g,'')+') !== "function"?"":'+_match_target.substr(1).replace(/\)(.*)/g,')')+');\n';
		}else if(match_target.substr(0,3).toLowerCase()==':v='){
			//如果是变量并且转意
			function_body += 'r.push(typeof('+_match_target.substr(3)+') === "undefined"?"":newTplEngine._encodeHTML('+_match_target.substr(3)+'));\n';
		}else if(match_target.substr(0,3).toLowerCase()==':u='){
			//如果是变量并且进行URL编码
			function_body += 'r.push(typeof('+_match_target.substr(3)+') === "undefined"?"":encodeURI('+_match_target.substr(3)+'));\n';
		}else{
			//直接是js代码
			function_body += _match_target+'\n';
		}
		start_index = index + match_all.length;
		return '';
	});
	//模板最后一个标签遗留下的部分
	function_body += 'r.push("'+tpl.substr(start_index).replace(new RegExp("[\\r\\t]","g"), "").replace(/"/g,'\\"').replace(/\n/g,'\\n')+'");\n';
	function_body += '})(__template_data__);';
	//合并函数体
	function_body += 'return r.join("");';
	//构造一个后台函数
	var fn = new Function('__template_data__',function_body);
	if( data!=undefined ){
		return fn(data);
	}else{
		return fn;
	}
}

/**
 +----------------------------------------------------------
 * 菜单自适应高度
 +----------------------------------------------------------
 */
function acMenu(){
	$('#ac-menu,#ac-content').css('height',W_HEIGHT-66);
	$('#iframeMain').css('height',W_HEIGHT-66-45);
}

/**
 +----------------------------------------------------------
 * 全选事件
 +----------------------------------------------------------
 */
function selectcheckbox(form) {
    for (var i = 0; i < form.elements.length; i++) {
        var e = form.elements[i];
        if (e.name != 'chkall' && e.disabled != true) e.checked = form.chkall.checked;
    }
}

/**
 +----------------------------------------------------------
 * 响应点击返回按钮事件
 +----------------------------------------------------------
 */
$(function(){
	$('.ac-back').click(function(){
		window.history.back();
	});
	
	var search = $('.ac-table-search'),time = 200;
			
	$('.ac-search').click(function(){
		if (search.css('display')=='none'){
			search.slideDown(time);
		}else{
			search.slideUp(time);
		}
	});
});

/**
 +----------------------------------------------------------
 * 绑定所有删除操作
 +----------------------------------------------------------
 */
 $(function(){
	var data_delete = $('a[data-delete]');
	if(data_delete.length<=0) return true;
	data_delete.click(function(e){
		e.preventDefault();
		var href = $(this).attr('href');
		parent.layer.confirm('你确定执行删除操作吗？', {icon: 3, title:'提示'}, function(index){
			parent.layer.close(index);
			window.location = href;
		});
	});
});

/**
 +----------------------------------------------------------
 * 展开与关闭所有菜单
 +----------------------------------------------------------
 */
function expandAll(obj,tr){
	var category_body = [];
	var layerLoading = layer.load(0);
	if( $(obj).attr('flag')=='none' ){
		$(obj).attr('flag','block').val('全部收起');
		for(var i=0,tr_len=tr.length;i<tr_len;i++){
			if( tr.eq(i).css('display')=='none' ){
				category_body[i] = i;
				tr.eq(i).css('display','table-row');
			}
			tr.eq(i).find('.ac-query-click').attr('flag','block').addClass('on');
		}
	}else{
		$(obj).attr('flag','none').val('全部展开');
		for(var i=0,category_body_len=category_body.length;i<category_body_len;i++){
			tr.eq(category_body[i]).css('display','none');
		}
		tr.find('.ac-query-click').attr('flag','none').removeClass('on');
	}
	layer.close(layerLoading);
}


/**
 +----------------------------------------------------------
 * 点击关闭或者展开菜单
 +----------------------------------------------------------
 */
function clickOpenMenuTree(subject_tree_table,keyname){

	subject_tree_table.on('click','.ac-query-click,td',function(e){
		var _this = $(this) , showAll = false;
		if($(this).find('.ac-query-click').length>0){
			_this = $(this).find('.ac-query-click');
			showAll = true;
		}
		e.stopPropagation();
		var flag = _this.attr('flag'),
			data_obj = _this.parent().parent(),
			arrchildid = data_obj.attr('arrchildid'),
			parentid = data_obj.attr(keyname);
		
		var tr = data_obj.nextAll();
		var tr_len = tr.length,treeid = '';
		
		arrchildid = ','+arrchildid+',';
		
		var hasSub = false;
		for(var i=0;i<tr_len;i++){
			treeid = tr.eq(i).attr(keyname);

			if(showAll==false){
				if( flag=='none' ){
					tr.eq(i).filter(function(index) {
						return $(this).attr('parentid')==parentid;
					}).css('display','table-row');
					_this.attr('flag','block').addClass('on');
				}else{
					if( arrchildid.match(','+treeid+',') ){
						tr.eq(i).css('display','none');
						_this.add(tr.eq(i).find('.ac-query-click')).attr('flag','none').removeClass('on');
					}
					tr.eq(i).filter(function(index) {
						return $(this).attr('parentid')==parentid;
					});
				}
			}else{		
				if( arrchildid.match(','+treeid+',') ){
					if( flag=='none' ){
						tr.eq(i).css('display','table-row');
						_this.add(tr.eq(i).find('.ac-query-click')).attr('flag','block').addClass('on');
					}else{
						tr.eq(i).css('display','none');
						_this.add(tr.eq(i).find('.ac-query-click')).attr('flag','none').removeClass('on');
					}
					hasSub = true;
				}
			}
		}
		if(!hasSub){
			if( flag=='none' ){
				_this.attr('flag','block').addClass('on');
			}else{
				_this.attr('flag','none').removeClass('on');
			}
		}
	});
	//绑定手型事件
	$(".ac-query-click").parent().css('cursor','pointer');
}

/**
 +----------------------------------------------------------
 * 产生一个不重复的随机id
 +----------------------------------------------------------
 */
function randomID(){
	return 'random_id_'+Math.random().toString().replace('.','');
}


/**
 +----------------------------------------------------------
 * 产生一个不重复的随机id
 +----------------------------------------------------------
 */
function ischeck(self){
	var url = $(self).attr('url');
	var layerHander2 = layer.msg('加载中', {icon:16,time: 100*1000});
	var _this = $(self) , ischeck = _this.attr('ischeck');
	$.get(url,{ischeck:ischeck},function(replay){
		layer.close(layerHander2);
		if(replay.code==0){
			layer.msg(replay.message, {icon:1});
			_this.html(replay.innerText);
			if(parseInt(ischeck)==1)
				_this.attr('ischeck',0);
			else
				_this.attr('ischeck',1);
		}else{
			layer.msg(replay.message, {icon:2});
		}
	},'json');
}





