<?php


namespace Plugin\Shop;

use App\Api\Controller\Plugin\PluginTrait;
use App\Api\Serializer\AttachmentSerializer;
use App\Models\Attachment;
use App\Models\PluginSettings;
use App\Modules\ThreadTom\TomBaseBusi;
use Plugin\Shop\Controller\WxShopTrait;
use Plugin\Shop\Model\ShopProducts;

class ShopBusi extends TomBaseBusi
{
    use WxShopTrait;
    use PluginTrait;

    public const TYPE_ORIGIN = 10;
    public const TYPE_WX_SHOP = 11;


    public function create()
    {
        $products = $this->getParams('products');
        $productsNew = [];
        $wxshopNum = 0;
        foreach ($products as $item){
            if(!isset($item["type"])){
                continue;
            }
            if (self::TYPE_WX_SHOP == $item["type"]){
                if (!isset($item["data"]) || !isset($item["data"]["productId"])){
                    continue;
                }
                if ($wxshopNum>=10){
                    continue;
                }
                $wxshopNum++;
                $pData  = $this->doProduct($item["data"]["productId"]);
                if($pData !== false){
                    $item["data"] = $pData;
                }
            }
            $productsNew[] = $item;

        }
        $productData["products"] = $productsNew;
        return $this->jsonReturn($productData);
    }

    public function update()
    {
        $products = $this->getParams('products');
        $productsNew = [];
        $wxshopNum = 0;
        foreach ($products as $item){
            if(!isset($item["type"])){
                continue;
            }
            if (self::TYPE_WX_SHOP == $item["type"]){
                if (!isset($item["data"]) || !isset($item["data"]["productId"])){
                    continue;
                }
                if ($wxshopNum>=10){
                    continue;
                }
                $wxshopNum++;
                $pData  = $this->doProduct($item["data"]["productId"]);
                if($pData !== false){
                    $item["data"] = $pData;
                }
            }
            $productsNew[] = $item;
        }
        $productData["products"] = $productsNew;
        return $this->jsonReturn($productData);
    }

    public function select()
    {
        if (!isset($this->body["_plugin"])) {
            $plugin = ["name"=>"shop"];
            $this->body["_plugin"] = $plugin;
        }

        $products = $this->getParams('products');

        $this->selectWxshop($products);

        $productData["products"] = $products;
        return $this->jsonReturn($productData);
    }


    private function selectWxShop( &$products){
        $qrCode = "";
        $setting = app()->make(PluginSettings::class)->getSettingRecord($this->tomId);
        if (!empty($setting) && !empty($setting["public_value"]["wxQrcode"])){
            if(!empty($setting["public_value"]["checkSiteUrl"]) && isset($setting["public_value"]["checkSiteUrl"]["wxQrcode"])){
                $img = $setting["public_value"]["wxQrcode"];
                $isRemote = $setting["public_value"]["checkSiteUrl"]["wxQrcode"];
                $qrCode = $this->siteUrlSplicing($img,$isRemote);
            }else{
                $qrCode = $setting["public_value"]["wxQrcode"];
            }
        }

        $serializer = $this->app->make(AttachmentSerializer::class);

        foreach ($products as &$item){
            if (!isset($item["type"]) || self::TYPE_WX_SHOP != $item["type"]) {
                continue;
            }
            if (!isset($item["data"])) {
                continue;
            }

            if (!isset($item["data"]["attachFilePath"])
                || empty($item["data"]["attachFilePath"])
                || !isset($item["data"]["attachFileName"])
                || empty($item["data"]["attachFileName"]) ) {
                $item["data"]["detailQrcode"] = $qrCode;
            }else{
                $attachment = Attachment::build(
                    1,
                    Attachment::TYPE_OF_FILE,
                    $item["data"]["attachFileName"],
                    $item["data"]["attachFilePath"],
                    $item["data"]["attachFileName"],
                    0,
                    0,
                    $item["data"]["isRemote"],
                    Attachment::APPROVED,
                    "",
                    0,
                    0,
                    0
                );
                $attachmentData = $serializer->getBeautyAttachment($attachment);
                $item["data"]["detailQrcode"] = $attachmentData['url'];
            }

            //删掉不用的字段
            unset($item["data"]["attachFilePath"]);
            unset($item["data"]["attachFileName"]);
            unset($item["data"]["isRemote"]);
        }
    }


