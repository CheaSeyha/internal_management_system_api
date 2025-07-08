<?php

namespace App\Http\Controllers\api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Models\Card;
use App\Services\CardService;
use Illuminate\Http\Request;

class CardController extends Controller
{
    protected $cardService;

    protected $responseHelper;
    public function __construct(CardService $cardService, ResponseHelper $responseHelper)
    {
        $this->cardService = $cardService;
        $this->responseHelper = $responseHelper;
        // You can add middleware or other initializations here if needed
        // For example, you might want to apply authentication middleware
        // $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
            $cardResponse = $this->cardService->getAllCards();
            return response()->json($cardResponse->getData(), $cardResponse->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cards',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request) {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(CardRequest $cardRequest)
    {
        try {
            $cardResponse = $this->cardService->store($cardRequest);
            return response()->json($cardResponse->getData(), $cardResponse->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Card creation failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        try {
            $cardResponse = $this->cardService->getCardById($id);
            return response()->json($cardResponse->getData(), $cardResponse->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found',
                'error' => $th->getMessage(),
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        try {
            $cardUpdate = $this->cardService->updateCard($id, $request);
            return response()->json($cardUpdate->getData(), $cardUpdate->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => "Can't Update Card",
                'error' => $th->getMessage(),
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

    }
}
