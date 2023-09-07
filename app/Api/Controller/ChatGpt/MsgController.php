<?php
namespace App\Api\Controller\ChatGpt;

use App\Models\ChatGptKernel;
use App\Models\ChatGptOffMsg;
use Discuz\Http\DiscuzResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Settings\SettingsRepository;

class MsgController implements RequestHandlerInterface
{
    protected $settings;
    protected $blockSize;
    protected $appid;
    protected $secret;
    protected $offiaccount_close;
    protected $offiaccount;
    protected $voice = "<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[voice]]></MsgType>
  <Voice>
    <MediaId><![CDATA[%s]]></MediaId>
  </Voice>
</xml>";

    protected $text = "<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[text]]></MsgType>
  <Content><![CDATA[%s]]></Content>
</xml>";

    protected $picutext = "<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[news]]></MsgType>
  <ArticleCount>1</ArticleCount>
  <Articles>
    <item>
      <Title><![CDATA[%s]]></Title>
      <Description><![CDATA[%s]]></Description>
      <PicUrl><![CDATA[%s]]></PicUrl>
      <Url><![CDATA[url]]></Url>
    </item>
  </Articles>
</xml>";

    public function __construct(SettingsRepository $setting)
    {
        $this->settings = $setting;
        $this->blockSize = 32;
        $this->appid=$this->settings->get('offiaccount_app_id', 'wx_offiaccount');
        $this->secret=$this->settings->get('offiaccount_app_secret', 'wx_offiaccount');
        $this->offiaccount_close=(bool)$this->settings->get('offiaccount_close', 'wx_offiaccount');
        $this->offiaccount=(bool)$this->settings->get('offiaccount', 'chatgpt');
        $this->aesKey = base64_decode($this->settings->get('oplatform_app_aes_key', 'wx_oplatform') . '=');
        $this->serverToken = $this->settings->get('oplatform_app_token', 'wx_oplatform');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $xmltext = $request->getBody()->getContents();
        $signature = Arr::get($request->getQueryParams(), 'signature','');
        $msg_signature = Arr::get($request->getQueryParams(), 'msg_signature','');
        $echostr = Arr::get($request->getQueryParams(), 'echostr','');
        $timestamp = Arr::get($request->getQueryParams(), 'timestamp','');
        $nonce = Arr::get($request->getQueryParams(), 'nonce','');
        $createmenu = Arr::get($request->getQueryParams(), 'createmenu','');

        file_put_contents('./1.txt', "MsgController:".$xmltext ."\r\n", FILE_APPEND);
        file_put_contents('./1.txt', "getQueryParams:".json_encode($request->getQueryParams()) ."\r\n", FILE_APPEND);

        if (empty($xmltext) && self::checkSign($signature,$timestamp,$nonce)){
            return DiscuzResponseFactory::HtmlResponse($echostr);
        }

        if (!empty($xmltext)){
            $result = $this->decryptMessage($xmltext, $msg_signature, $timestamp, $nonce);

            if (isset($result['MsgType'])) $MsgType = $result['MsgType'];
            if (isset($result['ToUserName'])) $tousername = $result['ToUserName'];
            if (isset($result['FromUserName'])) $FromUserName = $result['FromUserName'];
            if (isset($result['PicUrl'])) $PicUrl = $result['PicUrl'];
            if (isset($result['Content'])) $Content = $result['Content'];
            if (isset($result['MediaId'])) $MediaId = $result['MediaId'];
            if (isset($result['Event'])) $Event = $result['Event'];
            if (isset($result['Recognition'])) $Recognition = $result['Recognition'];
            if (isset($result['MsgId'])) $MsgId = $result['MsgId'];
            $cache = app('cache');
            $cacheKey = 'MsgId';
            $cacheData = $cache->get($cacheKey);

            if ($cacheData != $MsgId){
                $cache->put($cacheKey, $MsgId);
//                if (isset($Event) && $Event == 'subscribe'){
//                    $media_id ='IXon6S67ZfgAdHkEfOKnpcVfpKmuDt7XwOis8C4PeZY';
//                    $from_xml = sprintf($this->voice, $FromUserName, $tousername, time(), $media_id);
//                    $encrypted = $this->encryptMessage($from_xml, $timestamp, $nonce);
//                    return DiscuzResponseFactory::XmlResponse($encrypted);
//                }

                if ($this->offiaccount_close && $this->offiaccount){
                    $from_xml = sprintf($this->text, $FromUserName, $tousername, time(), '正在帮您询问,请稍后~');
                    file_put_contents('./1.txt', "from_xml :".$from_xml ."\r\n", FILE_APPEND);

                    $encrypted = $this->encryptMessage($from_xml, $timestamp, $nonce);

                    if (!empty($Content)){
                        $query = new ChatGptKernel();
                        $query->toid = $FromUserName;
                        $query->type = 'TEXT';
                        $query->msg_type = 2;
                        $query->dataline = time();
                        $query->save();

                        $query2 = new ChatGptOffMsg();
                        $query2->toid = $FromUserName;
                        $query2->role = 'user';
                        $query2->msg = $Content;
                        $query2->dataline = time();
                        $query2->save();
                    }
                }

//                return DiscuzResponseFactory::XmlResponse($encrypted);
                return DiscuzResponseFactory::EmptyResponse();
            }
        }
        return DiscuzResponseFactory::HtmlResponse($echostr);
    }

