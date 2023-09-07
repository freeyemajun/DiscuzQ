<?php

namespace Plugin\Import\Console;

use Discuz\Base\DzqCommand;

class StopAutoImportCommands extends DzqCommand
{
    protected $signature = 'autoImport:stop';
    protected $description = '执行一个脚本命令,控制台执行[autoImport:stop]';

    protected function main()
    {
        $publicPath = public_path();
        $autoImportDataLockFilePath = $publicPath . DIRECTORY_SEPARATOR . 'autoImportDataLock.conf';
        if (file_exists($autoImportDataLockFilePath)) {
            @unlink($autoImportDataLockFilePath);
            $this->info('----The automatic import task has been stopped.----');
        } else {
            $this->info("----The automatic import task doesn't exist.----");
        }
        exit;
    }
}