<?php

namespace App\Repository;

use App\Models\Isp;

class ISPRepository
{
    public function getAllISPs()
    {
        return Isp::all();
    }

    public function addISP($data)
    {
        return Isp::create($data);
    }

    public function updateISP($id, $data)
    {
        $isp = Isp::find($id);
        if ($isp) {
            $isp->update($data);
            return $isp;
        }
        return null;
    }

    public function deleteISP($id)
    {
        $isp = Isp::find($id);
        if ($isp) {
            return $isp->delete();
        }
        return false;
    }
}
