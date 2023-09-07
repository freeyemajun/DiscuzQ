<?php

namespace Plugin\Import\Platform;

use App\Import\PlatformTrait;

class TieBa
{
    use PlatformTrait;

    private $cookie = '';

    /**
     * @method  主入口
     * @param string $topic 话题
     * @param int $page 获取第几页数据
     * @param string $cookie 用户cookie
     * @return array
     */
    public function main($topic, $number, $cookie = '')
    {
        set_time_limit(0);
        $this->cookie = $cookie;
        $page = 1;
        $data = $pageData = $this->getListWap($topic, $page);
        while (count($data) < $number && !empty($pageData)) {
            $page++;
            $pageData = $this->getListWap($topic, $page);
            $data = array_merge($data, $pageData);
        }
        if (count($data) > $number) {
            $data = array_slice($data,0, $number);
        }

        return $data;
    }

    /**
     * @method  从话题搜索数据，获取到帖子数据[从WAP端获取数据]
     * @param string $topic 话题
     * @param int $page 分页
     * @return array
     */
    private function getListWap($topic, $page = 1)
    {
        $data = [];
        $num = 30;
        $page = ($page - 1) * $num;
        $url = "https://tieba.baidu.com/mo/q/m?word={$topic}&page_from_search=index&tn6=bdISP&tn4=bdKSW&tn7=bdPSB&lm=16842752&lp=6093&sub4=进吧&pn={$page}&";
        $html = $this->curlGet($url, $this->cookie);
        if (empty($html)) {
            return $data;
        }
        $forumList = explode('<div class="ti_infos clearfix" data-tid="">', $html);
        if (!isset($forumList[1])) {
            return $data;
        }
        unset($forumList[0]); //删除头部信息
        if ($forumList) {
            $data = [];
            //遍历
            foreach ($forumList as $k => $v) {
                $content = explode('<span class="ti_time">', $v);
                //用户信息
                $user = $this->getUserWap($content[0]);
                //如果用户信息不能抓取,跳过
                if (empty($user['nickname'])) {
                    continue;
                }
                //帖子信息
                $forum = $this->getForumDetailWap('<span class="ti_time">' . $content[1]);
                //如果帖子信息不能抓取，跳过
                if (empty($forum['id'])) {
                    continue;
                }
                $user['nickname'] = str_replace(" ", "", $user['nickname']);
                $data[$k]['user'] = $user;
                $data[$k]['forum'] = $forum;
                //评论信息
                $data[$k]['comment'] = $this->getCommentList($forum['id']);
            }
        }
        return $data;
    }

    /**
     * @method  获取用户信息, 从html页面中提取用户数据[从WAP端获取数据]
     * @param string $content 内容
     * @return array
     */
    private function getUserWap($content)
    {
        //返回参数
        $userInfo = [
            'avatar' => '',
            'nickname' => ''
        ];
        if ($content) {
            //头像
            $avatar = $this->dealMatchStr("/<img src=\"(.*)\" alt=\"\">/i", $content);
            $userInfo['avatar'] = trim($avatar);
            if (strstr($userInfo['avatar'], '?')) {
                $userInfo['avatar'] = substr($userInfo['avatar'], 0, strpos($userInfo['avatar'], '?'));
                $userInfo['avatar'] = $userInfo['avatar'] . '.jpg';
            }

            //昵称
            preg_match_all("/<span class=\"ti_author(.*)<\/span> /i", $content, $nicknameMatches);
            if (isset($nicknameMatches[0][0]) && !empty($nicknameMatches[0][0])) {
                $userInfo['nickname'] = trim(strip_tags($nicknameMatches[0][0]));
            }
        }
        return $userInfo;
    }

    /**
     * @method  获取帖子基本信息
     * @param string $content 内容
     * @return array
     */
    private function getForumDetailWap($content)
    {
        //返回参数
        $forumData = [
            'id' => '',
            'text' => [
                'text' => '',
                'position' => '',
                'topicList' => []
            ],
            'createdAt' => '',
            'images' => [],
            'media' => [
                'video' => [],
                'audio' => [],
            ]
        ];
        if ($content) {
            //id
            $id = $this->dealMatchStr("/<a href=\"\/p\/(.*)\?lp=/i", $content);
            $forumData['id'] = trim($id);

            //帖子内容
            $text = $this->dealMatchStr("/<div class=\"ti_title\">(.*)<\/span>(?)<\/div> *<div/i", $content);
            preg_match_all('/#(.*?)#/is',$content,$topics);
            $forumData['text']['text'] = trim(strip_tags($text, '<p><br><img>'));
            $forumData['text']['topicList'] = $topics[1];

            //发帖时间
            $createAt = $this->dealMatchStr("/<span class=\"ti_time\">(.*)<\/span><\/div> *<\/div><a/i", $content);
            $createAt = trim($createAt);
            //判断是否为当天时间
            if (strpos($createAt, ':') != false) {
                $createAt = date('Y-m-d ') . $createAt;
            }
            $forumData['createdAt'] = $createAt;

            //图片
            //判断是否包含图片
            $localtion = strpos($content, 'medias_wrap ordinary_thread clearfix');
            if ($localtion != false) {
                $picsHtml = substr($content, $localtion);
                preg_match_all("/[img|IMG].*?url=['|\"](.*?(?:[.gif|.jpg]))['|\"].*?[\/]?>/", $picsHtml, $pics);
                foreach ($pics[1] as $picKey => $picValue) {
                    if (strpos($picValue, '.jpg') == false && strpos($picValue, '.gif') == false) {
                        unset($pics[1][$picKey]);
                    }
                }
                $forumData['images'] = $pics[1];
            }
        }
        return $forumData;
    }

