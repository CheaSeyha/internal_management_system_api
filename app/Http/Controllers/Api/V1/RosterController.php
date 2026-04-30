<?php

namespace App\Http\Controllers\Api\V1;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RosterRequest;
use App\Services\RosterService;
use Illuminate\Http\Request;

class RosterController extends Controller
{

    protected ResponseHelper $responseHelper;
    protected RosterService $roster_service;
    public function __construct(ResponseHelper $responseHelper, RosterService $roster_service)
    {
        $this->responseHelper = $responseHelper;
        $this->roster_service = $roster_service;
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
        return $this->roster_service->createOrUpdateRoster($request->all());
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
