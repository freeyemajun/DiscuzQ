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

namespace App\Import;

use App\Models\Thread;

trait ImportLockFileTrait
{
    public function getLockFileContent($lockPath)
    {
        if (!file_exists($lockPath)) {
            $lockFileContent = $this->changeLockFileContent($lockPath, 0, 0, Thread::IMPORT_WAITING, '');
        } else {
            $lockFileContent = file_get_contents($lockPath);
            $lockFileContent = json_decode($lockFileContent, true);
            if (isset($lockFileContent['runtime'])) {
                $lockFileContent['runtime'] = $lockFileContent['startCrawlerTime'] ? floor((time() - $lockFileContent['startCrawlerTime'])%86400/60) : 0;
            }
        }

        return $lockFileContent;
    }

    public function changeLockFileContent($lockPath, $startCrawlerTime, $progress, $status, $topic, $totalDataNumber = 0)
    {
        if (!file_exists($lockPath)) {
            touch($lockPath);
        }
        $data = [
            'status' => $status, // 0 未开始;1 进行中;2 正常结束;3 异常结束;4 超时
            'progress' => floor((string)$progress),
            'startCrawlerTime' => $startCrawlerTime,
            'runtime' => 0,
            'topic' => $topic,
            'totalDataNumber' => (int) $totalDataNumber
        ];

        $writeCrawlerSplQueueLock = fopen($lockPath, 'w');
        fwrite($writeCrawlerSplQueueLock, json_encode($data));
        return $data;
    }

    // 自动导入参数校验
    public function checkAutoImportParameters($data, $platform)
    {
        if (empty($data['topic'])) {
            throw new \Exception('缺少参数topic');
        }

        if (empty($data['number'])) {
            throw new \Exception('缺少参数number');
        }

        if (empty($data['type'])) {
            throw new \Exception('缺少参数type');
        }

        if (empty($data['interval'])) {
            throw new \Exception('缺少参数interval');
        }

        if (empty($data['hour'])) {
            throw new \Exception('缺少参数hour');
        }

        if (!in_array(
            $data['type'],
            [Thread::AUTO_IMPORT_OF_YEAR, Thread::AUTO_IMPORT_OF_MONTH, Thread::AUTO_IMPORT_OF_WEEKS, Thread::AUTO_IMPORT_OF_DAY]
        )) {
            throw new \Exception(' 未知类型type');
        }

        if ($data['type'] == Thread::AUTO_IMPORT_OF_YEAR) {
            if (empty($data['month']) || empty($data['day'])) {
                throw new \Exception('缺少参数month/day');
            }
        }

        if ($data['type'] == Thread::AUTO_IMPORT_OF_MONTH) {
            if (empty($data['day'])) {
                throw new \Exception('缺少参数day');
            }
        }

        if ($data['type'] == Thread::AUTO_IMPORT_OF_WEEKS) {
            if (empty($data['week'])) {
                throw new \Exception('缺少参数week');
            }
        }

        foreach ($data as $key => $value) {
            if (($key != 'topic' && $key != 'cookie' && $key != 'token' && $key != 'userAgent') && !empty($value)) {
                $this->checkPositiveInteger($key, $value);
            }
        }

        $status = 0;
        if (file_exists($this->autoImportDataLockFilePath)) {
            $autoImportDataLockFileContent = file_get_contents($this->autoImportDataLockFilePath);
            if (!empty($autoImportDataLockFileContent)) {
                $status++;
            }
        } else {
            touch($this->autoImportDataLockFilePath);
        }

        $data['lastImportTime'] = 0;
        $data['lastImportStatus'] = Thread::AUTO_IMPORT_HAVE_NOT_BEGUN;
        $data['platform'] = $platform;
        $writeAutoImportDataLockFile = fopen($this->autoImportDataLockFilePath, 'w');
        fwrite($writeAutoImportDataLockFile, json_encode($data));
        $status++;
        return $status;
    }

    // 检查正整数、数值范围
    private function checkPositiveInteger($key, $number)
    {
        if ($number <= 0) {
            throw new \Exception('数值必须大于0');
        }
        if (floor($number) != $number) {
            throw new \Exception('数值必须为整数');
        }
        if ($key == 'month' && $number > 12) {
            throw new \Exception('月份数值不符合范围规范');
        }
        if ($key == 'day' && $number > 31) {
            throw new \Exception('日期数值不符合范围规范');
        }
        if ($key == 'hour' && $number > 24) {
            throw new \Exception('小时数值不符合范围规范');
        }
        if ($key == 'minute' && $number > 60) {
            throw new \Exception('分钟数值不符合范围规范');
        }
        return true;
    }

