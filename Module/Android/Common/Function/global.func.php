<?php

/**
 * @return bool
 * 归类
 */
function subject_class($data){
    $majorids = [];
    foreach ($data as $k => $v ){
        if(!in_array($v['majorid'],$majorids)){
            $majorids[] = $v['majorid'];
        }
    }
    if(empty($majorids)){
        return $data;
    }

    $major_data = M('major')->where([
        'majorid'=>[$majorids,'in']
    ])->order('listorder desc,majorid desc')->getAll();
    foreach ($major_data as $k => $v ){
        foreach ($data as $k2 => $v2 ){
            if( $v2['majorid']==$v['majorid'] ){
                $major_data[$k]['subject'][] = $v2;
                unset($data[$k2]);
            }
        }
    }

    return $major_data;

}



