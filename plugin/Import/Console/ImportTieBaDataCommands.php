<?php


namespace Plugin\Import\Console;

use App\Import\ImportDataTrait;
use Discuz\Base\DzqCommand;
use Plugin\Import\Platform\TieBa;

class ImportTieBaDataCommands extends DzqCommand
{
    use ImportDataTrait;
    protected $signature = 'importData:insertTieBaData {--topic=} {--number=} {--cookie=} {--auto=} {--type=} {--interval=} {--month=} {--week=} {--day=} {--hour=} {--minute=}';
    protected $description = '执行一个脚本命令,控制台执行[php disco importData:insertTieBaData]';

    protected function main()
    {
        $optionData = [
            'topic' => $this->option('topic'),
            'number' => (int) $this->option('number'),
            'cookie' => $this->option('cookie'),
            'auto' => $this->option('auto'),
            'type' => $this->option('type') ?? 0,
            'interval' => $this->option('interval') ?? 0,
            'month' => $this->option('month') ?? 0,
            'week' => $this->option('week') ?? 0,
            'day' => $this->option('day') ?? 0,
            'hour' => $this->option('hour') ?? 0,
            'minute' => $this->option('minute') ?? 0
        ];

        $this->importDataMain($optionData);
        exit;
    }

    public function getPlatformData($parameter)
    {
        $platform = new TieBa();
        $data = $platform->main($parameter['topic'], $parameter['number'], $parameter['cookie']);
        return $data;
    }
}