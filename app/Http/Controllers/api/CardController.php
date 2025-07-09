<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Services\CardService;
use App\Helper\ResponseHelper;
use Illuminate\Http\Request;

class CardController extends Controller
{
    protected $cardService;
    protected $responseHelper;

    public function __construct(CardService $cardService, ResponseHelper $responseHelper)
    {
        $this->cardService = $cardService;
        $this->responseHelper = $responseHelper;
    }

    public function index()
    {
        $response = $this->cardService->getAllCards();
        return response()->json($response->getData(), $response->getStatusCode());
    }

    public function store(CardRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id(); // Set current user as owner
        
        $response = $this->cardService->store($data);
        return response()->json($response->getData(), $response->getStatusCode());
    }

    public function show($id)
    {
        $response = $this->cardService->getCardById($id);
        return response()->json($response->getData(), $response->getStatusCode());
    }

    public function update(CardRequest $request, $id)
    {
        $data = $request->validated();
        $response = $this->cardService->updateCard($id, $data);
        return response()->json($response->getData(), $response->getStatusCode());
    }

    public function destroy($id)
    {
        $response = $this->cardService->deleteCard($id);
        return response()->json($response->getData(), $response->getStatusCode());
    }

    public function getImage($id)
    {
        return $this->cardService->getCardImageResponse($id);
    }
}