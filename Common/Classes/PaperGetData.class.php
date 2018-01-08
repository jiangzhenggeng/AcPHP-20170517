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
		
class PaperGetData{
	public $t = false;
	//根据试卷id获取试题
	public function paper_get_question($paperData,$t = false){
		$this->t = $t;
		$ruleid = $paperData['ruleid'];
		$id = $paperData['id'];
		$rule_cfg_temp = M('rule_cfg')->where(array('ruleid'=>$ruleid))->getAll();
		$rule_cfg = array();
		foreach($rule_cfg_temp as $v){
			$rule_cfg[$v['treeid'].'-'.$v['typeid']] = $v;
		}
		unset($rule_cfg_temp);
		//过滤兼容模式的子类id
		foreach($rule_cfg as $k=>$v){
			//兼容模式考虑子章节，需要过滤子章节
			if($v['model']==2){
				$sub_subject_tree = M('subject_tree')->field('arrchildid')->where(array('treeid'=>$v['treeid']))->getOne();
				$arrchildid_array = eval('return array('.$sub_subject_tree['arrchildid'].');');
				foreach($rule_cfg as $k2=>$v2){
					//是同一种题型
					if($v2['treeid']!=$v['treeid'] && $v2['typeid']==$v['typeid'] && in_array($v2['treeid'],$arrchildid_array)){
						unset($rule_cfg[$v2['treeid'].'-'.$v2['typeid']]);
					}
				}
				$rule_cfg[$v['treeid'].'-'.$v['typeid']]['arrchildid'] = $sub_subject_tree['arrchildid'];
			}else{
				$rule_cfg[$v['treeid'].'-'.$v['typeid']]['arrchildid'] = $v['treeid'];
			}
		}

		$questionObj = M('question');
		foreach($rule_cfg as $k=>$v){
			$temp_data = $questionObj->field('questionid')
				->where(array('typeid'=>$v['typeid']))
				->where(array('treeid'=>array($v['arrchildid'],'IN')))->getAll();
				
			//随机算法抽取规定数量的试题
			$this->randomGetQuestionData($temp_data,$v,$id);
		}
		$rule = M('rule')->field('subjectid')->where(array('ruleid'=>$ruleid))->getOne();
		$subjectid = $rule['subjectid'];
		
		$find_question_data_id_string = '';
		//还差多少道题 key为题型value为数量
		$discrepancy = array();
		//当前已经获取的体量 key为题型value为数量
		$cuur_has_number = array();

		$this->find_question_data_id_string = substr($this->find_question_data_id_string,1);
		//从整体题库获取差于的试题
		foreach($this->discrepancy as $k => $v ){
			$where = array('typeid'=>$k,'subjectid'=>$subjectid);
			if($this->find_question_data_id_string!=''){
				$where['questionid'] = array($this->find_question_data_id_string,'NOT IN');
			}
			$count = $questionObj->clearWhere()->field('questionid')->where($where)->count();

			//题库量不够
			if($count<=$v) {
				$temp_data = $questionObj->clearWhere()->field('questionid')->where($where)->getAll();
			}else{
				//产生一个起点随机数
				$start = rand(0,$count-$v);
				$temp_data = $questionObj->clearWhere()->field('questionid')->where($where)->limit($start.','.$v)->getAll();
			}
			$has_count = count($temp_data);
			foreach($temp_data as $k2=>$v2){
				$this->find_question_data[$v2['questionid']] = array(
					'id'=>$id,
					'questionid'=>$v2['questionid'],
					'typeid'=>$k
				);
				$find_question_data_id_string .= ','.$v2['questionid'];
			}
			$this->discrepancy[$k] = $v - $has_count;
			$this->cuur_has_number[$k] += $has_count;
		}
		
		$this->find_question_data_id_string .= $find_question_data_id_string;	
		return $this->find_question_data;
	}
	
	//已经获得的试题
	private $find_question_data = array();
	//已经获得的试题id字符串
	private $find_question_data_id_string = '';
	//还差多少道题 key为题型value为数量
	private $discrepancy = array();
	//当前已经获取的体量 key为题型value为数量
	private $cuur_has_number = array();
	//随机算法抽取规定数量的试题
	private function randomGetQuestionData($all_question,$rule,$id){
		$max_index = count($all_question);
		//如果体量不够
		if($max_index<=$rule['number']){
			foreach($all_question as $k=>$v){
				$this->find_question_data[$v['questionid']] = array(
					'id'=>$id,
					'questionid'=>$v['questionid'],
					'typeid'=>$rule['typeid']
				);
				$this->find_question_data_id_string .= ','.$v['questionid'];
			}
			if(!isset($this->discrepancy[$rule['typeid']])) $this->discrepancy[$rule['typeid']] = 0;
			if(!isset($this->cuur_has_number[$rule['typeid']])) $this->cuur_has_number[$rule['typeid']] = 0;
			$this->discrepancy[$rule['typeid']] += $rule['number'] - $max_index;
			$this->cuur_has_number[$rule['typeid']] += $max_index;
			return;
		}
		//一条规则的当前题量
		$seil_cuur_has_number = 0;
		//无限循环直到获取足够数量的试题
		while(true){
			//如果试题数量满啦，就结束循环
			if($seil_cuur_has_number==$rule['number']) break;
			
			//产生一个下标
			$index = rand(0,$max_index-1);
			//如果该小标的试题不存在
			if(!isset($this->find_question_data[$all_question[$index]['questionid']])){
				$this->find_question_data[$all_question[$index]['questionid']] = array(
					'id'=>$id,
					'questionid'=>$all_question[$index]['questionid'],
					'typeid'=>$rule['typeid']
				);
				$this->find_question_data_id_string .= ','.$all_question[$index]['questionid'];
				if(!isset($this->cuur_has_number[$rule['typeid']]))
					$this->cuur_has_number[$rule['typeid']] = 0;
				$this->cuur_has_number[$rule['typeid']]++;
				$seil_cuur_has_number++;
			}
		}
	}
}