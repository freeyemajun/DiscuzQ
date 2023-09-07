<?php

namespace Plugin\Import\Platform;

use App\Import\PlatformTrait;

class Discuz
{
    use PlatformTrait;

    private $siteUrl;
    private $urlCookie;
    private $urlPort;
    private $keyword;
    private $formhash;
    private $searchid;
    private $urlContentEncoding;


    public function main($topic, $number, $url, $cookie = '', $port = 80)
    {
        if (substr($url, -1) != '/') {
            $url = $url . '/';
        }
        $this->siteUrl = $url;
        $this->urlPort = $port;
        $this->urlCookie = $cookie;
        $this->keyword = $topic;
        $forumIndexContent = $this->getUrlContent($url);
        if ($this->urlContentEncoding != 'UTF-8') {
            $this->keyword = mb_convert_encoding($topic, "gbk", "utf-8");
        }
        $this->formhash = $this->getFormhash($forumIndexContent);
        $this->searchid = $this->getSearchId($url);

        $page = 1;
        $data = $pageData = $this->getDataList($url, $page);
        while (count($data) < $number && !empty($pageData)) {
            $page++;
            $pageData = $this->getDataList($url, $page);
            $data = array_merge($data, $pageData);
        }
        if (count($data) > $number) {
            $data = array_slice($data,0, $number);
        }
        return $data;
    }

    private function getDataList($url, $page)
    {
        $dataList = [];
        if ($this->urlContentEncoding == 'UTF-8') {
            $url = $url .'search.php?mod=forum&searchid=' . $this->searchid . '&orderby=lastpost&ascdesc=desc&searchsubmit=yes&kw=' . urlencode($this->keyword) . '&page=' . $page;
        } else {
            $url = $url .'search.php?mod=forum&searchid=' . $this->searchid . '&orderby=lastpost&ascdesc=desc&searchsubmit=yes&kw=' . $this->keyword . '&page=' . $page;
        }

        $htmlContent = $this->getUrlContent($url);

        $aMatch = '/<a[^>]*\s+href="forum.php([^"]*)"[^>]*>(.*)<\/a>/isU';
        $aMatches = $this->getHtmlLabel($aMatch, $htmlContent);
        if (empty($aMatches[1])) {
            return $dataList;
        }

        $threadDetailUrl = [];
        foreach ($aMatches[1] as $href) {
            if (strpos($href, "?mod=viewthread") !== false) {
                $threadDetailUrl[] = $this->siteUrl . 'forum.php' . htmlspecialchars_decode($href);
            }
        }
        if (empty($threadDetailUrl)) {
            return $dataList;
        }

        foreach ($threadDetailUrl as $threadUrl)
        {
            $data = [];
            $threadHtmlContent = $this->getUrlContent($threadUrl);
            if ($this->urlContentEncoding != 'UTF-8') {
                $threadHtmlContent = $this->changeContentEncoding($threadHtmlContent, 'UTF-8', $this->urlContentEncoding);
            }

            $spanMatch = '/<span id="thread_subject".*>(.*)<\/span>/isU';
            $spanMatches = $this->getHtmlLabel($spanMatch, $threadHtmlContent);
            $data['forum']['text']['title'] = $spanMatches[1][0] ?? '';

            $divMatch = '/<div id="post_([^"]*)" >(.*?)<\/script>\r\n<\/div>/ism';
            $divMatches = $this->getHtmlLabel($divMatch, $threadHtmlContent);
            if (empty($divMatches[2])) break;

            foreach ($divMatches[2] as $divKey => $divContent) {
                $data = $this->getForum($divMatches[1][$divKey], $data, $divKey, $divContent);
                if (!$data) {
                    break 2;
                }
            }
            $dataList[] = $data;
        }

        return $dataList;
    }

    private function deleteEmotion($content)
    {
        $imgMatch = '/<img[^>]*\s+src="static\/image\/smiley([^"]*)"[^>]*>/isU';
        $imgMatches = $this->getHtmlLabel($imgMatch, $content);
        if (!empty($imgMatches[0])) {
            foreach ($imgMatches[0] as $value) {
                $content = str_replace($value, '', $content);
            }
        }
        return $content;
    }

