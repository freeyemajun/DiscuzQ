<?php


namespace Plugin\Shop\Controller;

use App\Common\ResponseCode;
use App\Common\Utils;
use App\Models\PluginGroupPermission;
use App\Repositories\UserRepository;
use Discuz\Auth\Exception\NotAuthenticatedException;
use Discuz\Base\DzqController;

class WxShopListController extends DzqController
{
    use WxShopTrait;

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        if ($this->user->isAdmin()){
            return true;
        }
        if ($this->user->isGuest()) {
            throw new NotAuthenticatedException;
        }


        $appid = Utils::getPluginAppId();
        $groupId = $this->user->groupId;
        if(PluginGroupPermission::hasPluginPermission($appid,$groupId)){
            return true;
        }

        return false;
    }


    public function main()
    {
        $page = intval($this->inPut('page'));
        $page = $page ?: 1;
        $perPage = intval($this->inPut('perPage'));
        $perPage = $perPage ?: 10;

       $appid = Utils::getPluginAppId();
        list($result,$accssToken) = $this->getAccessToken($appid);
        if ($result !== 0){
            $this->outPut($result,$accssToken);
        }
        $data = $this->getShopList($accssToken,$page,$perPage);
        if (empty($data) || !isset($data["spus"])){
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND,"没有商品");
        }

        $productList = [];
        foreach ($data["spus"] as $one){
            $img = "";
            if (count($one["head_img"])>0){
                $img=$one["head_img"][0];
            }
            $price = $one["min_price"]/100.0;

            $oneGoods = [];
            $oneGoods["productId"] = (string)$one["product_id"];
            $oneGoods["title"] = $one["title"];
            $oneGoods["imagePath"] = $img;
            $oneGoods["price"] = $price;

            $productList[] = $oneGoods;
        }

        $url = $this->request->getUri();
        $port = $url->getPort();
        $port = $port == null ? '' : ':' . $port;
        $path = $url->getScheme() . '://' . $url->getHost() . $port . $url->getPath() . '?';
        parse_str($url->getQuery(), $query);
        $queryFirst = $queryNext = $queryPre = $query;
        $queryFirst['page'] = 1;
        $queryPre['page'] = $page <= 1 ? 1 : $page - 1;
        $queryNext['page'] = $page + 1;



        $dataResult = [
            "currentPage"=>$page,
            "perPage"=>$perPage,
            "pageLength"=>count($productList), //本页条数
            "totalCount"=>$data["total_num"],
            "totalPage"=>$data["total_num"] % $perPage == 0 ? $data["total_num"] / $perPage : intval($data["total_num"] / $perPage) + 1,
            "firstPageUrl"=>urldecode($path . http_build_query($queryFirst)),
            "prePageUrl"=>urldecode($path . http_build_query($queryPre)),
            "nextPageUrl"=>urldecode($path . http_build_query($queryNext)),
            "pageData"=>$productList
        ];


       $this->outPut(0,'',$dataResult);
    }
}
