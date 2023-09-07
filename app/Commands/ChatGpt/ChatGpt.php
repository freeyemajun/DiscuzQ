<?php
namespace App\Commands\ChatGpt;

use Carbon\Carbon;
use App\Models\ChatGptKernel;
use App\Models\ChatGptOffMsg;
use App\Models\Thread;
use Discuz\Qcloud\QcloudManage;
use Discuz\Foundation\Application;
use Discuz\Contracts\Setting\SettingsRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Exception;

class ChatGpt
{
    public $settings = '';
    public $fandaiurl = '';
    public $hosturl = '';
    public $airenge = '';
    public $apikey = '';
    public $aiusername = '';
    public $aipassword = '';
    public $access_token = '';
    public $model = '';
    public $app;
    public $appid;
    public $secret;
    public $vrenum = 150;
    public $Voice = 101025;
    public $revoice = false;

    public $postData = [
        "model" => "gpt-3.5-turbo",
        "temperature" => 0.9,
        "stream" => false,
        "messages" => [],
    ];

    public $headers  = [];

    public function __construct() {
        $this->settings=app(SettingsRepository::class);
        $this->app = app(Application::class);
        $this->fandaiurl=$this->settings->get('fandaiurl', 'chatgpt');
        $this->airenge=$this->settings->get('airenge', 'chatgpt');
        $this->apikey=$this->settings->get('apikey', 'chatgpt');
        $this->aiusername=$this->settings->get('aiusername', 'chatgpt');
        $this->aipassword=$this->settings->get('aipassword', 'chatgpt');
        $this->hosturl=$this->settings->get('hosturl', 'chatgpt');
        $this->revoice=$this->settings->get('revoice', 'chatgpt');

        $this->appid=$this->settings->get('offiaccount_app_id', 'wx_offiaccount');
        $this->secret=$this->settings->get('offiaccount_app_secret', 'wx_offiaccount');

        $this->headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apikey
        ];

//        $this->postData['model'] = $this->model;

