<?php

namespace Plugin\Import\Platform;

use App\Import\PlatformTrait;

class WeiBo
{
    use PlatformTrait;

    private $cookie = '';

    /**
     * @method  主入口
     * @param sting $topic 话题
     * @param int $page  获取第几页
     * @return array
     */
    public function main($topic, $number)
    {
        set_time_limit(0);
        $page = 1;
        $data = $pageData = $this->getList($topic, $page);

        while (count($data) < $number && !empty($pageData)) {
            $page++;
            $pageData = $this->getList($topic, $page);
            $data = array_merge($data, $pageData);
        }
        if (count($data) > $number) {
            $data = array_slice($data,0, $number);
        }
        foreach ($data as $key => $value) {
            $comment = $this->getComment($value['forum']['id']);
            $data[$key]['comment'] = $comment;
        }
        return $data;
    }

    /**
     * @method  从话题搜索数据，获取到帖子数据
     * @param string $topic 话题
     * @param int $page 话题
     * @return array
     */
    private function getList($topic, $page)
    {
        //获取话题搜索页面结果
        $url = "https://m.weibo.cn/api/container/getIndex?containerid=100103type=1%3D1%26q%3D{$topic}&page_type=searchall&page={$page}";
        $html = $this->curlGet($url, $this->cookie);
        $html = json_decode($html, true);
        $data = [];
        if (isset($html['data']['cards']) && !empty($html['data']['cards'])) {
            foreach ($html['data']['cards'] as $key => $value) {
                if (!isset($value['mblog'])) {
                    continue;
                }
                //用户信息
                $value['mblog']['user']['screen_name'] =  str_replace(" ", "", $value['mblog']['user']['screen_name']);
                $forum['user'] = [
                    'avatar' => $value['mblog']['user']['avatar_hd'],//头像
                    'nickname' => $value['mblog']['user']['screen_name'],//昵称
                ];
                //帖子数据
                $text = $this->dealText($value['mblog']['text']);
                //帖子内容不全的情况下
                if(empty($text['text'])){
                    $text = $this->getForumTextDetail($value['mblog']['mid']);
                }
                //处理转发的情况
                if(isset($value['mblog']['retweeted_status']['text']) && !empty($value['mblog']['retweeted_status']['text'])){
                    $moreText = $this->getForumTextDetail($value['mblog']['retweeted_status']['mid']);
                    $text['text'] = $text['text'] . $moreText['text']; // 转发的文字内容
                    $text['topicList'] = array_merge($text['topicList'], $moreText['topicList']); // 转发内容中的话题
                }

                $forum['forum'] = [
                    'id' => $value['mblog']['id'],
                    'mid' => $value['mblog']['mid'],
                    'nickname' => $value['mblog']['user']['screen_name'],
                    'text' => $text,
                    'createdAt' => date('Y-m-d H:i:s', strtotime($value['mblog']['created_at'])),//发贴时间
                ];

                //图片和视频只有一种
                //判断是否帖子中包含图片
                $smallPics = [];
                if (isset($value['mblog']['pics']) && !empty($value['mblog']['pics'])) {
                    //取出图片
                    foreach ($value['mblog']['pics'] as $valuePic) {
                        $smallPics[] = $valuePic['url'];
                    }
                }
                // 转发内容的图片
                if (isset($value['mblog']['retweeted_status']['pics']) && !empty($value['mblog']['retweeted_status']['pics'])) {
                    foreach ($value['mblog']['retweeted_status']['pics'] as $valuePic) {
                        $smallPics[] = $valuePic['url'];
                    }
                }
                $forum['forum']['images'] = $smallPics;
                //判断是否帖子中包含视频
                $smallMedias = [];
                if (isset($value['mblog']['page_info']) && !empty($value['mblog']['page_info'])) {
                    if (strtolower($value['mblog']['page_info']['type']) == 'video') {
                        $smallMedias = $value['mblog']['page_info']['media_info'];
                    }
                }
                // 转发微博中的视频
                if (isset($value['mblog']['retweeted_status']['page_info']) && !empty($value['mblog']['retweeted_status']['page_info'])) {
                    if (strtolower($value['mblog']['retweeted_status']['page_info']['type']) == 'video' && empty($smallMedias)) {
                        $smallMedias = $value['mblog']['retweeted_status']['page_info']['media_info'];
                    }
                }

                $forum['forum']['media']['video'] = $smallMedias['stream_url'] ?? '';
                $data[] = $forum;
            }
        }
        return $data;
    }

