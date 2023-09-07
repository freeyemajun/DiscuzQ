<?php

namespace Plugin\Import\Platform;

use Carbon\Carbon;
use App\Import\PlatformTrait;

class OfficialAccountArticle
{
    use PlatformTrait;

    //微信内容div正则
    private $wxContentDiv = '/<div class="rich_media_content " id="js_content" style="visibility: hidden;">(.*?)<\/div>/s';

    public function main($articleUrl)
    {
        if (empty($articleUrl)) {
            $this->deleteImportLockFile();
            throw new \Exception('缺少文章链接');
        }

        $articleUrl = explode(',', $articleUrl);
        $data = [];
        foreach ($articleUrl as $url) {
            $data[] = $this->getArticle($url);
        }

        return $data;
    }

    private function getArticle($url)
    {
        $data = [
            'user' => [
                'avatar' => ''
            ],
            'forum' => [
                'text' => [
                    'position' => '',
                    'topicList' => []
                ],
                'images' => [],
                'media' => [
                    'video' => '',
                    'audio' => ''
                ],
                'contentMedia' => [
                    'videos' => [],
                    'audio' => []
                ],
                'attachments' => []
            ],
            'comment' => []
        ];

        $urlContents = $this->getUrlContent($url);
        $articleBasicInfo = $this->getArticleBasicInfo($urlContents);

        $data['user']['nickname'] = str_replace(' ', '', $articleBasicInfo['wechatName']);
        $data['forum']['createdAt'] = $articleBasicInfo['createdAt'] ? date('Y-m-d H:i:s', $articleBasicInfo['createdAt']) : Carbon::now();
        $data['forum']['text']['title'] = str_replace("'", "", $articleBasicInfo['title']);;

        $contentUrl = str_replace('#rd', '', $articleBasicInfo['contentUrl']);
        $content = $this->getContent($contentUrl);
        $content = $this->getArticleContent($content);
        [$data, $content] = $this->changeArticleVideo($contentUrl, $content, $data);
        $content = str_replace('data-src=', 'src=', $content);
        $content = $this->changeImg($content);

        $data['forum']['text']['text'] = $content;

        return $data;
    }

    private function getArticleContent($urlContents)
    {
        $content_html_pattern = $this->wxContentDiv;
        preg_match_all($content_html_pattern, $urlContents, $html_matchs);
        if(empty(array_filter($html_matchs))) {
            return '未获取到相关内容';
        }
        $content = $html_matchs[0][0];
        //去除掉hidden隐藏
        $content = str_replace('style="visibility: hidden;"','', $content);
        $content = preg_replace("/<(\/?mpprofile.*?)>/si",'', $content);
        $content = preg_replace("/<(\/?svg.*?)>/si",'', $content);
        $content = preg_replace("/<(\/?g.*?)>/si",'', $content);
        $content = preg_replace("/<(\/?path.*?)>/si",'', $content);
        $content = preg_replace("/<(\/?figure.*?)>/si",'', $content);
        $content = preg_replace("/<(\/?mpvideosnap.*?)>/si",'', $content); //  过滤视频号
        $content = preg_replace("/<(\/?mp-miniprogram.*?)>/si",'', $content); //  过滤小程序
        $content = preg_replace("/<(\/?qqmusic.*?)>/si",'', $content); //  过滤qq音乐
        //添加微信样式
        $content = '<div style="max-width: 677px;margin-left: auto;margin-right: auto;">' . $content . '</div>';

        return $content;
    }

