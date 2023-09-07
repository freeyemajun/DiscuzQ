<?php

namespace Plugin\Import\Platform;

use App\Import\PlatformTrait;

class DouBan
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
        $data = $pageData = $this->getList($topic, $page);
        while (count($data) < $number && !empty($pageData)) {
            $page++;
            $pageData = $this->getList($topic, $page);
            $data = array_merge($data, $pageData);
        }
        if (count($data) > $number) {
            $data = array_slice($data,0, $number);
        }

        return $data;
    }


    /**
     * @method  从话题搜索数据，获取到帖子列表
     * @param string $topic 话题
     * @param int $page 分页
     * @return array
     */
    private function getList($topic, $page = 1)
    {
        $data = [];
        $num = 50;
        $page = ($page - 1) * $num;
        $url = "https://www.douban.com/group/search?start={$page}&cat=1013&q={$topic}&sort=relevance";
        $html = $this->curlGet($url, $this->cookie);
        if (empty($html)) {
            return $data;
        }
        if (strpos($html, "登录跳转") !== false) {
            $this->deleteImportLockFile();
            throw new \Exception('请求次数过多，强制登录跳转，无法获取数据');
        }

        $forumList = explode('<tr class="pl">', $html);
        if (!isset($forumList[1])) {
            return $data;
        }
        unset($forumList[0]); //删除头部信息
        if ($forumList) {
            $data = [];
            //遍历
            foreach ($forumList as $k => $v) {
                //帖子链接
                $info = $this->dealMatchStr("/<td class=\"td-subject\">(.*)<\/td>/i", $v);
                //链接
                $link = $this->dealMatchStr('/<a[^>]+href=["\'](.*?)["\']/i', $info);
                if (empty($link)) {
                    continue;
                }
                //提取帖子ID
                $id = intval(substr($link, strpos($link, 'topic/') + 6));
                //标题
                $title = $this->dealMatchStr('/<a[^>]+title=["\'](.*?)["\']/i', $info);
                //发帖时间
                $createdAt = $this->dealMatchStr('/<td class="td-time" title="(.*)" nowrap="nowrap">/i', $v);

                //获取帖子基本信息及评论信息
                $result = $this->getForumDetail($link, $id);
                if (isset($result['code'])) {
                    continue;
                }
                if (empty($result['user']['nickname'])) {
                    continue;
                }
                //组装数据
                //发帖人信息
                $result['user']['nickname'] = str_replace(" ", "", $result['user']['nickname']);
                $data[$k]['user'] = $result['user'];
                //帖子信息
                $data[$k]['forum'] = [
                    'id' => $id,
                    'mid' => $id,
                    'nickname' => $result['user']['nickname'],
                    'text' => [
                        'text' => $title . $result['forum'],
                        'position' => '',
                        'topicList' => '',
                    ],
                    'createdAt' => $createdAt,
                    'images' => [],
                    'media' => [
                        'videos' => [],
                        'audio'  => []
                    ]
                ];
                //评论信息
                $data[$k]['comment'] = $result['comment'];
            }
        }
        return $data;
    }

    /**
     * @method  获取帖子详情及评论
     * @param string $url 链接
     * @param string $mid 帖子ID
     * @return array
     */
    private function getForumDetail($url, $mid)
    {
        $data = ['code' => 0, 'msg' => 'no data！'];
        if (empty($url)) {
            return $data;
        }
        $html = $this->curlGet($url, $this->cookie);
        if (empty($html)) {
            return $data;
        }
        $data = [];
        //切分
        $content = explode('<li class="clearfix comment-item reply-item "', $html);
        //提取帖子内容
        $forumInfo = $this->getForumInfo($content[0]);
        $data['user'] = $forumInfo['user'];
        $data['forum'] = $forumInfo['forum'];

        //提取帖子评论信息
        unset($content[0]);
        if (empty($content)) {
            $comment = [];
        } else {
            $comment = $this->getCommentList($content, $mid);
        }
        $data['comment'] = $comment;

        return $data;
    }

    /**
     * @method  获取发帖人信息和帖子内容
     * @param string $content 内容
     * @return array
     */
    private function getForumInfo($content)
    {
        //定义返回参数
        $forumInfo = [
            'user' => [
                'avatar' => '',
                'nickname' => ''
            ],
            'forum' => '',
        ];
        if (empty($content)) {
            return $forumInfo;
        }
        $content = substr($content, strpos($content, '<div class="topic-content clearfix" id="topic-content">'));
        //发帖人信息
        $user = $this->getUser($content);
        $forumInfo['user'] = $user;

        //帖子内容信息
        $forumInfo['forum'] = strip_tags(trim($this->dealMatchStr("/<div class=\"rich-content topic-richtext\">\s(.*?)\s<\/div>/ism", $content)), '<p><br><img>');

        return $forumInfo;
    }


    /**
     * @method  获取用户信息
     * @param string $userInfo 内容
     * @return array
     */
    private function getUser($userInfo)
    {
        $info = $this->dealMatchStr("/<div class=\"user-face\">\s(.*?)\s<\/div>/ism", $userInfo);
        //用户头像
        $avatarUrl = $this->dealMatchStr("/[img|IMG].*?src=['|\"](.*?(?:[.gif|.jpg]))['|\"].*?[\/]?>/", $info);
        if (strpos($avatarUrl, "user_normal") === false) {
            $user['avatar'] = $avatarUrl;
        } else {
            $user['avatar'] = '';
        }

        //用户昵称
        $user['nickname'] = $this->dealMatchStr("/[img|IMG].*?alt=['|\"](.*?)['|\"].*?[\/]?>/", $info);
        return $user;
    }

    /**
     * @method  从话题搜索数据，获取到帖子列表
     * @param string $content 内容
     * @param string $mid 帖子ID
     * @return array
     */
    private function getCommentList($content, $mid)
    {
        $comment = [];
        if (empty($content)) {
            return $comment;
        }
        foreach ($content as $commentKey => $commentValue) {
            //判断是否为回复评论，直接跳过
            if (strpos($commentValue, 'reply-quote-content') != false) {
                continue;
            }
            //评论用户信息
            $user = $this->getUser($commentValue);
            $user['nickname'] = str_replace(" ", "", $user['nickname']);
            //评论信息
            $commentDetail = $this->getComment($commentValue);
            if (empty($user['nickname']) || empty($commentDetail['text']['text'])) {
                continue;
            }
            $comment[$commentKey]['user'] = $user;
            $comment[$commentKey]['comment'] = $commentDetail;
            //增加评论人昵称
            $comment[$commentKey]['comment']['forumId'] = $mid;
            $comment[$commentKey]['comment']['nickname'] = $user['nickname'];
        }

        return $comment;
    }

    /**
     * @method  从话题搜索数据，获取到单个帖子信息
     * @param string $comment 内容
     * @return array
     */
    private function getComment($comment)
    {
        $commentInfo = [
            'id' => '',
            'rootid' => '',
            'createdAt' => '',
            'text' => [
                'text' => '',
                'position' => '',
                'topicList' => [],
            ],
        ];
        if (empty($comment)) {
            return $commentInfo;
        }

        // id
        $commentInfo['id'] = $this->dealMatchStr("/data-cid=\"(.*)\" >/i", $comment);
        $commentInfo['rootid'] = $commentInfo['id'];
        // 评论时间
        $commentInfo['createdAt'] = $this->dealMatchStr("/<span class=\"pubtime\">(.*)<\/span>/i", $comment);
        // 评论内容
        $text = $this->dealMatchStr("/<p class=\" reply-content\">(.*)<\/p>/i", $comment);
        $commentInfo['text']['text'] = $text;
        // 评论图片
        $commentInfo['images'] = [];
        $divMatches = $this->getHtmlLabel('/<div[^>]*\s+class="cmt-img"[^>]*>(.*)<\/div>/isU', $comment);
        if (!empty($divMatches[1])) {
            $images = $this->getHtmlLabel('/<img[^>]*\s+src="([^"]*)"[^>]*>/isU', $divMatches[1][0]);
            $commentInfo['images'] = $images[1];
        }

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