    public function checkSignature(string $signature, string $timestamp, string $nonce, ?string $encrypt_msg = null): bool {
        $array = [$this->serverToken, $timestamp, $nonce];

        if ($encrypt_msg) {
            $array[] = $encrypt_msg;
        }

        sort($array, SORT_STRING);

        return sha1(implode($array)) === $signature;
    }

    public function checkSign($signature,$timestamp,$nonce) {
        if ($this->checkSignature($signature,$timestamp, $nonce)) {
            return true;
        }
        return false;
    }

    public function decode(string $text): string {
        $pad = ord(substr($text, -1));

        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }

        return substr($text, 0, (strlen($text) - $pad));
    }

    public function encode(string $text): string {
        $text_length = strlen($text);

        $amount_to_pad = $this->blockSize - ($text_length % $this->blockSize);

        if ($amount_to_pad == 0) {
            $amount_to_pad = $this->blockSize;
        }

        $pad_chr = chr($amount_to_pad);
        $tmp = '';

        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }

        return $text . $tmp;
    }

    public function decrypt(string $encrypted) {
        $iv = substr($this->aesKey, 0, 16);

        // decrypt
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $this->aesKey, OPENSSL_ZERO_PADDING, $iv);

        if (!$decrypted) {
            return -40007;
        }

        $result = $this->decode($decrypted);

        if (strlen($result) < 16) {
            return '';
        }

        $content = substr($result, 16, strlen($result));
        $lenList = unpack('N', substr($content, 0, 4));

        $lenXML = $lenList[1];

        $fromappid = substr($content, $lenXML + 4);

        if ($fromappid !== $this->appid) {
            return -40001;
        }

        return substr($content, 4, $lenXML);
    }

    public function decryptMessage(string $message, string $msg_signature, string $timestamp, string $nonce) {
        // get message
        try {
            $message = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_COMPACT + LIBXML_NOCDATA);
        } catch (Exception $e) {
            return -40002;
        }

        // get encrypt text
        $encrypt = $message->Encrypt->__toString();

        if (!$encrypt) {
            return -40002;
        }

        // check sign
        if (!$this->checkSignature($msg_signature, $timestamp, $nonce, $encrypt)) {
            return -40001;
        }

        $decrypted = $this->decrypt($encrypt);

        if (is_int($decrypted)) {
            return $decrypted;
        }

        try {
            $decrypted = simplexml_load_string($decrypted, 'SimpleXMLElement', LIBXML_COMPACT + LIBXML_NOCDATA);
            return json_decode(json_encode($decrypted),true);
        } catch (Exception $e) {
            return -40002;
        }
    }

    function getRandomStr(): string {
        $str = '';
        $str_pol = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($str_pol) - 1;

        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }

        return $str;
    }

    public function encrypt(string $text) {
        // Laravel/Lumen 中可直接生成 16 位随机字符串
        // 如非该框架请参考附录
        $random = self::getRandomStr();

        $text = $random . pack('N', strlen($text)) . $text . $this->appid;

        $text = $this->encode($text);

        $iv = substr($this->aesKey, 0, 16);

        // encrypt
        $encrypted = openssl_encrypt($text, 'AES-256-CBC', $this->aesKey, OPENSSL_ZERO_PADDING, $iv);

        return $encrypted ?: -40006;
    }

    public function generateSignature(string $encrypt_msg, string $timestamp, string $nonce): string {
        $array = [$encrypt_msg, $this->serverToken, $timestamp, $nonce];

        sort($array, SORT_STRING);

        return sha1(implode($array));
    }

    public function encryptMessage(string $reply_message, string $timestamp, string $nonce) {
        // encrypt
        $encrypted = $this->encrypt($reply_message);
        $WeChatReplyMsgCrypt = '<xml>
    <Encrypt><![CDATA[%s]]></Encrypt>
    <MsgSignature><![CDATA[%s]]></MsgSignature>
    <TimeStamp>%s</TimeStamp>
    <Nonce><![CDATA[%s]]></Nonce>
</xml>';

        if (is_int($encrypted)) {
            return $encrypted;
        }

        // $nonce 同 $timestamp 可以自己生成或直接取微信请求中的 nonce
        $signature = $this->generateSignature($encrypted, $timestamp, $nonce);

        if (!$signature) {
            return -40001;
        }

        return sprintf(
            $WeChatReplyMsgCrypt,
            $encrypted, $signature, $timestamp, $nonce
        );
    }


}
