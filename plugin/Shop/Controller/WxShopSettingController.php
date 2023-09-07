<?php


namespace Plugin\Shop\Controller;

use App\Common\ResponseCode;
use App\Common\Utils;
use App\Models\PluginSettings;
use Discuz\Base\DzqAdminController;

class WxShopSettingController extends DzqAdminController
{
    use WxShopTrait;

    public function main()
    {
        $appid = Utils::getPluginAppId();
        $url = "";
        /** @var PluginSettings $pluginSettings */
        $pluginSettings = app()->make(PluginSettings::class);
        $settingData = $pluginSettings->getSettingRecord($appid);
        if (!empty($settingData)){
            if (isset($settingData["public_value"]["wxAppId"])
                && !empty($settingData["public_value"]["wxAppId"])
                && isset($settingData["private_value"]["wxAppSecret"])
                && !empty($settingData["private_value"]["wxAppSecret"])){

                list($url,$isRemote) = $this->getShopQrCode($appid);
                if (empty($url)){
                    $this->outPut(ResponseCode::INVALID_PARAMETER,'生成二维码失败');
                }
                $settingData["public_value"]["wxQrcode"] = $url;
                $settingData["public_value"]["checkSiteUrl"]["wxQrcode"] = $isRemote;

                $pluginSettings->setData($appid, $settingData["app_name"], $settingData["type"],
                    $settingData["private_value"], $settingData["public_value"]);
            }
        }
        $data = [];
        $data['wxQrCode'] = $url;

        $this->outPut(0,'',$data);
    }
}
