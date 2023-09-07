<?php

namespace App\Commands\Withdraw;

class Withdraw
{
    protected $settings;
    public $mchid = '你的商户id';
    public $appid = '小程序appid';
    public $serial_no = '证书序列号'; // 证书序列号，不知道怎么获取的看上面的图

    public function __construct($appid,$mchid,$serial_no) {
        $this->appid=$appid;
        $this->mchid=$mchid;
        $this->serial_no =$serial_no;
    }


    public function transfer($openid, $trade_no, $money, $desc='备注说明')
    {
        $post_data = [
            "appid" => $this->appid,//appid
            "out_batch_no" => $trade_no,//商家批次单号
            "batch_name" => $desc,//批次名称
            "batch_remark" => $desc,//批次备注
            "total_amount" => $money,// 转账金额单位为“分”
            "total_num" => 1, // 转账总笔数
            //此处可以多笔提现  组合二维数组放到transfer_detail_list即可   我这里单笔操作，写死了
            "transfer_detail_list" => [
                [
                    'out_detail_no' => $trade_no,
                    'transfer_amount' => $money,
                    'transfer_remark' => $desc,
                    'openid' => $openid,
                ]
            ]
        ];
        $url = 'https://api.mch.weixin.qq.com/v3/transfer/batches';
        //JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE  防止中文被转义
        $result = $this->wx_post($url, json_encode($post_data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        $result = json_decode($result, true);
        if (isset($result['prepay_id'])) {
            return $result['prepay_id'];
        }
        return $result;
    }

    public function details($id){
        $url = 'https://api.mch.weixin.qq.com/v3/transfer/batches/out-batch-no/'.$id.'/details/out-detail-no/'.$id;
        //JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE  防止中文被转义
        $result = $this->wx_get($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**post请求
     * @param $url
     * @param $param
     * @return bool|string
     */
    private function wx_post($url, $param)
    {
        $authorization = $this->getV3Sign($url, "POST", $param);
        $curl = curl_init();
        $headers = [
            'Authorization:' . $authorization,
            'Accept:application/json',
            'Content-Type:application/json;charset=utf-8',
            'User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
        ];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
        curl_setopt($curl, CURLOPT_POST, true);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    private function wx_get($url)
    {
        $authorization = $this->getV3Sign($url, "GET");
        $curl = curl_init();
        $headers = [
            'Authorization:' . $authorization,
            'Accept:application/json',
            'Content-Type:application/json;charset=utf-8',
            'User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
        ];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
//        curl_setopt($curl, CURLOPT_POST, true);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    /**
     * 微信提现V3签名
     * @param $url
     * @param $http_method
     * @param $body
     * @return string
     */
    private function getV3Sign($url, $http_method, $body='')
    {
        $nonce = strtoupper($this->createNonceStr(32));
        $timestamp = time();
        $url_parts = parse_url($url);
        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));
//        $cert_dir = ROOT_PATH.DS."config".DS."payment_cert".DS."wechatpay".DS;
//        $sslKeyPath = $cert_dir."apiclient_key.pem";
        //拼接参数
        $message = $http_method . "\n" .
            $canonical_url . "\n" .
            $timestamp . "\n" .
            $nonce . "\n";
        if (isset($body)){
            $message = $message .
            $body . "\n";
        }
        $private_key = $this->getPrivateKey(storage_path().'/cert/apiclient_key.pem');
        openssl_sign($message, $raw_sign, $private_key, 'sha256WithRSAEncryption');
        $sign   = base64_encode($raw_sign);
        $token = sprintf('WECHATPAY2-SHA256-RSA2048 mchid="%s",nonce_str="%s",timestamp="%s",serial_no="%s",signature="%s"', $this->mchid, $nonce, $timestamp, $this->serial_no, $sign);
        return $token;
    }

    /**
     * 生成随机32位字符串
     * @param $length
     * @return string
     */
    public function createNonceStr($length = 16) { //生成随机16个字符的字符串
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取私钥
     * @param $filepath
     * @return false|resource
     */
    private function getPrivateKey($filepath)
    {
        return openssl_get_privatekey(file_get_contents($filepath));
    }
}
