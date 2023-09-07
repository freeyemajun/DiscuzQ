<?php

namespace App\Import;

trait PlatformTrait
{
    public function getHtmlLabel($match, $content)
    {
        preg_match_all($match, $content,$matches);
        return $matches;
    }

    public function deleteImportLockFile()
    {
        $publicPath = public_path();
        $importDataLockFilePath = $publicPath . DIRECTORY_SEPARATOR . 'importDataLock.conf';
        if (file_exists($importDataLockFilePath)) {
            @unlink($importDataLockFilePath);
        }
        return true;
    }

    public function curlGet($url, $cookie, $headers = [], $port = 80)
    {
        $ch = curl_init();
        $header = array();
        $header[] = 'Content-Type:application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($port !== 80) {
            curl_setopt($ch, CURLOPT_PORT, $port);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36");
        curl_setopt($ch, CURLOPT_HEADER, 0);//设定是否输出页面内容
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);       //链接超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);       //设置超时时间
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $filecontent = curl_exec($ch);
        curl_close($ch);

        return $filecontent;
    }
}