<?php


namespace Plugin\Import\Platform;


class LearnStar
{
    private $cookie = '';
    private $userAgent = '';

    /**
     * @method  主入口
     * @param string $topic 话题
     * @param int $page 获取几天前的数据，每次获取10天
     * @param string $cookie 用户cookie
     * @return array
     */
    public function main($topic, $num = 1, $cookie = '', $userAgent)
    {
        set_time_limit(0);
        $this->cookie = $cookie;
        $this->userAgent = $userAgent;
        return $this->getData($topic, $num);
    }

    private function getData($topic, $num){
        if ($num<=0){
            return [];
        }

        //搜索
        $threadDataList = $this->getData_groupsSearch($topic,$num);

        return $threadDataList;
    }

    private function getData_groupsSearch($key,$num){
        $ret = [];
        $curIndex = 0;
        $count = $num<30?$num:30;
        $leftNum = $num;
        for ($i=0;$i<100;$i++){
            $url = "https://api.zsxq.com/v2/search/topics?keyword=".urlencode($key)."&index=".$curIndex."&count=".$count;
            $html = $this->getDataByUrl($url, $this->userAgent, $this->cookie);
            $data = json_decode($html);

            if(empty($data)){
                break;
            }
            if($data->succeeded != true){
                break;
            }

            $resp_data = $data->resp_data;
            if (empty($resp_data->topics)){
                break;
            }
            foreach ($resp_data->topics as $oneTopic){
                //搜索的要重新拉取一遍，图文混排的这种，在搜索中没有链接出来
                $urlTopic = "https://api.zsxq.com/v2/topics/".$oneTopic->topic->topic_id;
                $htmlTopic = $this->getDataByUrl($urlTopic, $this->userAgent, $this->cookie);
                $dataTopic = json_decode($htmlTopic);
                if(empty($dataTopic)){
                    continue;
                }
                if($dataTopic->succeeded != true){
                    continue;
                }
                $oneTopicTemp = $dataTopic->resp_data->topic;
                $oneThread = $this->paseOneTopic($oneTopicTemp);
                if (empty($oneThread)){
                    continue;
                }
                array_push($ret, $oneThread);

                $leftNum--;
                if ($leftNum<=0){
                   return $ret;
                }
            }

            if ($count<30 || count($resp_data->topics) < $count){
                break;
            }

            $curIndex = $curIndex+count($resp_data->topics);
        }

        return $ret;
    }

    private function getData_groups(){
        $url = "https://api.zsxq.com/v2/groups";
        $html = $this->getDataByUrl($url, $this->userAgent, $this->cookie);
        $data = json_decode($html);
        $ret = [];
        if(empty($data)){
            return $ret;
        }
        if($data->succeeded != true){
            return $ret;
        }

        $respData = $data->resp_data;
        $groups = $respData->groups;
        foreach ($groups as $v){
            array_push($ret, $v->group_id);
        }

        return $ret;
    }


    private function getData_oneGroup($gId, $num,$filterIdList){
        $threadDataList=[];
        if ($num<=0){
            return $threadDataList;
        }
        $leftN = $num;
        $endTime = null;
        $oneN = 20;
        for($i=0;$i<100;$i++){
            list($data,$endTime,$isContinue) = $this->getData_oneGroupPer($gId, $endTime, $oneN, $filterIdList, $leftN);
            if (!empty($data)){
                $threadDataList = array_merge($threadDataList,$data);
                $leftN-=count($data);
                if ($leftN<=0){
                    break;
                }
            }
            if (!$isContinue){
                break;
            }

        }
        return $threadDataList;
    }

    public function getDataByUrl($url, $userAgent, $cookie){
        $rrd = $this->rId();
        $tts = $this->ts();
        $strS = $url." ".$tts." ".$rrd;
        $aa11 = sha1($strS);

        $headers = array();

        $headers[0] = 'user-agent:' . $userAgent;
        $headers[1] = 'cookie:' . $cookie;
        $headers[2] = 'x-request-id:' . $rrd;
        $headers[3] = 'x-signature:' . $aa11;
        $headers[4] = 'x-timestamp:' . $tts;

        $html = $this->curlGet2($url,$headers);

        return $html;
    }

