<?php


namespace Plugin\Import\Console;

use App\Import\ImportDataTrait;
use Discuz\Base\DzqCommand;
use Plugin\Import\Platform\OfficialAccountArticle;

class ImportOfficialAccountArticleDataCommands extends DzqCommand
{
    use ImportDataTrait;
    protected $signature = 'importData:insertOfficialAccountArticleData {--articleUrl=}';
    protected $description = '执行一个脚本命令,控制台执行[php disco importData:insertOfficialAccountArticleData]';

    protected function main()
    {
        $articleUrl = $this->option('articleUrl');
        if (empty($articleUrl)) {
            throw new \Exception('缺少文章链接');
        }

        $optionData = [
            'topic' => 'official account article',
            'articleUrl' => $articleUrl,
            'number' => count(explode(',', $articleUrl))
        ];
        $this->importDataMain($optionData);
        exit;
    }

    public function getPlatformData($parameter)
    {
        $platform = new OfficialAccountArticle();
        $data = $platform->main($parameter['articleUrl']);
        return $data;
    }
}