    private function changeArticleVideo($url, $content, $data)
    {
        preg_match_all('/<iframe[^>]*\s+data-src="([^"]*)"[^>]*>[^>]*<\/iframe>/isU', $content, $videoIframeSrc);
        preg_match_all('/<mpvoice[^>]*\s+voice_encode_fileid="([^"]*)"[^>]*>[^>]*<\/mpvoice>/isU', $content, $voiceIframeSrc);

        if (empty($videoIframeSrc[0]) && empty($voiceIframeSrc[0])) {
            return [$data, $content];
        }

        $resources = $this->getWxArticleMaterial($url);
        if (empty($resources['video']) && empty($resources['voice'])) {
            return $content;
        }

        if (!empty($resources['video'])) {
            $data['forum']['contentMedia']['videos'] = array_column($resources['video'], 'url');
            $videoIdArray = array_column($resources['video'],null, 'vid');
            foreach ($videoIframeSrc[1] as $key => $value) {
                if (strpos($value, "vid=") !== false) {
                    $urlParameter = explode('&', $value);
                    foreach ($urlParameter as $parameter) {
                        if (strpos($parameter, "vid=") !== false) {
                            $vid = substr($parameter,strrpos($parameter,'vid=') + 4, strlen($parameter));
                            if (isset($videoIdArray[$vid])) {
                                $iframeString = '<iframe src="' . $videoIdArray[$vid]['url'] . '"></iframe>';
                                $content = str_replace($videoIframeSrc[0][$key], $iframeString, $content);
                                continue;
                            }
                        }
                    }
                }
            }
        }
        if (!empty($resources['voice'])) {
            $data['forum']['contentMedia']['audio'] = array_column($resources['voice'], 'url');
            $voiceIdArray = array_column($resources['voice'],null, 'vid');
            foreach ($voiceIframeSrc[1] as $key => $value) {
                if (isset($voiceIdArray[$value])) {
                    $iframeString = '<iframe src="' . $voiceIdArray[$value]['url'] . '"></iframe>';
                    $content = str_replace($voiceIframeSrc[0][$key], $iframeString, $content);
                }
            }
        }

        return [$data, $content];
    }

    private function changeImg($content)
    {
        preg_match_all('/<img[^>]*\s+src="([^"]*)"[^>]*>/isU', $content, $imageSrcMatches);
        if (!empty($imageSrcMatches[1])) {
            foreach ($imageSrcMatches[1] as $key => $value) {
                $newImg = '<img src="' . $value . '"/>';
                $content = str_replace($imageSrcMatches[0][$key], $newImg, $content);
            }
        }
        return $content;
    }

    private function getUrlContent($url)
    {
        return  \Discuz\Common\Utils::downLoadFile($url);
    }

    private function getContent($url)
    {
        $fileContents = @file_get_contents($url, false, stream_context_set_default(['ssl' => ['verify_peer'=>false, 'verify_peer_name'=>false]]));
        return $fileContents;
    }
    private function getArticleBasicInfo($urlContents)
    {
        $item = [
            'ct' => 'createdAt', //发布时间
            'msg_title' => 'title', //标题
            'msg_link' => 'contentUrl', //文章链接
            'nickname' => 'wechatName', //公众号名称
        ];
        $basicInfo = [];
        foreach ($item as $k => $v) {
            if($k == 'msg_title'){
                $pattern = '/ var '.$k.' = (.*?)\.html\(false\);/s';
            } else {
                $pattern = '/ var ' . $k . ' = "(.*?)";/s';
            }
            preg_match_all($pattern, $urlContents, $matches);
            if(array_key_exists(1, $matches) && !empty($matches[1][0])){
                $basicInfo[$v] = $this->transformHtml($matches[1][0]);
            }else{
                $basicInfo[$v] = '';
            }
        }

        return $basicInfo;
    }

    private function transformHtml($string)
    {
        $string = str_replace('&quot;','"',$string);
        $string = str_replace('&amp;','&',$string);
        $string = str_replace('amp;','',$string);
        $string = str_replace('&lt;','<',$string);
        $string = str_replace('&gt;','>',$string);
        $string = str_replace('&nbsp;',' ',$string);
        $string = str_replace("\\", '',$string);
        return $string;
    }

