<?php


function ac_pay_status($status){
    switch ($status){
        case 1:
            return'未支付';
            break;
        case 2:
            return'正在支付';
            break;
        case 3:
            return'支付成功';
            break;
        case 4:
            return'支付成功';
            break;
        default:
            return'未知';

    }
}

function ac_order_status($status){
    switch ($status){
        case -9:
            return'订单删除';
            break;
        case -4:
            return'退款订单';
            break;
        case -3:
            return'无效订单';
            break;
        case -2:
            return'订单关闭';
            break;
        case 1:
            return'等待支付';
            break;
        case 2:
            return'待发货';
            break;
        case 3:
            return'待收货';
            break;
        case 4:
            return'待退款';
            break;
        case 5:
            return'订单完成';
            break;
        case 6:
            return'完成支付单支付价格异常';
            break;
        default:
            return'未知';

    }
}
function ac_order_type($type){
    switch ($type){
        case 1:
            return'题库';
            break;
        case 2:
            return'教材';
            break;
        default:
            return'未知';

    }
}

function ac_pay_type_name($type){
    switch ($type){
        case 'ali':
            return'支付宝';
            break;
        case 'ax':
            return'微信';
            break;
        default:
            return'未知';

    }
}