    private function getData_oneGroupPer($gId, $endTime, $num, $filterIds, $leftN){
        $url = "https://api.zsxq.com/v2/groups/".$gId."/topics?scope=all&count=".$num;
        if ($endTime!=null){
            $tempET = urlencode($endTime);
            $url = "https://api.zsxq.com/v2/groups/".$gId."/topics?scope=all&count=".($num+1)."&end_time=".$tempET;
        }

        $html = $this->getDataByUrl($url, $this->userAgent, $this->cookie);

        $data = json_decode($html);
        $ret = [];
        if(empty($data)){
            return [$ret,null,false];
        }
        if($data->succeeded != true){
            return [$ret,null,false];
        }

        $respData = $data->resp_data;

        if(empty($respData->topics)){
            return [$ret,null,false];
        }
        $topics = $respData->topics;
        $isContinue = count($topics)>=$num;

        $i=0;
        if(!empty($endTime)){
            $i = 1;
        }


        for (;$i<count($topics);$i++){
            $oneTopic = $topics[$i];

            if (!empty($oneTopic->create_time)){
                $endTime = $oneTopic->create_time;
            }

            if (in_array($oneTopic->topic_id,$filterIds)){
               continue;
            }

            $oneD = $this->paseOneTopic($oneTopic);
            if (empty($oneD)){
                continue;
            }

            array_push($ret, $oneD);
            $leftN--;
            if ($leftN<=0){
                $isContinue = false;
                break;
            }
        }

        return [$ret,$endTime,$isContinue];
    }

    private function paseOneTopic($oneTopic){


        $oneD = [];
        $author=[];
        $forum =[];

        $forum["id"] = $oneTopic->topic_id;
        $forum["createdAt"] = $oneTopic->create_time;

        $text = [];
        $pics = [];
        $medias = [];
        $attachment = [];
        $commonetList = [];


        if ($oneTopic->type == "talk")// 帖子,作业,问答等类型
        {
            $talk = $oneTopic->talk;
            $owner = $talk->owner;
            $author["nickname"] = str_replace(" ","", $owner->name);
            $author["avatar"] = $owner->avatar_url;

            //文字
            if (!empty($talk->article)){
                //文字图片混编的
                $text = $this->getTextArticle($talk->article);
            }else{
                if(!empty($talk->text)){
                    $text = $this->getTextContent($talk->text);
                }
            }

            //文件的
            if(!empty($talk->files)) {
                $attachment = $this->getAttachFile($talk->files);
            }

            //图片的
            if(!empty($talk->images)) {
                $pics = $this->getAttachImage($talk->images);
            }
            //评论
            $commonetList = $this->getData_OneComment($oneTopic->topic_id, $author['nickname']);
        }elseif($oneTopic->type == "q&a"){
            $talk = $oneTopic->question;
            $owner = $talk->questionee;
            $author["nickname"] = str_replace(" ","", $owner->name);
            $author["avatar"] = $owner->avatar_url;

            //文字
            if (!empty($talk->article)){
                //文字图片混编的
                $text = $this->getTextArticle($talk->article);
            }else{
                if(!empty($talk->text)){
                    $text = $this->getTextContent($talk->text);
                }
            }
            //文件的
            if(!empty($talk->files)) {
                $attachment = $this->getAttachFile($talk->files);
            }
            //图片的
            if(!empty($talk->images)) {
                $pics = $this->getAttachImage($talk->images);
            }

            //有回答者的，把回答者转为一个评论
            if(!empty($oneTopic->answer)) {
                $answerComment = $this->answerToComment($oneTopic->answer,$oneTopic->topic_id, $author['nickname'],$oneTopic->create_time);
                array_push($commonetList, $answerComment);
            }
            $otherCommonet= $this->getData_OneComment($oneTopic->topic_id, $author['nickname']);
            $commonetList = array_merge($commonetList,$otherCommonet);
        }else{ //非帖子类型的排除
            return [];
        }

        $forum["text"] = $text;
        $forum["images"] = $pics;
        $forum["media"] = $medias;
        $forum["attachments"] = $attachment;



        $oneD["user"] = $author;
        $oneD["forum"] = $forum;
        $oneD["comment"] = $commonetList;

        return $oneD;
    }

