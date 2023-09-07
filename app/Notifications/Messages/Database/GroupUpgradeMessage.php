<?php

namespace App\Notifications\Messages\Database;

use App\Models\User;
use Discuz\Notifications\Messages\SimpleMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * 用户角色升级通知
 */
class GroupUpgradeMessage extends SimpleMessage
{
    protected $actor;

    protected $data;

    public function __construct()
    {
        //
    }

    public function setData(...$parameters)
    {
        // 解构赋值
        [$firstData, $actor, $data] = $parameters;
        // set parent tpl data
        $this->firstData = $firstData;

        $this->actor = $actor;
        $this->data = $data;

        $this->render();
    }

    protected function titleReplaceVars()
    {
        return [];
    }

    public function contentReplaceVars($data)
    {
        $newGroup = $data['new_group'];
        $nickname = strlen($this->actor->nickname) < User::NICKNAME_LIMIT_LENGTH ? $this->actor->nickname :
            Str::substr($this->actor->nickname, 0, User::NICKNAME_LIMIT_LENGTH) . '...';

        return [
            $nickname,
            $newGroup
        ];
    }

    public function render()
    {
        $build = [
            'title' => $this->getTitle(),
            'content' => $this->getContent($this->data),
            'raw' => Arr::get($this->data, 'raw'),
        ];

        Arr::set($build, 'raw.tpl_id', $this->firstData->id);

        return $build;
    }

}