    /**
     * @method  爬取话题搜索结果页面mid
     * @param string $topic 话题
     * @return array
     */
    private function getMid($topic)
    {
        //获取话题搜索页面结果
        $url = "https://s.weibo.com/weibo/%23{$topic}%23";
        $html = $this->curlGet($url,  $this->cookie);
        //从话题页面提取出mid
        //<div class=\"card-wrap\" action-type=\"feed_list_item\" mid=\"(.*)\" >
        preg_match_all("/<div class=\"card-wrap\" action-type=\"feed_list_item\" mid=\"(.*)\" >/i", strtolower($html), $matches);
        return $matches[1];
    }

    /**
     * @method  根据mid获取帖子基本信息
     * @param string $mid mid
     * @return array
     */
    private function getForumDetail($mid)
    {
        $forumUrl = "https://m.weibo.cn/detail/{$mid}";
        $html = $this->curlGet($forumUrl,  $this->cookie);
        //获取js标签之间的数据
        $start = strpos($html, '[{');
        $end = strpos($html, '}]');
        $result = substr($html, $start, $end - $start);
        $result = $result . '}]';
        $result = json_decode($result, true);
        if (empty($result) || !is_array($result)) {
            return false;
        }
        //用户信息
        $forum['user'] = [
            'avatar' => $result[0]['status']['user']['avatar_hd'],//头像
            'nickname' => str_replace(" ", "", $result[0]['status']['user']['screen_name']),//昵称
            'gender' => $result[0]['status']['user']['gender'],//性别
            'home_page' => $result[0]['status']['user']['profile_url'],//个人主页
            'description' => $result[0]['status']['user']['description'],//描述
            'followers_count' => $result[0]['status']['user']['followers_count'],//粉丝
            'follow_count' => $result[0]['status']['user']['follow_count'],//关注
        ];
        //发帖信息
        $text = $this->dealText($result[0]['status']['text']);

        $forum['forum'] = [
            'text' => $text,//发帖内容
            'createdAt' => date('Y-m-d H:i:s', strtotime($result[0]['status']['created_at'])),//发贴时间
        ];
        //图片和视频只有一种
        //判断是否帖子中包含图片
        $smallPics = [];
        if (isset($result[0]['status']['pics']) && !empty($result[0]['status']['pics'])) {
            //取出图片
            foreach ($result[0]['status']['pics'] as $valuePic) {
                $smallPics[] = $valuePic['url'];
            }
        }
        $forum['forum']['images'] = $smallPics;
        //判断是否帖子中包含视频
        $smallMedias = [];
        if (isset($result[0]['status']['page_info'])) {
            if (strtolower($result[0]['status']['page_info']['type']) == 'video') {
                $smallMedias = $result[0]['status']['page_info']['media_info'];
            }
        }
        $forum['forum']['media']['video'] = $smallMedias;

        return $forum;
    }

    /**
     * @method  根据mid获取帖子内容信息【适用于内容显示不全的情况】
     * @param string $mid mid
     * @return string
     */
    private function getForumTextDetail($mid)
    {
        $forumUrl = "https://m.weibo.cn/detail/{$mid}";
        $html = $this->curlGet($forumUrl,  $this->cookie);
        //获取js标签之间的数据
        $start = strpos($html, '[{');
        $end = strpos($html, '}]');
        $result = substr($html, $start, $end - $start);
        $result = $result . '}]';
        $result = json_decode($result, true);
        if (empty($result) || !is_array($result)) {
            return false;
        }
        $text = $result[0]['status']['text'];
        //处理转发的情况
        if(isset($result[0]['status']['retweeted_status']['text']) && !empty($result[0]['status']['retweeted_status']['text'])){
            $text = $text . $result[0]['status']['retweeted_status']['text'];
        }
        //发帖信息
        return $this->dealText($text);
    }

