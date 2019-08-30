<?php

namespace App\Services\Admin;

use App\Dto\ResponseDto;
use App\Libs\Utils\ErrorMsg;
use App\Repositories\Admin\ProjectRepository;
use App\Services\BaseService;

class ProjectService extends BaseService
{
    protected $projectRepository;

    public function __construct(
        ProjectRepository $projectRepository
    ) {
        $this->projectRepository = $projectRepository;
    }

    public function getProjectList($params) {
        $data_arr = $this->projectRepository->getProjectList();
        return $data_arr;
    }
}