    private function getForum($postMessageId, $data, $key, $content)
    {
        $content = $this->deleteEmotion($content);
        if ($key === 0) {
            $data['user']['nickname'] = $this->getNickname($content);
            if (!$data['user']['nickname']) return false;
            $data['user']['avatar'] = $this->getAvatar($content);
            $tdMatch = '/<td[^>]*\s+id="postmessage_'.$postMessageId.'">(.*)<\/td>/isU';
            $tdMatches = $this->getHtmlLabel($tdMatch, $content);
            if (!isset($tdMatches[1][0]) || empty($tdMatches[1][0])) {
                return false;
            }
            $text = $tdMatches[1][0];
            $data['forum']['text']['topicList'] = $this->getTopicList($content);
            if (!empty($data['forum']['text']['topicList'])) {
                $text = $text . '#' . implode('##', $data['forum']['text']['topicList']) . '#';
            }
            $data['forum']['text']['position'] = '';
            $data['forum']['images'] = $this->getFileUrl('tattl attm', $content);
            $data['forum']['attachments'] = $this->getFileUrl('tattl', $content);
            $data['forum']['media']['video'] = $data['forum']['media']['audio'] = [];
            [$data['forum']['contentMedia'], $text] = $this->getContentMedia($text);
            $data['forum']['text']['text'] = $this->changeText($text);
            $data['forum']['createdAt'] = $this->getCreatedAt($postMessageId, $content);
            $data['comment'] = [];
        } else {
            $comment = $this->getComment($postMessageId, $content);
            if ($comment) $data['comment'][] = $comment;
        }
        return $data;
    }

    private function getComment($postMessageId, $content)
    {
        $comment['user']['nickname'] = $this->getNickname($content);
        if (!$comment['user']['nickname']) return false;
        $comment['user']['avatar'] = $this->getAvatar($content);
        $tdMatch = '/<td[^>]*\s+id="postmessage_'.$postMessageId.'">(.*)<\/td>/isU';
        $tdMatches = $this->getHtmlLabel($tdMatch, $content);
        if (!isset($tdMatches[1][0]) || empty($tdMatches[1][0])) {
            return false;
        }
        $comment['comment']['text']['text'] = $this->changeText($tdMatches[1][0]);
        $comment['comment']['images'] = $this->getFileUrl('tattl attm', $content);
        $comment['comment']['createdAt'] = $this->getCreatedAt($postMessageId, $content);
        return $comment;
    }

    private function changeText($text)
    {
        $text = preg_replace("'<script[^>]*?>.*?</script>'smi",'', $text);
        $text = preg_replace("/<(\/?ignore_js_op.*?)>/si",'', $text);

        $pMatch = '/<p class="xg1 y">(.*)<\/p>/isU';
        $pMatches = $this->getHtmlLabel($pMatch, $text);
        if (!empty($pMatches[0])) {
            foreach ($pMatches[0] as $value) {
                $text = str_replace($value, '', $text);
            }
        }

        $imgMatch = '/<img[^>]*\s+id="aimg_([^"]*)"[^>]*\s+src="([^"]*)"[^>]*\s+file="([^"]*)"[^>]*>/isU';
        $imgMatches = $this->getHtmlLabel($imgMatch, $text);
        if (empty($imgMatches[2]) || empty($imgMatches[3])) {
            $imgFileMatch = '/<img[^>]*\s+file="([^"]*)"[^>]*>/isU';
            $imgFileMatches = $this->getHtmlLabel($imgFileMatch, $text);
            if (empty($imgFileMatches[0])) return $text;
            foreach ($imgFileMatches[0] as $key => $value) {
                if (strpos($value, "file=") !== false && strpos($value, "src=") === false) {
                    $value = str_replace("file=", "src=", $value);
                    $text = str_replace($imgFileMatches[0][$key], $value, $text);
                }
            }
        } else {
            foreach ($imgMatches[3] as $key => $value) {
                if ($value != $imgMatches[2][$key]) {
                    $text = str_replace($imgMatches[2][$key], $value, $text);
                }
            }
        }

        return $text;
    }

