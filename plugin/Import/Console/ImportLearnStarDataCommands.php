<?php

namespace Plugin\Import\Console;

use App\Import\ImportDataTrait;
use Discuz\Base\DzqCommand;
use Plugin\Import\Platform\LearnStar;

class ImportLearnStarDataCommands extends DzqCommand
{
    use ImportDataTrait;
    protected $signature = 'importData:insertLearnStarData {--topic=} {--number=} {--auto=} {--type=} {--interval=} {--month=} {--week=} {--day=} {--hour=} {--minute=} {--cookie=} {--userAgent=}';
    protected $description = '执行一个脚本命令,控制台执行[php disco importData:insertLearnStarData]';

    protected function main()
    {
        $optionData = [
            'topic' => $this->option('topic'),
            'number' => (int) $this->option('number'),
            'auto' => $this->option('auto'),
            'type' => $this->option('type') ?? 0,
            'interval' => $this->option('interval') ?? 0,
            'month' => $this->option('month') ?? 0,
            'week' => $this->option('week') ?? 0,
            'day' => $this->option('day') ?? 0,
            'hour' => $this->option('hour') ?? 0,
            'minute' => $this->option('minute') ?? 0,
            'cookie' => $this->option('cookie') ?? '',
            'userAgent' => $this->option('userAgent') ?? '',
        ];
        $this->importDataMain($optionData);
        exit;
    }

    public function getPlatformData($parameter)
    {
        $platform = new LearnStar();
        $data = $platform->main($parameter['topic'], $parameter['number'], $parameter['cookie'], $parameter['userAgent']);
        return $data;
    }
}
