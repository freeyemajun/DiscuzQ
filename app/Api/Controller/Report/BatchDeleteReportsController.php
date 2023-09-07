<?php
namespace App\Api\Controller\Report;

use App\Models\Report;
use App\Common\ResponseCode;
use App\Repositories\UserRepository;
use Discuz\Base\DzqAdminController;

class BatchDeleteReportsController extends DzqAdminController
{
    public function main()
    {
        $idString = $this->inPut('ids');
        if (empty($idString)) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '缺少必要参数', '');
        }
        $ids = explode(',', $idString);

        if (count($ids) > 100) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '批量添加超过限制', '');
        }

        foreach ($ids as $id) {
            if ($id < 1) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '', '');
            }
        }

        $result = Report::query()->whereIn('id', $ids)->delete();
        if (!$result) {
            app('log')->info('requestId：' . $this->requestId . '-' . '删除举报记录出错，ID为： ' . $idString);
            $this->outPut(ResponseCode::DB_ERROR, '', '');
        }


        $this->outPut(ResponseCode::SUCCESS, '', '');
    }
}
