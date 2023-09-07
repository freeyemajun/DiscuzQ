<?php
/**
 * Copyright (C) 2021 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Plugin\Shop\Controller;


use App\Commands\Attachment\AttachmentUploader;
use App\Common\ResponseCode;
use App\Models\Attachment;
use App\Models\PluginSettings;
use App\Settings\SettingsRepository;
use Discuz\Base\DzqLog;
use Discuz\Wechat\EasyWechatTrait;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;

trait WxShopTrait
{
    use EasyWechatTrait;

    protected $config;
    protected $httpClient;
    protected $wxApp;
    protected $accessToken;
    protected $settingData;

    private function getWxShopHttpClient(){
        if (empty($this->httpClient)){
            $this->httpClient = new Client([]);
        }
        return $this->httpClient;
    }

    public function getWxApp($appId){
        if (!empty($this->wxApp)){
            return [0,$this->wxApp];
        }
        $settingData = app()->make(PluginSettings::class)->getSettingRecord($appId);
        if (empty($settingData)){
            return [ResponseCode::RESOURCE_NOT_FOUND,"插件没配置"];
        }
        if (!isset($settingData["public_value"]["wxAppId"])
            || empty($settingData["public_value"]["wxAppId"])
            || !isset($settingData["private_value"]["wxAppSecret"])
            || empty($settingData["private_value"]["wxAppSecret"])){
            return [ResponseCode::RESOURCE_NOT_FOUND,"插件没配置"];
        }
        $this->wxApp = $this->miniProgram(["app_id"=>$settingData["public_value"]["wxAppId"],"secret"=>$settingData["private_value"]["wxAppSecret"]]);
        if (empty($this->wxApp)){
            return [ResponseCode::RESOURCE_NOT_FOUND,"插件配置不正确"];
        }
        return [0, $this->wxApp];
    }

    public function getAccessToken($appId){
        if (!empty($this->accessToken)){
            return [0,$this->accessToken];
        }

        list($result,$wxApp) = $this->getWxApp($appId);
        if ($result !== 0){
            return [$result,$wxApp];
        }
        $accessToken = $wxApp->access_token->getToken(true);
        if (empty($accessToken["access_token"])){
            return [ResponseCode::RESOURCE_NOT_FOUND,"插件配置错误"];
        }
        $this->accessToken = $accessToken["access_token"];
        return [0,$this->accessToken];
    }


    private function getShopList($accessToken,$page,$perPage)
    {
        $url = "https://api.weixin.qq.com/product/spu/get_list?access_token=".$accessToken;
        $one = $this->getWxShopHttpClient();

        $body = [ "status"=>5,
            "page"=>$page,
            "page_size"=>$perPage,
            "need_edit_spu"=>0
        ];
        $bodyStr = json_encode($body);

        $options = [
                "body"=>$bodyStr
            ];

        $response = $one->request("post", $url, $options);
        $statusCode = $response->getStatusCode();
        if ($statusCode != 200){
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND,"商品列表错误");
        }
        $contentData = $response->getBody()->getContents();
        if (empty($contentData)){
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND,"商品列表错误");
        }
        $result = json_decode($contentData,true);

        return $result;
    }

    private function getProductInfo($accessToken, $productId)
    {
        $url = "https://api.weixin.qq.com/product/spu/get?access_token=".$accessToken;

        $httpClientTemp = $this->getWxShopHttpClient();

        $body = [ "product_id"=>$productId,
            "out_product_id"=>"",
            "need_edit_spu"=>0
        ];
        $bodyStr = json_encode($body);

        $options = [
            "body"=>$bodyStr
        ];

        $response = $httpClientTemp->request("post", $url, $options);

        $statusCode = $response->getStatusCode();
        if ($statusCode != 200){
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND,"商品错误");
        }
        $contentData = $response->getBody()->getContents();
        if (empty($contentData)){
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND,"商品错误");
        }
        $result = json_decode($contentData,true);

        if(empty($result["data"]) || empty($result["data"]["spu"])){
            return null;
        }

        return $result["data"]["spu"];
    }

    public function packProductDetail($id,$productId,$name,$imgUrl,$price,$inUrl,$outUrl){
        $oneGoods=[
            "id"=>$id,
            "productId"=>(string)$productId,
            "title"=>$name,
            "imagePath"=>$imgUrl,
            "price"=>(string)$price,
        ];
        return $oneGoods;
    }

    /**
     * 商品二维码
     * @param $appId
     * @param $path
     * @return array|int
     */
    public function getProductQrCode($appId, $path){
        $pathNew = str_replace("plugin-private://","__plugin__/",$path);
        list($result,$wxApp) = $this->getWxApp($appId);
        if ($result !== 0){
            DzqLog::error('WxShopTrait::getProductQrCode', [], $wxApp);
            return ["", "", false];
        }

        $qrResponse = $wxApp->app_code->get($pathNew);
        if(is_array($qrResponse) && isset($qrResponse['errcode']) && isset($qrResponse['errmsg'])) {
            DzqLog::error('WxShopTrait::getProductQrCode', [], $qrResponse['errmsg']);
            return ["", "", false];
        }
        $pStartIndex = strpos($path,"productId=");
        $productIdStr = substr($path, $pStartIndex+strlen("productId="));

        $fileName = "wxshop_".$productIdStr."_".time().".jpg";
        $qrBuf = $qrResponse->getBody()->getContents();

        $tmpFile = tempnam(storage_path('/tmp'), 'attachment');
        $fileHandler = fopen($tmpFile,"w");
        fwrite($fileHandler, $qrBuf);
        fclose($fileHandler);

        $attach = $this->save($tmpFile,$fileName,"image/jpeg");

        @unlink($tmpFile);

        return $attach;
    }


    private function save($path,$fileName,$fileType){
        $uploader = app()->make(AttachmentUploader::class);

        $file = new UploadedFile(
            $path,
            $fileName,
            $fileType,
            null,
            true
        );
        // 上传
        $uploader->upload($file, Attachment::TYPE_OF_FILE);

        list($width, $height) = getimagesize($path);

        $attachment = Attachment::build(
            1,
            Attachment::TYPE_OF_FILE,
            $uploader->fileName,
            $uploader->getPath(),
            $fileName,
            $file->getSize(),
            $file->getClientMimeType(),
            $uploader->isRemote(),
            Attachment::APPROVED,
            "",
            0,
            $width,
            $height
        );
        $attachment->save();

        return [$attachment->file_path, $uploader->fileName, $attachment->is_remote];
    }

    /**
     * 小商店二维码
     * @param $appId
     * @return array|string
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getShopQrCode($appId){
        list($result,$wxApp) = $this->getWxApp($appId);
        if ($result !== 0){
            DzqLog::error('WxShopTrait::getProductQrCode', [], $wxApp);
            return ["",false];
        }

        $qrResponse = $wxApp->app_code->get("pages/index/index");
        if(is_array($qrResponse) && isset($qrResponse['errcode']) && isset($qrResponse['errmsg'])) {
            DzqLog::error('WxShopTrait::getShopQrCode', [], $qrResponse['errmsg']);
            return  ["",false];
        }

        $fileName = "wxshop_".$appId.".jpg";
        $qrBuf = $qrResponse->getBody()->getContents();

        try {
            $settings =  app()->make(SettingsRepository::class);
            $fileSystemFactory =  app()->make( \Illuminate\Contracts\Filesystem\Factory::class);

            $isRemote = false;
            $path='shop/'.$fileName;
            if ($settings->get('qcloud_cos', 'qcloud')) {
                $fileSystemFactory->disk('cos')->put($path, $qrBuf);
                $isRemote = true;
            }
            $fileSystemFactory->disk('public')->put($path, $qrBuf);
            return [$path,$isRemote];
        } catch (Exception $e) {
            if (empty($e->validator) || empty($e->validator->errors())) {
                $errorMsg = $e->getMessage();
            } else {
                $errorMsg = $e->validator->errors()->first();
            }
            DzqLog::error('ShopFileSave::saveFile', [], $errorMsg);

            return  ["",false];
        }
    }


    public function getSchemeProduct($appId,$path)
    {
        list($ret2,$accessToken) = $this->getAccessToken($appId);
        $settingData = app()->make(PluginSettings::class)->getSettingRecord($appId);
        if (empty($settingData) || empty($settingData["public_value"]["wxScheme"])
            || empty($settingData["public_value"]["wxAppId"])
            || empty($settingData["private_value"]["wxAppSecret"])){
            return "";
        }

        $wxAppId = $settingData["public_value"]["wxAppId"];
        $wxAppSecret = $settingData["private_value"]["wxAppSecret"];

        $pathNew = str_replace("plugin-private://","__plugin__/",$path);
        $post_data['jump_wxa']['path'] = $pathNew;
        $post_data['jump_wxa']['query'] = '2';
        $postBody = json_encode($post_data);

        return $this->getScheme($wxAppId,$wxAppSecret,$accessToken,$postBody);
    }

    public function getScheme($appid,$secret,$accessToken,string $body)
    {
        $httpClientTemp = $this->getWxShopHttpClient();
        if (empty($accessToken)){
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;

            $response = $httpClientTemp->request("get", $url, []);
            $statusCode = $response->getStatusCode();
            if ($statusCode != 200){
                return "";
            }
            $contentData = $response->getBody()->getContents();
            if (empty($contentData)){
                return "";
            }
            $result = json_decode($contentData,true);
            $accessToken = $result['access_token'];
        }

        $options = [];
        if (!empty($body)){
            $options["body"] = $body;
        }
        $post_url = 'https://api.weixin.qq.com/wxa/generatescheme?access_token='.$accessToken;
        $response = $httpClientTemp->request("post", $post_url, $options);
        $statusCode = $response->getStatusCode();
        if ($statusCode != 200){
            return "";
        }
        $contentData = $response->getBody()->getContents();
        if (empty($contentData)){
            return "";
        }
        $result = json_decode($contentData,true);
        return isset($result['openlink']) ? $result['openlink'] : "";
    }
}
