<?php

class core_http{

    public function __construct()
    {
    }
    /**
     * GET 请求c
     * @param string $url
     * @param string $params
     */
    public function get($url, $params){
        $oCurl = curl_init();
        $url = $this->joinParams($url, $params);
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 15 );
        curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
        curl_setopt($oCurl, CURLOPT_HEADER, false);
        curl_setopt($oCurl, CURLINFO_HEADER_OUT, false);
        $sContent = $this->execCURL($oCurl);
        return $sContent;
    }
    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @return string content
     */
    public function post($url, $params, $data){
        $oCurl = curl_init();
        $url = $this->joinParams($url, $params);
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 15 );
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
        curl_setopt($oCurl, CURLOPT_HEADER, false);
        curl_setopt($oCurl, CURLINFO_HEADER_OUT, false);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data)));

        $sContent = $this->execCURL($oCurl);
        curl_close($oCurl);
        return $sContent;
    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @return string content
     */
    public function postsms($url, $params, $data){
        $oCurl = curl_init();
        $url = $this->joinParams($url, $params);
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
        curl_setopt($oCurl, CURLOPT_HEADER, false);
        curl_setopt($oCurl, CURLINFO_HEADER_OUT, false);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data),
            'X-LC-Id:'.get_setting("sms_app_id"),
            'X-LC-Key:'.get_setting("sms_app_key")
        ));


        $sContent = $this->execCURL($oCurl);
        curl_close($oCurl);
        return $sContent;
    }

    /**
     * 执行CURL请求，并封装返回对象
     */
    private function execCURL($ch){
        $response = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {
            return $response;
        }
        return null;
    }

    private function joinParams($path, $params)
    {
        $url =  $path;
        if (count($params) > 0)
        {
            $url = $url . "?";
            foreach ($params as $key => $value)
            {
                $url = $url . $key . "=" . $value . "&";
            }
            $length = count($url);
            if ($url[$length - 1] == '&')
            {
                $url = substr($url, 0, $length - 1);
            }
        }
        return $url;
    }
}