    private function getNickname($content)
    {
        $aMatch = '/<a[^>]*\s+href="home.php([^"]*)"[^>]*>(.*)<\/a>/isU';
        $aMatches = $this->getHtmlLabel($aMatch, $content);
        if (!isset($aMatches[2][0]) || empty($aMatches[2][0])) {
            return false;
        }
        return str_replace(' ', '', $aMatches[2][0]);
    }

    private function getAvatar($content)
    {
        $avatar = '';
        $imgMatch = '/<img[^>]*\s+src="([^"]*)"[^>]*>/isU';
        $imgMatches = $this->getHtmlLabel($imgMatch, $content);
        if (!empty($imgMatches[1])) {
            foreach ($imgMatches[1] as $imageSrc) {
                if (strpos($imageSrc, "/avatar/") !== false) {
                    $avatar = $this->getUrlContent(htmlspecialchars_decode($imageSrc), 'redirectUrl');
                }
            }
        }
        return $avatar;
    }

    private function getTopicList($content)
    {
        $aMatch = '/<a[^>]*\s+href="misc.php([^"]*)"[^>]*>(.*)<\/a>/isU';
        $aMatches = $this->getHtmlLabel($aMatch, $content);
        if (!isset($aMatches[2]) || empty($aMatches[2])) return [];
        return $aMatches[2];
    }

    private function getFileUrl($className, $content)
    {
        $dlMatch = '/<dl class="' . $className . '">(.*)<\/dl>/isU';
        $dlMatches = $this->getHtmlLabel($dlMatch, $content);
        if (empty($dlMatches[1])) return [];

        $fileUrl = [];
        foreach ($dlMatches[1] as $value) {
            if ($className == 'tattl attm') {
                $imgMatch = '/<img[^>]*\s+file="([^"]*)"[^>]*>/isU';
                $imgMatches = $this->getHtmlLabel($imgMatch, $value);
                if (isset($imgMatches[1][0]) && !empty($imgMatches[1][0])) {
                    $fileUrl[] = $this->siteUrl . htmlspecialchars_decode($imgMatches[1][0]);
                }
            } else {
                $aMatch = '/<a[^>]*\s+href="forum.php([^"]*)"[^>]*>(.*)<\/a>/isU';
                $aMatches = $this->getHtmlLabel($aMatch, $value);
                if (isset($aMatches[1][0]) && !empty($aMatches[1][0])) {
                    $fileUrl[] = $this->siteUrl . 'forum.php' . htmlspecialchars_decode($aMatches[1][0]);
                }
            }

        }
        return $fileUrl;
    }

    private function getContentMedia($text)
    {
        $contentMedia = [
            'videos' => [],
            'audio' => []
        ];
        $videoExt = ['wmv', 'rm', 'mov', 'mpeg', 'mp4', '3gp', 'flv', 'avi', 'rmvb']; // 需要准确确定
        $audioExt = ['wma', 'mp3', 'ra', 'rm'];
        $aMatch = '/<a[^>]*\s+href="([^"]*)"[^>]*>(.*)<\/a>/isU';
        $aMatches = $this->getHtmlLabel($aMatch, $text);
        if (!isset($aMatches[1]) || empty($aMatches[1])) return [$contentMedia, $text];

        foreach ($aMatches[1] as $key => $value) {
            $ext = substr($value, strrpos($value,".") + 1);
            if (in_array($ext, $videoExt)) {
                $contentMedia['videos'][] = $value;
            }
            if (in_array($ext, $audioExt)) {
                $contentMedia['audio'][] = $value;
            }

            // 将音视频的a标签替换为iframe标签
            $oldHtmlLabel = $aMatches[0][$key];
            $newHtmlLabel = '<iframe src="' . $value . '" scrolling="no" border="0" frameborder="no" framespacing="0" allowfullscreen="true"></iframe>';
            $text = str_replace($oldHtmlLabel, $newHtmlLabel, $text);
        }
        return [$contentMedia, $text];
    }

