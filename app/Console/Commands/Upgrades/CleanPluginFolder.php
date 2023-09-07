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

namespace App\Console\Commands\Upgrades;


use Discuz\Common\Utils;
use Discuz\Console\AbstractCommand;

class CleanPluginFolder extends AbstractCommand
{
    protected $signature = 'clean:plugin';

    protected $description = '清理冗余的错误插件信息';

    protected function handle()
    {
        
    }

    function copyDir($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while ($file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . DIRECTORY_SEPARATOR . $file)) {
                    $this->copyDir($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                } else {
                    copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
        closedir($dir);
    }

    function removeDir($path)
    {
        if (empty($path) || !$path) {
            return false;
        }
        return is_file($path) ? @unlink($path) : array_map([$this, __FUNCTION__], glob($path . '/*')) == @rmdir($path);
    }
}