    private function doProduct($productId){
        $config = app()->make(PluginSettings::class)->getSettingRecord($this->tomId);
        if (empty($config) || empty($config["public_value"]["wxAppId"])){
            return false;
        }
        $wxAppId = $config["public_value"]["wxAppId"];

        list($result,$accssToken) = $this->getAccessToken($this->tomId);
        if ($result !== 0){
            return false;
        }

        $productId =  (string)$productId;
        $productInfo = $this->getProductInfo($accssToken, $productId);
        if (empty($productInfo)){
            return false;
        }
        $imgUrl = "";
        if (count($productInfo["head_img"])>0){
            $imgUrl=$productInfo["head_img"][0];
        }
        $name = $productInfo["title"];
        $price = $productInfo["min_price"]/100.0;
        $productIdTemp = $productInfo["product_id"];
        $path = $productInfo["path"]; //微信内部url, plugin-private:

        /** @var ShopProducts $productOld */
        $productOld = ShopProducts::query()->where("app_id",$wxAppId)
            ->where("product_id",$productId)->first();
        if (empty($productOld)){
            //拉取二维码
            list($filePath, $fileName, $isRemote) = $this->getProductQrCode($this->tomId,$path);

            $productOld = new ShopProducts();
            $productOld->app_id = $wxAppId;
            $productOld->product_id = $productId;
            $productOld->title = $name;
            $productOld->image_path = $imgUrl;
            $productOld->price = (string)$price;
            $productOld->path = $path;
            $productOld->detail_url = $path;
            $productOld->detail_qrcode = $filePath.$fileName;
            $productOld->is_remote = $isRemote;
            $productOld->attach_file_path = $filePath;
            $productOld->attach_file_name = $fileName;
            $productOld->detail_scheme = $this->getSchemeProduct($this->tomId,$path);
            $productOld->save();
        }else{
            $productOld->title = $name;
            $productOld->image_path = $imgUrl;
            $productOld->price = (string)$price;
            $productOld->path = $path;
            $productOld->detail_url = $path;
            if (empty($productOld->attach_file_path)){
                list($filePath,$fileName,$isRemote) = $this->getProductQrCode($this->tomId,$path);
                $productOld->detail_qrcode = $filePath.$fileName;
                $productOld->is_remote = $isRemote;
                $productOld->attach_file_path = $filePath;
                $productOld->attach_file_name = $fileName;
                $productOld->detail_scheme = $this->getSchemeProduct($this->tomId,$path);
            }
            $productOld->save();
        }

        $resultDataTemp = [];
        $resultDataTemp["id"] =  $productOld["id"];
        $resultDataTemp["appId"] =  $productOld["app_id"];
        $resultDataTemp["productId"] =  $productOld["product_id"];
        $resultDataTemp["title"] =  $productOld["title"];
        $resultDataTemp["imagePath"] =  $productOld["image_path"];
        $resultDataTemp["price"] =  $productOld["price"];
        $resultDataTemp["path"] =  $productOld["path"];
        $resultDataTemp["detailUrl"] =  $productOld["detail_url"];
        $resultDataTemp["detailQrcode"] =  $productOld["detail_qrcode"];
        $resultDataTemp["detailScheme"] =  $productOld["detail_scheme"];
        $resultDataTemp["isRemote"] =  $productOld["is_remote"];
        $resultDataTemp["attachFilePath"] = $productOld["attach_file_path"];
        $resultDataTemp["attachFileName"] = $productOld["attach_file_name"];

        return $resultDataTemp;
    }
}