    private function getWxArticleMaterial($url) {
        if (empty($url)) {
            return false;
        }
        $info_id_arr = $this->getChatInfoId($url);
        //获取真实地址链接
        $info_arr = [];
        foreach ($info_id_arr as $key => $value){
            //获取视频
            switch ($key){
                case 'video':
                    $info_arr['video'] = [];
                    if(!empty($value)){
                        foreach ($value as $vid){
                            $video_json = $this->getVideoInfo($vid);
                            if(!empty($video_json['msg']) && $video_json['msg'] == 'vid is wrong'){
                                //微视
                                $return = $this->getWsUrl($vid);
                            }else{
                                //腾讯
                                $return = $this->getTxUrl($video_json);
                            }
                            $info_arr['video'][] = $return;
                        };
                    }
                    break;
                case 'voice':
                    $info_arr['voice'] = [];
                    if(!empty($value)){
                        foreach ($value as $vid){
                            $return = $this->getVoiceInfo($vid);
                            $info_arr['voice'][] = $return;
                        };
                    }
                    break;
                default:
                    break;
            }
        }
        return $info_arr;
    }

    /**
     * 获取公众号中的资源  音频和视频
     * @param $url
     * @return array
     */
    public function getChatInfoId($url){
        //微信的链接有长链和短链，以下为长链
        //$url ='http://mp.weixin.qq.com/s?__biz=MzI0NTc1MTczNA==&mid=2247485130&idx=1&sn=945cfb8b0cfdd99f1b730889de0216e2&chksm=e9488c13de3f05057be6c6b065f8e44d43c566cb9ee3a4f35cf8084382742159181ea480b935&scene=27';
        if (stripos($url, '?')) {
            if (stripos($url, '#wechat_redirect')) {
                $url = str_replace('#wechat_redirect', '', $url);
            }
            $json = $url . '&f=json';
        } else {
            $json = $url . '?f=json';
        }

        $data = $this->getCurlRequest($json);
        $data = json_decode($data, 1);
        $chat_info_id = [];
        //获取json中的得到视频vid
        $vid_arr = $data['video_ids'] ?? [];
        //获取json中的得到音频的mid
        if (isset($data['voice_in_appmsg'])) {
            $voice_arr = array_column($data['voice_in_appmsg'],'voice_id');
        } else {
            $voice_arr = [];
        }

        $chat_info_id['video'] = $vid_arr;
        $chat_info_id['voice'] = $voice_arr;

        if(empty($vid_arr)){
            //data 为文章的详情
            $html = $data['content_noencode'];
            preg_match_all('/<iframe (.*?)data-src="(.*?)">/', $html, $matchs);
            //没有视频脚本退出
            if (empty($matchs[2])) {
                // echo '没有视频匹配到，不采集';
                return $chat_info_id;
            }
            //判断是否是url地址  而后解析得出 vid的值
            $url = current($matchs[2]);
            if(!filter_var($url,FILTER_VALIDATE_URL)){
                // echo '视频地址异常：'.$url;
                return $chat_info_id;
            }
            $url = str_replace('&amp;','&',$url);
            //https://v.qq.com/iframe/preview.html?vid=i1324786hv8&width=500&height=375&auto=0
            $url_arr = parse_url($url);
            $query = $url_arr['query'] ?? '';
            $vidArray = explode("&vid=",$query);
            //获取到vid
            $vid_arr = [$vidArray[1]] ?? '';
            if(empty($vid_arr)){
                // echo '视频参数异常：'.$query;
                return $chat_info_id;
            }
        }
        $chat_info_id['video'] = $vid_arr;
        return $chat_info_id;
    }

    /**
     * 腾讯微视获取真实地址
     */
    private function getWsUrl($vid){
        $url = 'https://mp.weixin.qq.com/mp/videoplayer?action=get_mp_video_play_url&vid='.$vid;
        $data = $this->getCurlRequest($url);
        $data = json_decode($data,1);
        //得到数据的json 组装成功url
        $format_id = $data['url_info'][0]['format_id'] ?? '';
        $title = $data['title'];
        if (isset($data['url_info'][0]['url'])) {
            $url = $data['url_info'][0]['url']."&vid=$vid&format_id=$format_id";
        } else {
            $url = '';
        }

        return [
            'vid' => $vid,
            'type' => '公众号素材视频',
            'title' => $title,
            'url' => $url
        ];
    }