    /**
     * @method  根据mid获取帖子评论信息
     * @param string $mid mid
     * @return array
     */
    private function getComment($mid)
    {
        $commentUrl = "https://m.weibo.cn/comments/hotflow?id={$mid}&mid={$mid}&max_id_type=0";
        $html = $this->curlGet($commentUrl,  $this->cookie);
        $html = json_decode($html, true);
        $comment = [];
        if (isset($html['data']['data']) || !empty($html['data']['data'])) {
            foreach ($html['data']['data'] as $key => $value) {
                //评论信息
                $value['user']['screen_name'] = str_replace(" ", "", $value['user']['screen_name']);
                $comment[$key]['comment'] = [
                    'id' => $value['id'],//评论ID
                    'rootid' => $value['rootid'],//评论ID
                    'forumId' => $mid, // 帖子ID
                    'nickname' => $value['user']['screen_name'],//昵称
                    'createdAt' => date('Y-m-d H:i:s', strtotime($value['created_at'])),//评论时间
                    'text' => $this->dealText($value['text']),//评论内容
                ];
                //评论用户信息
                $comment[$key]['user'] = [
                    'avatar' => $value['user']['avatar_hd'],//头像
                    'nickname' => $value['user']['screen_name'],//昵称
                ];

            }
        }
        return $comment;
    }

    /**
     * @method  处理帖子内容格式
     * @param string $text 文本内容
     * @return array
     */
    private function dealText($text)
    {
        //发帖信息
        $text = strip_tags($text,'<p><br><img>');   //保留<p><br><img>
        //判断是否完成,以最后两位是否为全文进行判断
        if (mb_substr($text, -2) == '全文') {
            return ['text' => ''];
        }
        //去除@某人数据
        if(strpos($text,'@') != false){
            $text = preg_replace('/@(.*?) /is','',$text);
        }
        //位置，根据定位图标处理
        $position = '';
        if(strpos($text,'timeline_card_small_location_default.png') != false) {
            //特殊处理，内容末尾出现定位，拼接一个空格
            $text = $text.' ';
            $text = str_replace("'","\"",$text);
            preg_match_all('/timeline_card_small_location_default.png\">(.*?) /is', $text, $position);
            if (isset($position[1][0]) && !empty($position[1][0])) {
                $position = strip_tags($position[1][0]);
            }
        }
        //去掉文案后面的视频链接文案
//        if(strpos($text,'timeline_card_small_video_default.png') != false) {
//            //特殊处理，内容末尾出现定位，拼接一个空格
//            $text = $text.' ';
//            $text = str_replace("'","\"",$text);
//            $text = preg_replace('/timeline_card_small_video_default.png\">(.*?) /is','timeline_card_small_video_default.png">',$text);
//        }

        /*        preg_match_all('/<img.*?src="(.*?)".*?>/is',$text,$images);*/
//        if(isset($images[1]) && !empty($images[1])) {
//            //下载图片或者视频
//            foreach ($images[1] as $imageValue) {
//                $imageUrl = $this->downloadFile($imageValue);
//                //替换为本地上传的图片
//                $text = str_replace($imageValue,$imageUrl,$text);
//            }
//        }

        //话题提取
        preg_match_all('/#(.*?)#/is',$text,$topics);

        // 删除表情和小图标的img
        $text = preg_replace('/(<img.*?)(style=.+?[\'|"])|((width)=[\'"]+[0-9]+[\'"]+)|((height)=[\'"]+[0-9]+[\'"]+)/i', '$1', $text);
        preg_match_all("/<img[^>]+/", $text, $imagesSrc);
        if (!empty($imagesSrc) && !empty($imagesSrc[0])) {
            foreach ($imagesSrc[0] as $imageSrc) {
                if (strstr($imageSrc, 'emoticon') || strstr($imageSrc, 'timeline_card_small')) {
                    $text = str_replace($imageSrc . '>', '', $text);
                }
            }
        }

        return [
            'text' => $text,
            'position' => $position,
            'topicList' => $topics[1]
        ];
    }
}