    private function getTextContent($textIn){
        $textIn = urldecode($textIn);
        $tagList = array();
        $textContent = "";
        $posStart = 0;
        $flagStartStr = '<e type=';  //hashtag标签,web链接
        $flagEndStr = '/>';
        while (1){
            list($preStr,$flagStr,$posFlagEnd) = $this->getFlagSubStr($textIn,$posStart,$flagStartStr,$flagEndStr);
            $textContent = $textContent.$preStr;

            if (strpos($flagStr,'type="hashtag"')!==false){ //标签
                $textContent = $textContent.$flagStr;
            }else{
                //取出文字
                $titleStr = $this->dealMatchStr('/<e[^>]+title=["\'](.*?)["\']/i', $flagStr);
                $textContent = $textContent. $titleStr;
                //取出链接
                $linkStr = $this->dealMatchStr('/<e[^>]+href=["\'](.*?)["\']/i', $flagStr);
                if ($titleStr != $linkStr){
                    $textContent = $textContent.$linkStr;
                }
            }

            if ($posFlagEnd === false){
                break;
            }
            $posStart = $posFlagEnd;
        }


        list($textContent,$tagList) = $this->filterDataTag($textContent);

        $text = array();
        $text["text"] = $textContent;
        $text["topicList"] = $tagList;

        return $text;
    }

    private function getTextArticle($article){
        $url = $article->article_url;
        $html = $this->getDataByUrl($url, $this->userAgent, $this->cookie);

        $contentPosStart = strpos($html,'<div class="content">',0);
        $contentPosEnd = strpos($html,'</div>',$contentPosStart);
        $textIn = substr($html,$contentPosStart,$contentPosEnd-$contentPosStart+6);

        list($textContent,$tagList) = $this->filterDataTag($textIn);

        $text = array();
        $text["text"] = $textContent;
        $text["topicList"] = $tagList;

        return $text;
    }

    /**
     * @method  获取两个字符串，标识符前面部分和标识符包括的字符串
     * @param string $textIn 文本
     * @param string $posStart 起始
     * @param string $flagStartStr 起始符
     * @param string $flagEndStr 结束符
     * @return array
     */
    private function getFlagSubStr($textIn,$posStart,$flagStartStr,$flagEndStr){
        $subStrPre = "";
        $subStrFlag = "";
        $posEnd = false;
        $posFlagStart = strpos($textIn, $flagStartStr, $posStart);
        //拿出第一节文字
        if ($posFlagStart === false){
            $subStrPre = substr($textIn,$posStart);
        }else{
            $subStrPre = substr($textIn,$posStart,$posFlagStart-$posStart);
            $posFlagEnd = strpos($textIn, $flagEndStr, $posFlagStart);
            if ($posFlagEnd!==false){
                $subStrFlag = substr($textIn, $posFlagStart, $posFlagEnd-$posFlagStart+strlen($flagEndStr));
                $posEnd = $posFlagEnd+strlen($flagEndStr);
            }
        }


        return [$subStrPre,$subStrFlag,$posEnd];
    }


    private function filterDataTag($textIn){
        //处理标签
        $tagList = array();
        $textContent = "";
        $posStart = 0;
        $flagStartStr = '<e type="hashtag"';  //hashtag标签
        $flagEndStr = '/>';
        while (1){
            list($preStr,$flagStr,$posFlagEnd) = $this->getFlagSubStr($textIn,$posStart,$flagStartStr,$flagEndStr);

            if (strpos($flagStr,'type="hashtag"') !== false){ //标签

                $tagStrTemp = $this->dealMatchStr('/<e[^>]+title=["\']#(.*?)#["\']/i', $flagStr);
                $tagStr = urldecode($tagStrTemp);
                array_push($tagList,$tagStr);
            }

            if ($posFlagEnd === false){
                break;
            }
            $posStart = $posFlagEnd;
        }
        return [$textIn,$tagList];
    }

    private function getAttachFile($files){
        $fileLinkList = [];
        foreach ($files as $oneFile){
            $url = "https://api.zsxq.com/v2/files/".$oneFile->file_id."/download_url";
            $html = $this->getDataByUrl($url, $this->userAgent, $this->cookie);
            $data = json_decode($html);
            if(empty($data)){
                continue;
            }
            if($data->succeeded != true){
                continue;
            }

            $respData = $data->resp_data;
            if (empty($respData->download_url)){
                continue;
            }
            array_push($fileLinkList, $respData->download_url);
        }
        return $fileLinkList;
    }


