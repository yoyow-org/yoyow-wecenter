<?php
/**
 * Created by PhpStorm.
 * User: 神游界外
 * Date: 2018/1/16
 * Time: 11:04
 */

class yoyowauth
{
    protected $api_url = null;
    protected $platform_id = null;

    public function __construct(){
        $this->api_url = get_setting('api_url');
        $this->platform_id = get_setting('platform_id');
    }

    function get_remote_json($url){
        if(preg_match('/\{(.*)\}/', $url, $matches)) {
            $msg = json_decode(trim($matches[0]));
            if ($msg->code == 0) {
                return $msg->data;
            }else{
                return array();
            }
        }
        return array();
    }

    /**
     * 登录请求，跳转到YOYOW钱包
     */
    function login($url,$state='',$type='', $redirect_url = ''){
        $yyw_sign = $this->get_remote_json($url);
        if(isset($yyw_sign->platform)&&isset($yyw_sign->sign)&&isset($yyw_sign->time)&&$yyw_sign->platform==$this->platform_id){
                if($state!=''){
                    $redirect=base_url().'/?/account/openid/yoyow/login/';

                }else if($type!='') {
                    $state=$type;
                    $redirect=base_url().'/?/account/openid/yoyow/bind/';
                }else{
                    $state=base_url();
                    $redirect=base_url().'/?/account/openid/yoyow/bind/';
                }
                if($redirect_url) {
                    $redirect = $redirect . 'redirecturl-' . $redirect_url;
                }
            $params = array(
                'platform'=>$yyw_sign->platform,
                'time'=>$yyw_sign->time,
                'which'=>'Login',
                'sign'=>$yyw_sign->sign,
                'state'=>$state,
                'redirect'=>$redirect
            );

            header('Location:' . $yyw_sign -> url . '?'.http_build_query($params));
            exit();

        }else{

            exit();
        }

    }

    /**
     * 处理回调
     */
    function callback($yoyow,$time,$sign){
        $url = $this->api_url.'/auth/verify?yoyow='. $yoyow . '&time=' . $time . '&sign=' . $sign;
        $yoyow_array=http_get($url);
        $verify = $this->get_remote_json($yoyow_array);
        if(isset($verify->verify)&&$verify->verify==true){
           return true;
        }
        return false;
    }

}