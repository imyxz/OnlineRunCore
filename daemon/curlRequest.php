<?php
class curlRequest{
    private $cookie;
    private $response_cookie;
    private $header=array();
    private $response_code;
    private $response;
    public function setCookie($arr)
    {
        $this->cookie='';
        foreach($arr as $key=>&$value)
        {
            $this->cookie=$this->cookie . ' '. htmlspecialchars($key) . "=" . htmlspecialchars($value) .';';
        }
        $this->cookie= substr($this->cookie,0,strlen($this->cookie)-1);
    }
    public function setHeader($value)
    {
        $this->header[]=$value;
    }
    public function setCookieRaw($cookie)
    {
        $this->cookie=$cookie;
    }
    public function post($url, $post,$timeout=10)
    {
        $request_data='';
        if(is_array($post))
        {
            $request_array=array();
            foreach($post as $key=>&$one)
            {
                $request_array[]=urlencode($key) . "=" . urlencode($one);
            }
            $request_data=implode("&",$request_array);
        }
        else
            $request_data=$post;

        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_POSTFIELDS =>$request_data
        );

        $ch = curl_init();
        //curl_setopt ($ch, CURLOPT_PROXY, "127.0.0.1:8888");
        curl_setopt_array($ch, ( $defaults));
        curl_setopt($ch,CURLOPT_COOKIE,$this->cookie);
        if(!empty($this->header))
            curl_setopt($ch,CURLOPT_HTTPHEADER,$this->header);
        $cookie_filename=tempnam(sys_get_temp_dir(),"orc");
        curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_filename);
        $this->response_code=0;
        $result = curl_exec($ch);
        $this->response=$result;
        $this->response_code=curl_getinfo($ch,CURLINFO_HTTP_CODE );
        $error=curl_error($ch);
        curl_close($ch);
        $this->response_cookie=$this->processCookieJar(file_get_contents($cookie_filename));
        unlink($cookie_filename);
        if( ! $result)
        {
            echo $error;
            return false;
        }

        return $result;
    }
    public function get($url,$timeout=10)
    {
        $defaults = array(
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => $timeout
        );

        $ch = curl_init();
        curl_setopt_array($ch, ( $defaults));
        curl_setopt($ch,CURLOPT_COOKIE,$this->cookie);
        $this->response_code=0;

        if(!empty($this->header))
            curl_setopt($ch,CURLOPT_HTTPHEADER,$this->header);
        $cookie_filename=tempnam(sys_get_temp_dir(),"orc");
        curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_filename);
        $this->response_code=0;
        $result = curl_exec($ch);
        $this->response=$result;
        $this->response_code=curl_getinfo($ch,CURLINFO_HTTP_CODE );
        $error=curl_error($ch);
        curl_close($ch);
        $this->response_cookie=$this->processCookieJar(file_get_contents($cookie_filename));
        unlink($cookie_filename);

        if( ! $result)
        {
            echo $error;
            return false;
        }

        return $result;
    }
    public function getResponseCookie()
    {
        return $this->response_cookie;
    }
    public function getResponseCode()
    {
        return $this->response_code;
    }
    private function processCookieJar($str)
    {
        $str=str_replace("\t"," ",$str);
        $str=str_replace("\r","",$str);
        $arr=explode("\n",$str);
        $ret=array();
        foreach ($arr as $one) {
            $two=explode(" ",$one);
            if(count($two)!=7)
                continue;
            $ret[$two[5]]=$two[6];
        }
        return $this->cookieArr2Str($ret);

    }
    public function cookieArr2Str($arr)
    {
        $cookie="";
        foreach($arr as $key=>&$value)
        {
            $cookie=$cookie . ' '. htmlspecialchars($key) . "=" . htmlspecialchars($value) .';';
        }
        return substr($cookie,0,strlen($cookie)-1);
    }
    public function cookieStr2Arr($str)
    {
        $ret=array();
        $one=explode(";",$str);
        foreach($one as $two)
        {
            $three=explode("=",$two);
            if(count($three)==2)
                $ret[trim($three[0])]=trim($three[1]);
        }
        return $ret;
    }
}