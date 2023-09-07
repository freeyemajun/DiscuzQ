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

namespace App\Models;

use App\Common\CacheKey;
use Carbon\Carbon;
use Discuz\Base\DzqModel;
use Discuz\Cache\CacheManager;
use Illuminate\Support\Arr;

/**
 * @property int $id
 * @property string $app_id
 * @property string $app_name
 * @property int $type
 * @property string $private_value
 * @property string $public_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PluginSettings extends DzqModel
{
    protected $table = 'plugin_settings';

    protected $settings = null;

    /**
     * @var CacheManager
     */
    protected $cache;

    public function __construct()
    {
        $this->settings = [];
        $this->cache = app('cache');
    }

    public function allData()
    {
        if (!empty($this->settings)) {
            return $this->settings;
        }

        $settings = $this->cache->sear(
            CacheKey::PLUGIN_SETTINGS,
            function () {
                return $this->getAllFromDatabase();
            }
        );

        $this->settings = $settings;

        return $this->settings;
    }

    protected function getAllFromDatabase()
    {
        $settings = PluginSettings::all()->keyBy('app_id')->toArray();
        return $settings;
    }

    public function getData($appId)
    {
        return Arr::get($this->allData(), $appId);
    }

    public function setData($appId, $name, $type, $privateValue, $publicValue)
    {
        $pluginSetting = PluginSettings::query()->where(['app_id' => $appId])->first();
        if (empty($pluginSetting)) {
            $pluginSetting = new PluginSettings();
        }else{
            //检查星号,则用原来的
            if(!empty($pluginSetting->private_value)){
                $privateValueOld = json_decode($pluginSetting->private_value,true);
                foreach ($privateValue as $key=>$value){
                    if (is_string($value)){
                        $starNum = substr_count($value,"*");
                        if (strlen($value)!=0 && strlen($value) == $starNum && isset($privateValueOld[$key])){
                            $privateValue[$key] = $privateValueOld[$key];
                        }
                    }
                }
                $privateValue = array_merge($privateValueOld,$privateValue);
            }
            if (!empty($pluginSetting->public_value)){
                $publicValueOld = json_decode($pluginSetting->public_value,true);
                $publicValue = array_merge($publicValueOld,$publicValue);
            }
        }
        $pluginSetting->app_id = $appId;
        $pluginSetting->app_name = $name;
        $pluginSetting->type = $type;

        $pluginSetting->private_value = json_encode($privateValue, 256);
        $pluginSetting->public_value = json_encode($publicValue, 256);

        if (!$pluginSetting->save()) {
            return false;
        }

        $this->cache->delete(CacheKey::PLUGIN_SETTINGS);
        $this->settings = [];
        $this->allData();
        return true;
    }

    public function getSettingRecord($appId)
    {
        $setting = $this->getData($appId);
        if (empty($setting)) {
            return [];
        }

        if (!empty($setting['private_value'])) {
            $setting['private_value'] = json_decode($setting['private_value'], true);
        } else {
            $setting['private_value'] = [];
        }
        if (!empty($setting['public_value'])) {
            $setting['public_value'] = json_decode($setting['public_value'], true);
        } else {
            $setting['public_value'] = [];
        }

        return $setting;
    }

    public function getAllSettingRecord()
    {
        $appSettingMap = $this->allData();
        foreach ($appSettingMap as $key=>&$setting) {
            if (!empty($setting['private_value'])) {
                $setting['private_value'] = json_decode($setting['private_value'], true);
            } else {
                $setting['private_value'] = [];
            }
            if (!empty($setting['public_value'])) {
                $setting['public_value'] = json_decode($setting['public_value'], true);
            } else {
                $setting['public_value'] = [];
            }
        }
        return $appSettingMap;
    }
}
