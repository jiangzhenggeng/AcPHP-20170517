function vMobile(mobile){
    if(!/^1(3|4|5|7|8)\d{9}$/.test(mobile.toString().replace(/[^\d]/g,''))){
        return false;
    }
    return true;
}

function vPassword(password){
    var len = password.length;
    if( len<6 || len>16 ){
        return false;
    }
    return true;
}

$('#login').click(function(){

    if(!vMobile($('#mobile').val())){
        layer.msg('手机号码不正确');
        return;
    }

    if( ! vPassword($('#password').val()) ){
        layer.msg('密码格式不正确');
        return;
    }

    var toast = layer.msg('正在登录中',{time:99999});
    var formData = $('#loginForm').serialize();
    $.ajax({
        url:window.PcsURL['loginUrl'],
        data:formData,
        accepts:'json',
        success: function(replayData,status){
            if(replayData.status=='success'){
                layer.msg('登录成功,跳转到首页',{time:2000},function () {
                    window.location.reload();
                });
                return;
            }
            layer.msg(replayData.message);
        },
        complete: function(){
            layer.close(toast);
        }
    });
});