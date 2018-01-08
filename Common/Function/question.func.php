<?php

/**
 * @param $typeid
 * @return string
 * 根据题型id返回对应的题型名称
 */
function getQuestionTypeName($typeid){
    switch($typeid){
        case 1: return '单项选择题';
        case 2: return '多项选择题';
        case 3: return '判断题';
        case 4: return '不定项选择题';
        case 5: return '问答题';
        case 6: return '会计分录';
        case 7: return '填空题';
        case 8: return '综合题';
        default: return '未知题型';
    }
}

function ac_difficulty($difficulty){
    switch ($difficulty){
        case 1:
            return'简单';
            break;
        case 2:
            return'易';
            break;
        case 3:
            return'中难';
            break;
        case 4:
            return'难';
            break;
        case 5:
            return'困难';
            break;
        default:
            return'未知';

    }
}


function ac_hase_question4($dataArrayTemp){
    $caseIdArray = '';
    $dataArray = [];
    $dataArrayCopy =[];
    $dataArrayCopy_header =[];
    $dataArrayCopy_last =[];
    $is_type_4 = false;

    $caseIdArray = [];
    foreach($dataArrayTemp as $k => $v ){
        $is_center = false;
        //中间部分
        if($v['typeid']==4){
            $caseIdArray[] = $v['questionid'];
            $dataArray[$v['questionid']] = $v;
            $is_type_4 = true;
            $is_center = true;
        }
        //前一部分
        if(!$is_type_4) $dataArrayCopy_header[] = $v;
        //后边部分
        if(count($caseIdArray) && !$is_center) $dataArrayCopy_last[] = $v;
    }
    $caseIdArray = implode(',',$caseIdArray);

    if($caseIdArray!=''){
        $question_option_multiple_model = M('question_option_multiple');
        $multiple_option = $question_option_multiple_model
            ->where(array('questionid'=>[$caseIdArray,'in']))
            ->where(array('parentid'=>0))
            ->order('questionid asc,listorder asc')->getAll();

        foreach($dataArrayCopy_header as $k => $v ){
            $dataArrayCopy[] = $v;
        }
        foreach($multiple_option as $k => $v ){
            $dataArrayCopy[] = array_merge($v,$dataArray[$v['questionid']]);
        }
        foreach($dataArrayCopy_last as $k => $v ){
            $dataArrayCopy[] = $v;
        }

    }else{
        $dataArrayCopy = $dataArrayTemp;
    }
    return $dataArrayCopy;
}
