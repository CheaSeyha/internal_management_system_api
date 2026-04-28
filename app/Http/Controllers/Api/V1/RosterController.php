<?php

namespace App\Http\Controllers\Api\V1;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RosterRequest;
use App\Services\RosterSerivce;
use Illuminate\Http\Request;

class RosterController extends Controller
{

    protected ResponseHelper $responseHelper;
    protected RosterSerivce $roster_serivce;
    public function __construct(ResponseHelper $responseHelper, RosterSerivce $roster_serivce)
    {
        $this->responseHelper = $responseHelper;
        $this->roster_serivce = $roster_serivce;
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RosterRequest $request)
    {
        return $this->roster_serivce->createOrUpdateRoster($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