    /**
     * @method  根据mid获取评论信息
     * @param string $mid mid
     * @return array
     */
    private function getCommentList($mid)
    {
        $data = [];
        $url = "https://tieba.baidu.com/p/{$mid}";
        $html = $this->curlGet($url, $this->cookie);
        if (empty($html)) {
            return $data;
        }

        $forum = explode('<ul class="p_author">', $html);
        unset($forum[0]); //删除头部信息
        foreach ($forum as $key => $value) {
            //将评论信息和用户信息切分
            $content = explode('<li class="d_nameplate">', $value);
            //用户信息
            $user = $this->getUser($content[0]);
            //如果用户昵称为空，抛弃
            if (empty($user['nickname'])) {
                continue;
            }

            //评论信息
            $commentDetail = $this->getComment($content[1]);
            //如果评论ID为空，抛弃
            if (empty($commentDetail['id']) || (empty($commentDetail['text']['text']) && empty($commentDetail['images']))) {
                continue;
            }
            //用户信息
            $user['nickname'] = str_replace(" ", "", $user['nickname']);
            $data[$key]['user'] = $user;
            //评论信息
            $data[$key]['comment'] = $commentDetail;
        }
        return $data;
    }

    /**
     * @method  获取用户信息, 从html页面中提取用户数据
     * @param string $content 内容
     * @return array
     */
    private function getUser($content)
    {
        //返回数据
        $userInfo = [
            'avatar' => '',
            'nickname' => ''
        ];
        if ($content) {
            //头像
            $avatar = $this->dealMatchStr("/\" class=\"\" src=\"(.*)\"\/>/i", $content);
            //判断是否有头像
            if (strpos($avatar, 'https://')) {
                $avatar = substr($avatar, strpos($avatar, 'https://'));
            }
            $userInfo['avatar'] = trim($avatar);
            if (strstr($userInfo['avatar'], '?')) {
                $userInfo['avatar'] = substr($userInfo['avatar'], 0, strpos($userInfo['avatar'], '?'));
                $userInfo['avatar'] = $userInfo['avatar'] . '.jpg';
            }

            //昵称
            $nickname = $this->dealMatchStr("/img username=\"(.*)\" class=\"\"/i", $content);
            $userInfo['nickname'] = trim($nickname);
        }
        return $userInfo;
    }

    /**
     * @method  获取评论信息
     * @param string $content 内容
     * @return array
     */
    private function getComment($content)
    {
        $commentInfo = [
            'id' => '',
            'createdAt' => '',
            'text' => [
                'text' => ''
            ],
        ];
        //评论ID
        $commentInfo['id'] = $this->dealMatchStr("/<div id=\"post_content_(.*)\" class=\"d_post_content j_d_post_content/i", $content);

        //评论内容
        $commentText = $this->dealMatchStr("/class=\"d_post_content j_d_post_content \" style=\"display:;\">(.*)<\/div><br>/i", $content);
        $imageMatches = $this->getHtmlLabel('/<img[^>]*\s+src="([^"]*)"[^>]*>/isU', $commentText);
        $commentInfo['images'] = $imageMatches[1] ?? [];
        $commentInfo['text']['text'] = trim(strip_tags($commentText, '<p><br><img>'));

        //评论时间
        $commentInfo['createdAt'] = $this->dealMatchStr("/楼<\/span><span class=\"tail-info\">(.*)<\/span><\/div><ul class=\"p_props_tail props_appraise_wrap/i", $content) ?: date('Y-m-d H:i:s', time());
        return $commentInfo;
    }

    /**
     * @method  处理单个正则匹配，返回结果
     * @param string $match 正则
     * @param string $content 内容
     * @return string
     */
    private function dealMatchStr($match, $content)
    {
        $result = '';
        if ($content) {
            //评论ID
            preg_match_all($match, $content, $matches);
            if (isset($matches[1][0]) && !empty($matches[1][0])) {
                $result = $matches[1][0];
            }
        }
        return $result;
    }
}
