<?php


namespace Plugin\Shop\Model;

use Carbon\Carbon;
use Discuz\Base\DzqModel;


/**
 * @property int $id
 * @property string $app_id
 * @property string $product_id
 * @property string $title
 * @property string $image_path
 * @property string $price
 * @property string $path
 * @property string $detail_url
 * @property string $detail_qrcode
 * @property string $attach_file_path
 * @property string $attach_file_name
 * @property string $detail_scheme
 * @property int $is_remote
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ShopProducts  extends DzqModel
{
    protected $table = "plugin_shop_wxshop_products";
}
