<?php


namespace App\Api\Controller\Emoji;

use App\Common\ResponseCode;
use App\Models\Emoji;
use App\Repositories\UserRepository;
use Discuz\Base\DzqAdminController;

class ListAdminEmojiController extends DzqAdminController
{
    public function main()
    {
        $emojis = Emoji::getEmojiListForController($this->request);
        $result = $this->camelData($emojis);
        $this->outPut(ResponseCode::SUCCESS, '', $result);
    }
}