    private function getCreatedAt($postMessageId, $content)
    {
        $currentTime = date('Y-m-d h:i:s', time());
        $emMatch = '/<em id="authorposton'.$postMessageId.'">(.*)<\/em>/isU';
        $emMatches = $this->getHtmlLabel($emMatch, $content);
        if (!isset($emMatches[1][0]) || empty($emMatches[1][0])) return $currentTime;

        $spanMatch = '/<span title="(.*)">/isU';
        $spanMatches = $this->getHtmlLabel($spanMatch, $emMatches[1][0]);
        if (!isset($spanMatches[1][0]) || empty($spanMatches[1][0])) return $currentTime;

        return $spanMatches[1][0];
    }

    private function getUrlContent($url, $demand = 'content')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($this->urlPort != 80) {
            curl_setopt($ch, CURLOPT_PORT, $this->urlPort);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);       //链接超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);       //设置超时时间
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  //对于web页等有重定向的，要加上这个设置，才能真正访问到页面
        curl_setopt($ch,CURLOPT_COOKIE, $this->urlCookie);
        $urlContent = curl_exec($ch);
        $redirectUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);//获取最终请求的url地址
        curl_close($ch);
        if ($demand == 'content') {
            $this->urlContentEncoding = $this->getEncoding($urlContent);
            return $urlContent;
        } else {
            return $redirectUrl;
        }
    }

    private function getEncoding($content)
    {
        $encoding = mb_detect_encoding($content, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5"));
        if ($encoding == 'EUC-CN') {
            $encoding = 'GB2312';
        }
        return $encoding;
    }

    private function changeContentEncoding($content, $expectedEncoding, $originEncoding)
    {
        return mb_convert_encoding($content, $expectedEncoding, $originEncoding);
    }

    private function getFormhash($content)
    {
        $formhash = '';
        $match = '/<input.*?name=[\"|\']?(.*?)[\"|\']?\s.*?.*?value=[\"|\']?(.*?)[\"|\']?\s.*?>/i';
        $matches = $this->getHtmlLabel($match, $content);
        if (empty($matches[1]) || empty($matches[2])) {
            $this->deleteImportLockFile();
            throw new \Exception('未获取到DiscuzX站点关键数据-formhash.');
        }
        foreach ($matches[1] as $key => $value) {
            if ($value == 'formhash') {
                $formhash = $matches[2][$key];
            }
        }
        return $formhash;
    }

    private function getSearchId($url)
    {
        $data = [
            'formhash' => $this->formhash,
            'srchtxt' => $this->keyword,
            'searchsubmit' => 'yes'
        ];

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url . 'search.php?mod=forum'); // 要访问的地址
        if ($this->urlPort != 80) {
            curl_setopt($curl, CURLOPT_PORT, $this->urlPort);
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36'); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, $url . 'search.php'); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($curl, CURLOPT_HTTPHEADER, []);
        curl_setopt($curl,CURLOPT_COOKIE, $this->urlCookie);
        curl_exec($curl);
        $redirectUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);//获取最终请求的url地址
        curl_close($curl); // 关闭CURL会话
        if (empty($redirectUrl)) {
            $this->deleteImportLockFile();
            throw new \Exception('未获取到重定向链接.');
        }

        $searchId = 0;
        $urlArray = explode('&', $redirectUrl);
        foreach ($urlArray as $value) {
            if (strpos($value, "searchid=") !== false) {
                $searchId = substr($value,strrpos($value,'=') + 1, strlen($value));
            }
        }
        if (empty($searchId)) {
            $this->deleteImportLockFile();
            throw new \Exception('未获取到DiscuzX站点关键数据-searchid.');
        }
        return $searchId;
    }
}