        $rg = [
            'role'=> 'system',
            'content'=> $this->airenge
        ];
        array_push($this->postData['messages'],$rg);
        $this->access_token=$this->login();
    }

    public function login(){
        $header = array("Content-Type:application/json");

        $url = $this->hosturl."/apiv3/users/username.login";

        $data = [
            'username'=> $this->aiusername,
            'password'=> $this->aipassword,
            'type'=> 'username_login'
        ];

        $content = $this->curlPost($url, $data,  5, $header, "json");
        file_put_contents('./1.txt', 'login'.$content."\r\n", FILE_APPEND);
        $content = json_decode($content,true);

        $access_token = $content['Data']['accessToken'];

        return $access_token;
    }

    public function repost($tid,$content){
        $header = array("Content-Type:application/json","authorization: Bearer ".$this->access_token);

        $url = $this->hosturl."/api/v3/posts.create";

        $data = [
            "attachments"=>[],
            "content"=>$content,
            "id"=>$tid,
        ];

        $content = $this->curlPost($url, $data,  5, $header, "json");
        file_put_contents('./1.txt', 'repost'.$content."\r\n", FILE_APPEND);
        $content = json_decode($content,true);
        return $content;
    }

    public function retid($touser,$content=[],$id){
        $tinfo = Thread::query()->where('id', $touser)->first();
        $tokens = ChatGptKernel::query()->where('id', $id)->first();
        if (4096 - $tokens->total_tokens < 100){
            return $this->repost($touser,'清重新开帖~');
        }
        if ($tinfo->is_approved == 1){
            $usertext = $content[count($content)-1]['content'];
            file_put_contents('./1.txt', 'usertext'.json_encode($content)."\r\n", FILE_APPEND);
            file_put_contents('./1.txt', 'usertext'.$usertext."\r\n", FILE_APPEND);
            if ($this->tencentCloudCheck($usertext)){
                $text = $this->sendtext($content,$id);
                if (!empty($text)){
                    return $this->repost($touser,$text);
                }
            }else{
                return $this->repost($touser,'您的问题包含违规内容,不能回答~');
            }
        }
    }

    public function getacctoken()
    {
        $cache = app('cache');
        $cacheKey = 'access_token';
        $access_token = $cache->get($cacheKey);
        if (isset($access_token)) {
            return $access_token;
        }
        $gacurl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->secret;
        $str = file_get_contents($gacurl);
        $str = json_decode($str, true);
        $access_token = $str["access_token"];
        file_put_contents('./1.txt', "getacctoken:".$access_token ."\r\n", FILE_APPEND);
        $expiresAt = (int)($str["expires_in"] / 60) -1;
        $cache->put($cacheKey, $access_token,$expiresAt);

        return $access_token;
    }

    public function getVoice($text) {
        $reqArr = array ();
        $reqArr['Text'] = $text;
        $reqArr['SessionId'] = Str::uuid();
        $reqArr['VoiceType'] = $this->Voice;
        file_put_contents('./1.txt', 'getVoice'.json_encode($reqArr)."\r\n", FILE_APPEND);
        $qcloud = $this->app->make('qcloud');
        $result = $qcloud->service('tts')->TextToVoice($reqArr);
        file_put_contents('./1.txt', 'getVoice'.json_encode($result)."\r\n", FILE_APPEND);

        return base64_decode($result['Audio']);
    }

    public function upload($text,$TYPE = 'voice'){
        //图片（image）、语音（voice）、视频（video）和缩略图（thumb）
        $ACCESS_TOKEN = $this->getacctoken();
        $gacurl = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$ACCESS_TOKEN}&type={$TYPE}";
        $tmpFile = tempnam(storage_path('/tmp'), 'msg');

        $file = $this->getVoice($text);
        file_put_contents($tmpFile.'.mp3',$file);

        $media = [
            "media"=>new \CURLFile($tmpFile.'.mp3'),
        ];
        $header = array("Content-Type: application/x-www-form-urlencoded");
        $res = $this->curlPost($gacurl, $media,  5,$header,'array');
        $res = json_decode($res,true);
        @unlink($tmpFile.'.mp3');
        file_put_contents('./1.txt', 'upload'.json_encode($res)."\r\n", FILE_APPEND);
        if (isset($res["media_id"])) return $res["media_id"];
    }

    public function sendmsg($touser,$content,$type = "text"){
        $ACCESS_TOKEN = $this->getacctoken();
        $gacurl = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$ACCESS_TOKEN}";
        $data = [
            "touser"=>$touser,
            "msgtype"=>$type,
        ];
        if ($type == "text") $data["text"]["content"] = $content;
        if ($type == "voice"){
            $media_id = $this->upload($content);
            $data["voice"]["media_id"] = $media_id;
        }
        $header = array("Content-Type:application/json");
        $res = $this->curlPost($gacurl, $data,  5, $header, "json",true);
        file_put_contents('./1.txt', 'sendmsg'.$res."\r\n", FILE_APPEND);
        return json_decode($res,true);
    }

    public function reoff($touser,$content=[],$id){
        $usertext = $content[count($content)-1]['content'];
        if (empty($usertext)){
            return $this->sendmsg($touser,'请不要发空白内容哦~');
        }
        file_put_contents('./1.txt', 'reoff'.$usertext."\r\n", FILE_APPEND);
        if ($this->tencentCloudCheck($usertext)){
            $text = $this->sendtext($content,$id,true);
            if (!empty($text)){
                $query2 = new ChatGptOffMsg();
                $query2->toid = $touser;
                $query2->role = 'assistant';
                $query2->msg = $text;
                $query2->dataline = time();
                $query2->save();
                if (mb_strlen($text) < $this->vrenum && $this->revoice){
                    return $this->sendmsg($touser,$text,"voice");
                }else{
                    return $this->sendmsg($touser,$text);
                }
            }
        }else{
            return $this->sendmsg($touser,'您的问题包含违规内容,不能回答~');
        }
    }

    public function sendtext($content=[],$id,$isoff = false){
        $postData = $this->postData;
        if (!empty($content)){
            foreach ($content as $v){
                array_push($postData['messages'],$v);
            }
        }
        file_put_contents('./1.txt', 'sendtext'.json_encode($postData)."\r\n", FILE_APPEND);
        $content = $this->curl_request(json_encode($postData));
        file_put_contents('./1.txt', 'content'.$content['content']."\r\n", FILE_APPEND);
        file_put_contents('./1.txt', 'total_tokens'.$content['total_tokens']."\r\n", FILE_APPEND);
        file_put_contents('./1.txt', 'completion_tokens'.$content['completion_tokens']."\r\n", FILE_APPEND);

        if ($isoff){
            ChatGptKernel::query()->where('id', $id)->update([
                'total_tokens' => (int)$content['total_tokens'],
                'completion_tokens' => (int)$content['completion_tokens']
            ]);

            $toid = ChatGptKernel::query()->where('id', $id)->first();

            if (4096 - (int)$content['total_tokens'] < 500){
                ChatGptOffMsg::query()->where('toid', $toid->toid)->where('id','<', $id)->delete();
                ChatGptKernel::query()->where('toid', $toid->toid)->where('total_tokens', '>',0)->orderBy('id', 'desc')->update([
                    'total_tokens' => 0,
                    'completion_tokens' => 0
                ]);
            }

        }
        return $content['content'];
    }

    public function tencentCloudCheck($content){
        if ($this->settings->get('qcloud_cms_text', 'qcloud', false)){
            $qcloud = $this->app->make('qcloud');
            $result = $qcloud->service('cms')->TextModeration($content);
            file_put_contents('./1.txt', 'tencentCloudCheck'.json_encode($result)."\r\n", FILE_APPEND);
            $keyWords = Arr::get($result, 'Data.Keywords', []);


            if (isset($result['Data']['DetailResult'])) {
                /**
                 * filter 筛选腾讯云敏感词类型范围
                 * Normal：正常，Polity：涉政，Porn：色情，Illegal：违法，Abuse：谩骂，Terror：暴恐，Ad：广告，Custom：自定义关键词
                 */
                $filter = ['Normal', 'Ad']; // Tag Setting 可以放入配置
                $filtered = collect($result['Data']['DetailResult'])->filter(function ($item) use ($filter) {
                    if (in_array($item['EvilLabel'], $filter)) {
                        $item = [];
                    }
                    return $item;
                });

                $detailResult = $filtered->pluck('Keywords');
                $detailResult = Arr::collapse($detailResult);
                $keyWords = array_merge($keyWords, $detailResult);
            }

            if (!blank($keyWords)) {
                return false;
            }
        }
        return true;
    }

    public function curl_request($postData){
        $curl = curl_init();

        $callback = function ($ch, $data) {
            file_put_contents('./1.txt', 'callback'.$data."\r\n", FILE_APPEND);
            $complete = json_decode($data);
            if (isset($complete->error)) {
                file_put_contents('./1.txt', 'callback'.$complete->error->message."\r\n", FILE_APPEND);
            }
            return strlen($data);
        };

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->fandaiurl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $this->headers,
//            CURLOPT_WRITEFUNCTION =>$callback,
        ));

        $response = curl_exec($curl);
        file_put_contents('./1.txt', 'curl_request'.$response."\r\n", FILE_APPEND);
        if($response=== FALSE ){
            $data = "CURL Error:".curl_error($curl);
            file_put_contents('./1.txt', 'curl_request'.$data."\r\n", FILE_APPEND);
            throw new Exception($data);
        }

        //{"total_tokens": response["usage"]["total_tokens"],
        //                    "completion_tokens": response["usage"]["completion_tokens"],
        //                    "content": response.choices[0]['message']['content']}
        $response = json_decode($response,true);

        $answer = [
            "total_tokens"=> $response["usage"]["total_tokens"],
            "completion_tokens"=> $response["usage"]["completion_tokens"],
            "content"=> $response["choices"][0]['message']['content']
        ];

