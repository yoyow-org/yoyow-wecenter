function loginTp(type, _this) {
    if(!$(_this).parent().hasClass('checked')) {
        triggerRelation(_this);
        //实际操作
        if(type=='否'){
            $('#settings_form').find('input[name="integral_system_config_login_base"]').attr("disabled",true);
            $('#settings_form').find('input[name="integral_system_config_login_cycle"]').attr("disabled",true);
            $('#settings_form').find('input[name="integral_system_config_login_coefficient"]').attr("disabled",true);

        }else if(type=='是') {
            $('#settings_form').find('input[name="integral_system_config_login_base"]').attr("disabled",false);
            $('#settings_form').find('input[name="integral_system_config_login_cycle"]').attr("disabled",false);
            $('#settings_form').find('input[name="integral_system_config_login_coefficient"]').attr("disabled",false);
        }
    }
}

function newQuestion(type,_this){
    if(!$(_this).parent().hasClass('checked')) {
        triggerRelation(_this);
        //实际操作
        if(type=='否'){
            $('#settings_form').find('input[name="integral_system_config_new_question"]').attr("disabled",true);
            $('#settings_form').find('input[name="integral_system_config_new_question_limit"]').attr("disabled",true);

        }else{
            $('#settings_form').find('input[name="integral_system_config_new_question"]').attr("disabled",false);
            $('#settings_form').find('input[name="integral_system_config_new_question_limit"]').attr("disabled",false);
        }
    }
}

function newArticle(type,_this){
    var $thisParent = $(_this).parent();
    if(!$(_this).parent().hasClass('checked')) {
        triggerRelation(_this);
        //实际操作
        if(type=='否'){
            $('#settings_form').find('input[name="integral_system_config_new_article"]').attr("disabled",true);
            $('#settings_form').find('input[name="integral_system_config_new_article_limit"]').attr("disabled",true);

        }else{
            $('#settings_form').find('input[name="integral_system_config_new_article"]').attr("disabled",false);
            $('#settings_form').find('input[name="integral_system_config_new_article_limit"]').attr("disabled",false);
        }
    }
}
function questionDelete(type,_this){
    if(!$(_this).parent().hasClass('checked')) {
        triggerRelation(_this);
        //实际操作
        if(type=='否'){
            $('#settings_form').find('input[name="integral_system_config_question_delete"]').attr("disabled",true);

        }else{
            $('#settings_form').find('input[name="integral_system_config_question_delete"]').attr("disabled",false);
        }
    }
}

function articleDelete(type,_this){
    if(!$(_this).parent().hasClass('checked')) {
        triggerRelation(_this);
        //实际操作
        if(type=='否'){

            $('#settings_form').find('input[name="integral_system_config_article_delete"]').attr("disabled",true);

        }else{
            $('#settings_form').find('input[name="integral_system_config_article_delete"]').attr("disabled",false);
        }
    }
}
function authConfig(type,_this){
    if(!$(_this).parent().hasClass('checked')) {
        triggerRelation(_this);
        //实际操作
        if(type=='否'){
            $('#settings_form').find('input[name="integral_pep_auth_config"]').attr("disabled",true);

        }else{
            $('#settings_form').find('input[name="integral_pep_auth_config"]').attr("disabled",false);
        }
    }
}

function orgConfig(type,_this){
    if(!$(_this).parent().hasClass('checked')) {
        triggerRelation(_this);
        //实际操作
        if(type=='否'){
            $('#settings_form').find('input[name="integral_org_auth_config"]').attr("disabled",true);

        }else{
            $('#settings_form').find('input[name="integral_org_auth_config"]').attr("disabled",false);
        }
    }
}

function questionbyanswer(type,_this){
    if(!$(_this).parent().hasClass('checked')) {
        triggerRelation(_this);
        //实际操作
        if(type=='否'){
            $('#settings_form').find('input[name="integral_system_config_question_by_answer"]').attr("disabled",true);

        }else{
            $('#settings_form').find('input[name="integral_system_config_question_by_answer"]').attr("disabled",false);
        }
    }
}

function articlebyanswer(type,_this){
    if(!$(_this).parent().hasClass('checked')) {
        triggerRelation(_this);
        //实际操作
        if(type=='否'){
            $('#settings_form').find('input[name="integral_system_config_article_by_answer"]').attr("disabled",true);

        }else{
            $('#settings_form').find('input[name="integral_system_config_article_by_answer"]').attr("disabled",false);
        }
    }
}


function triggerRelation(_this) {
    if($(_this).hasClass('checked')) {return;}
    $(_this).parentsUntil('.form-group').find('.checked').removeClass('checked');
    $(_this).addClass('checked');
}