    /**
     * 腾讯视频的处理url
     * @param array $video_json 腾讯视频数据
     * @return array
     */
    private function getTxUrl(array $video_json){
        $title = $video_json['vl']['vi'][0]['ti'];
        $vid = $video_json['vl']['vi'][0]['vid'];
        //高质量视频
        $fn_pre = $video_json['vl']['vi'][0]['lnk'];
        $host = $video_json['vl']['vi'][0]['ul']['ui'][0]['url'];
        $streams = $video_json['fl']['fi'];
        $seg_cnt = $video_json['vl']['vi'][0]['cl']['fc'];
        $best_quality = end($streams)['name'];
        $part_format_id = end($streams)['id'];
        $part_urls = [];
        for ($part = 1; $part <= $seg_cnt + 1; $part++) {
            $filename = $fn_pre . '.p' . ($part_format_id % 10000) . '.' . $part . '.mp4';
            $key_api = "http://vv.video.qq.com/getkey?otype=json&platform=11&format="
                . $part_format_id . "&vid=" . $vid . "&filename=" . $filename . "&appver=3.2.19.333";
            $part_info = $this->curl($key_api);
            preg_match('/QZOutputJson=(.*);$/Uis', $part_info, $key_json);
            $key_json = json_decode($key_json[1], 1);
            if (empty($key_json['key'])) {
                $vkey = $video_json['vl']['vi'][0]['fvkey'];
                $url = $video_json['vl']['vi'][0]['ul']['ui'][0]['url'] . $fn_pre . '.mp4?vkey=' . $vkey;
            } else {
                $vkey = $key_json['key'];
                $url = $host . $filename . "?vkey=" . $vkey;
            }
            $part_urls[] = $url;
        }
        //真实的地址
        if (empty($part_urls)) {
            //获取的视频质量低
            if (!empty($video_json['vl']['vi'])) {
                $keys = [];
                foreach ($video_json['vl']['vi'] as $key => $value) {
                    $fvkey = $value['fvkey'];
                    $fn = $value['fn'];
                    $self_host = $value['ul']['ui'][$key]['url'];
                    $keys['fvkey'] = $fvkey;
                    $keys['fn'] = $fn;
                    $keys['self_host'] = $self_host;
                    $keys['lnk'] = $value['lnk'];
                }
                $part_urls[0] = $keys['self_host'] . $keys['fn'] . '?vkey=' . $keys['fvkey'];
            }
        }
        return [
            'vid' => $vid,
            'type' => '腾讯视频',
            'title' => $title,
            'url' => current($part_urls)
        ];
    }


    /**
     * 获取音频真实地址
     * @param string $vid
     */
    private function getVoiceInfo(string $vid){
        $url = 'https://res.wx.qq.com/voice/getvoice?mediaid='.$vid;
        return [
            'vid' => $vid,
            'type' => '音频资料',
            'url' => $url
        ];
    }

    private function getCurlRequest($url, $post = '',$header = [], $cookie = '', $returnCookie = 0) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false );
        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($header){
            curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
        }
        if ($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if ($returnCookie) {
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie'] = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        } else {
            return $data;
        }
    }

    private function getVideoInfo($vid) {
        $infourl = 'https://vv.video.qq.com/getinfo?otype=json&appver=3.2.19.333&platform=11&defnpayver=1&vid=' . $vid;
        $data = self::curl($infourl);
        preg_match('/QZOutputJson=(.*);$/Uis', $data, $info);
        return json_decode($info[1], 1);
    }

    private function curl($url, $option = [], $cookie = '') {
        $split = explode('/', $url);
        $cookiejar = str_replace('\\', '/', dirname(__FILE__)) . '/' . $split[2] . '.cookie';
        $ch = curl_init();
        $options = $option?$option:[
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "Accept-Charset: UTF-8,*;q=0.5",
                "Accept-Encoding': 'gzip,deflate,sdch",
                "Accept-Language': 'en-US,en;q=0.8",
                "User-Agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:51.0) Gecko/20100101 Firefox/51.0",
                "Cookie: '". $cookie ."'"
            ],
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_COOKIEJAR => $cookiejar,
            CURLOPT_COOKIEFILE => $cookiejar,
        ];
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}