    // 比较自动导入时间，获取自动导入相关信息
    public function getAutoImportData($fileData)
    {
        $currentYear = (int)date('Y'); // 当前年份
        $currentMonth = (int)date('m'); // 当前月份
        $currentDay = (int)date('d'); // 当前日期
        $currentHour = (int)date('H');  // 当前小时
        $weekArray = [7, 1, 2, 3, 4, 5, 6];
        $currentWeek = $weekArray[(int)date('w')]; // 当前星期几

        if ($fileData['type'] == Thread::AUTO_IMPORT_OF_YEAR) {
            // 年-自动导入
            if ($currentMonth == $fileData['month'] &&
                $currentDay == $fileData['day'] &&
                $currentHour == $fileData['hour']) {
                if (empty($fileData['lastImportTime'])) {
                    if ($this->checkMinutes($fileData)) {
                        return $fileData;
                    }
                } else {
                    if ((int)date('Y', $fileData['lastImportTime']) + $fileData['interval'] == $currentYear &&
                        $fileData['lastImportStatus'] == Thread::AUTO_IMPORT_HAVE_NOT_BEGUN) {
                        if ($this->checkMinutes($fileData)) {
                            return $fileData;
                        }
                    }
                }
            } else {
                $this->changeLastImportFileContent($fileData['lastImportTime'], Thread::AUTO_IMPORT_HAVE_NOT_BEGUN);
            }
        } elseif ($fileData['type'] == Thread::AUTO_IMPORT_OF_MONTH) {
            // 月-自动导入
            if ($currentDay == $fileData['day'] && $currentHour == $fileData['hour']) {
                if (empty($fileData['lastImportTime'])) {
                    if ($this->checkMinutes($fileData)) {
                        return $fileData;
                    }
                } else {
                    if ((int)date('m', $fileData['lastImportTime']) + $fileData['interval'] == $currentMonth &&
                        $fileData['lastImportStatus'] == Thread::AUTO_IMPORT_HAVE_NOT_BEGUN) {
                        if ($this->checkMinutes($fileData)) {
                            return $fileData;
                        }
                    }
                }
            } else {
                $this->changeLastImportFileContent($fileData['lastImportTime'], Thread::AUTO_IMPORT_HAVE_NOT_BEGUN);
            }
        } elseif ($fileData['type'] == Thread::AUTO_IMPORT_OF_WEEKS) {
            // 周-自动导入
            if ($currentWeek == $fileData['week'] && $currentHour == $fileData['hour']) {
                if (empty($fileData['lastImportTime'])) {
                    if ($this->checkMinutes($fileData)) {
                        return $fileData;
                    }
                } else {
                    if (floor((time()-$fileData['lastImportTime'])/86400) == $fileData['interval'] * 7 &&
                        $fileData['lastImportStatus'] == Thread::AUTO_IMPORT_HAVE_NOT_BEGUN) {
                        if ($this->checkMinutes($fileData)) {
                            return $fileData;
                        }
                    }
                }
            } else {
                $this->changeLastImportFileContent($fileData['lastImportTime'], Thread::AUTO_IMPORT_HAVE_NOT_BEGUN);
            }
        } elseif ($fileData['type'] == Thread::AUTO_IMPORT_OF_DAY) {
            // 天-自动导入
            if ($currentHour == $fileData['hour']) {
                if (empty($fileData['lastImportTime'])) {
                    if ($this->checkMinutes($fileData)) {
                        return $fileData;
                    }
                } else {
                    if ((int)date('d', $fileData['lastImportTime']) + $fileData['interval'] == $currentDay &&
                        $fileData['lastImportStatus'] == Thread::AUTO_IMPORT_HAVE_NOT_BEGUN) {
                        if ($this->checkMinutes($fileData)) {
                            return $fileData;
                        }
                    }
                }
            } else {
                $this->changeLastImportFileContent($fileData['lastImportTime'], Thread::AUTO_IMPORT_HAVE_NOT_BEGUN);
            }
        }
        return [];
    }

    // 比较分钟
    private function checkMinutes($fileData)
    {
        $currentMinute = (int)date('i');  // 当前分钟
        if (empty($fileData['minute']) ||
            ($fileData['minute'] > 0 && $currentMinute >= $fileData['minute'])) {
            return true;
        }
        return false;
    }

    // 更新 自动导入时间
    public function changeLastImportFileContent($time, $lastImportStatus)
    {
        $data = $this->getLockFileContent($this->autoImportDataLockFilePath);
        $data['lastImportTime'] = $time;
        $data['lastImportStatus'] = $lastImportStatus;
        $writeAutoImportDataLockFile = fopen($this->autoImportDataLockFilePath, 'w');
        fwrite($writeAutoImportDataLockFile, json_encode($data));
        return true;
    }
}
