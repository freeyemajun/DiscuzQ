<?php


namespace App\Api\Controller\Plugin;

use Discuz\Base\DzqLog;
use Discuz\Contracts\Setting\SettingsRepository;
use Illuminate\Contracts\Filesystem\Factory;

class PluginFileSave
{
    private $fileSystem;

    /** @var SettingsRepository $settings */
    private $settings;

    public function __construct(Factory $fileSystem, SettingsRepository $settings)
    {
        $this->fileSystem = $fileSystem;
        $this->settings = $settings;
    }

    public function saveFile($fileName, string $qrBuff)
    {
        try {
            $path='public/plugin/'.$fileName;
            $isRemote=false;
            // 开启 cos 时，cos放一份
            if ($this->settings->get('qcloud_cos', 'qcloud')) {
                //$qrBuffTemp = clone $qrBuff;
                $this->fileSystem->disk('cos')->put($path, $qrBuff);
                $isRemote = true;
            }
            $this->fileSystem->disk('local')->put($path, $qrBuff);

            return [$path, $isRemote];
        } catch (Exception $e) {
            if (empty($e->validator) || empty($e->validator->errors())) {
                $errorMsg = $e->getMessage();
            } else {
                $errorMsg = $e->validator->errors()->first();
            }
            DzqLog::error('ShopFileSave::saveFile', [], $errorMsg);

            return ['',false];
        }
    }

    public function getFilePath($isRemote, $path)
    {
        if ($isRemote && $this->settings->get('qcloud_cos', 'qcloud')) {
            $isExist = $this->fileSystem->disk('cos')->has($path);
            if ($isExist) {
                $url = $this->fileSystem->disk('cos')->url($path);
                return $url;
            }
        }
        $url = $this->fileSystem->disk('local')->url($path);
        return $url;
    }
}
