<?php
namespace library;

class Response extends \think\Response {

    /**
     * 返回JSON
     *
     * @param mixed  $data
     * @param array  $header
     * @param int    $code
     *
     * @return \think\Response
     */
    final public function json($data, $header = [], $code = 200)
    {
		return $this->create($data, 'json', $code, $header);
	}

    /**
     * 返回跨域头
     *
     * @return array
     */
    public function cors()
    {
        $http_origin = (\think\Request::instance()->server()['HTTP_ORIGIN'] ?? null);
        if(env('APP_ENV') == 'dev'){
            return [
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Allow-Headers' => 'Content-Type,Authorization,Cookie',
                'Access-Control-Allow-Methods' => 'GET, POST',
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Allow-Origin' => $http_origin,
                'P3P' => 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"',
            ];
        }
        return (preg_match('/(\.chat\.com)|(\.chat\.net)|(localhost)|(127\.0\.0\.1)$/', explode(':', $http_origin)[1]??'') ? [
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers' => 'Content-Type,Authorization,Cookie',
            'Access-Control-Allow-Methods' => 'GET, POST',
            'Access-Control-Max-Age' => 86400,
            'Access-Control-Allow-Origin' => $http_origin,
            'P3P' => 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"',
        ] : []);
    }

    /**
     * 返回API JSON {"code":1000,"msg":"OK","data":{..}}
     *
     * @param array  $data
     * @param int    $code
     * @param string $msg
     * @param mixed  $trace
     * @param bool $useEncrypt 是否使用加密
     * @return \think\Response
     */
    final public function api($data = [], $code = 1000, $msg = '操作成功', $trace = null, $useEncrypt = true)
    {
        if(is_object($data) && method_exists($data, 'serialize')) $data = $data->serialize();
        if(is_array($data) && empty($data)) $data = (object)$data;

        $data = [ 'code' => $code, 'message'  => $msg, 'data' => $data];
        if(!empty($trace)){
            $data['trace']=$trace;
        }
        $returnValue =  $this->json($data);
        return $returnValue;
    }

    /**
     * HTTP跳转 301/302
     *
     * @param string $url
     * @param bool   $temporary 是否永久跳转
     *
     * @return \think\Response
     */
    final public function redirect($url, $temporary = true)
    {
        $this->code($temporary?302:301);
        return $this->header('location', $url);
    }
}
