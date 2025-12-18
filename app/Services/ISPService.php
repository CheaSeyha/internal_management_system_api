<?php

namespace App\Services;

use App\Repository\ISPRepository;

class ISPService
{
    protected $ispRepository;

    public function __construct(ISPRepository $ispRepository)
    {
        $this->ispRepository = $ispRepository;
    }

    public function getAllISPs()
    {
        return $this->ispRepository->getAllISPs();
    }

    public function addISP($data)
    {
        return $this->ispRepository->addISP($data);
    }

    public function updateISP($id, $data)
    {
        return $this->ispRepository->updateISP($id, $data);
    }

    public function deleteISP($id)
    {
        return $this->ispRepository->deleteISP($id);
    }
}
