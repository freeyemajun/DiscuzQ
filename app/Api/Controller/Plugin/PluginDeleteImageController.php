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

namespace App\Api\Controller\Plugin;

use Discuz\Base\DzqAdminController;
use Discuz\Contracts\Setting\SettingsRepository;
use Illuminate\Contracts\Filesystem\Factory;

class PluginDeleteImageController extends DzqAdminController
{
    /**
     * @param Factory $filesystem
     * @param SettingsRepository $settings
     */
    public function __construct(Factory $filesystem, SettingsRepository $settings)
    {
        $this->filesystem = $filesystem;
        $this->settings = $settings;
    }

    public function main()
    {
        $urlPath = $this->inPut('url');
        $this->outPut(0, '');
    }

    /**
     * @param string $file
     */
    private function remove($file)
    {
        $filesystem = $this->filesystem->disk('public');

        if ($filesystem->has($file)) {
            $filesystem->delete($file);
        }
    }
}