//        if (substr(trim($response), -6) == "[DONE]") {
//            $response = substr(trim($response), 0, -6) . "{";
//        }
//        $responsearr = explode("}\n\ndata: {", $response);
//
//        foreach ($responsearr as $msg) {
//            $contentarr = json_decode("{" . trim($msg) . "}", true);
//            if (isset($contentarr['choices'][0]['delta']['content'])) {
//                $answer .= $contentarr['choices'][0]['delta']['content'];
//            }
//        }

        curl_close($curl);
        return $answer;
    }

    public function curlPost($url, $post_data = array(), $timeout = 5, $header = "", $data_type = "",$isun = false)
    {
        $header = empty($header) ? '' : $header;
        //支持json数据数据提交
        if ($data_type == 'json') {
            if ($isun){
                $post_string = json_encode($post_data,JSON_UNESCAPED_UNICODE);
            }else{
                $post_string = json_encode($post_data);
            }
        } elseif ($data_type == 'array') {
            $post_string = $post_data;
        } elseif (is_array($post_data)) {
            $post_string = http_build_query($post_data, '', '&');
        }

        $ch = curl_init();    // 启动一个CURL会话
        curl_setopt($ch, CURLOPT_URL, $url);     // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 对认证证书来源的检查   // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($ch, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);     // Post提交的数据包
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);     // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        $result = curl_exec($ch);

        // 打印请求的header信息
        //$a = curl_getinfo($ch);
        //var_dump($a);

        if($result=== FALSE ){
            $data = "CURL Error:".curl_error($ch);
        }

        curl_close($ch);
        return $result;
    }

}
