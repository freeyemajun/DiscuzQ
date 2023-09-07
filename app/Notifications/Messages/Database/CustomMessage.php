<?php

/**
 * Copyright (C) 2020 Tencent Cloud.
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

namespace App\Notifications\Messages\Database;

use Discuz\Contracts\Setting\SettingsRepository;
use Discuz\Notifications\Messages\SimpleMessage;
use Illuminate\Support\Arr;

class CustomMessage extends SimpleMessage
{
    protected $actor;

    protected $data;

    /**
     * @var SettingsRepository
     */
    protected $settings;

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    public function setData(...$parameters)
    {
        [$firstData, $actor, $data] = $parameters;
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
        return [];
    }

    public function render()
    {
        $build = [
            'title' => Arr::get($this->data, 'title', '系统通知'),
            'content' => Arr::get($this->data, 'content', ''),
            'threadId'=>Arr::get($this->data, 'threadId', null)
        ];
        Arr::set($build, 'raw.tpl_id', $this->firstData->id);
        return $build;
    }
}