    private function getAttachImage($images){
        $pics = [];
        foreach ($images as $oneImage){
            if(!empty($oneImage->original)){//原图
                $imagelink =  urldecode($oneImage->original->url);
                array_push($pics,$imagelink);
            }else if(!empty($oneImage->large)){//大图
                $imagelink =  urldecode($oneImage->large->url);
                array_push($pics,$imagelink);
            }else{
                //小图
                if(empty($oneImage->thumbnail)){
                    continue;
                }
                $imagelink =  urldecode($oneImage->thumbnail->url);
                array_push($pics,$imagelink);
            }
        }
        return $pics;
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


    private function  getData_OneComment($topicId, $nickname){
        $url = "https://api.zsxq.com/v2/topics/".$topicId."/comments?sort=asc&count=30";
        $html = $this->getDataByUrl($url, $this->userAgent, $this->cookie);
        $data = json_decode($html);
        $ret = [];
        if(empty($data)){
            return $ret;
        }
        if($data->succeeded != true){
            return $ret;
        }

        $resp_data = $data->resp_data;
        if(empty($resp_data->comments)){
            return $ret;
        }
        $comments = $resp_data->comments;
        foreach ($comments as $one){


            $commentData = [];

            $text = [];
            if(isset($one->text)){
                $text["text"] = $one->text;
            }else{
                $text["text"] = "";
            }

            $commentData["text"] = $text;
            if (isset($one->images)) {
                $images = $this->getAttachImage($one->images);
                $commentData["images"] = $images;
            }
            if (isset($one->create_time)){
                $commentData["createdAt"] = $one->create_time;
            }else{
                $commentData["createdAt"] = date(DATE_ISO8601);
            }
            $userComment = [];
            if(!empty($one->owner)){
                $owner = $one->owner;
                $userComment["nickname"]= str_replace(" ","",$owner->name);
                $userComment["avatar"] = $owner->avatar_url;
            }

            $oneComment = [];
            $oneComment["comment"] = $commentData;
            $oneComment["user"] = $userComment;

            array_push($ret, $oneComment);
        }

        return $ret;
    }


    private function answerToComment($answer,$topicId, $nickname, $createTime){
        $commentData = [];
        $text = [];
        if (!isset($answer->text)) {
            $text["text"] = "";
        }else {
            $text["text"] = $answer->text;
        }
        $commentData["text"] = $text;
        if (isset($answer->images)) {
            $images = $this->getAttachImage($answer->images);
            $commentData["images"] = $images;
        }
        $commentData["createdAt"] = $createTime;

        $userComment = [];
        if(!empty($answer->owner)){
            $owner = $answer->owner;
            $userComment["nickname"]= str_replace(" ","", $owner->name);;
            $userComment["avatar"] = $owner->avatar_url;
        }

        $oneComment = [];
        $oneComment["comment"] = $commentData;
        $oneComment["user"] = $userComment;

        return $oneComment;
    }

    private function randFloat($min = 0, $max = 1)
    {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min),2);
    }

    private function rId(){
        $t="";
        $e=0;

        for (;$e<32;$e++){
            $a = 16*$this->randFloat();
            $strRandom = floor($a);
            $t .= dechex($strRandom);

            if($e==8 || $e==12 || $e==16 || $e==20 )
                $t .= "-";
        }
        return $t;
    }

    private function  ts(){
        $t = time();
        $t1 = $t/1e3;
        $t2 = floor(  $t1 );
        return $t;
    }



    /**
     * @method  curl-get请求
     * @param string $url 请求地址D
     * @param array $headers 请求头信息
     * @param int $port 端口号
     * @return string $filecontent  采集内容
     */
    private function curlGet2($url, $headers = [], $port = 80)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($port !== 80) {
            curl_setopt($ch, CURLOPT_PORT, $port);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);//设定是否输出页面内容
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);       //链接超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);       //设置超时时间

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  //对于web页等有重定向的，要加上这个设置，才能真正访问到页面

        $filecontent = curl_exec($ch);
        curl_close($ch);

        return $filecontent;
    }

}
