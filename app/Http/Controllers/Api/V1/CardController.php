<?php

namespace App\Http\Controllers\Api\V1;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Services\CardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    protected $card_service;
    protected $response_helper;
    //
    public function __construct(CardService $card_service, ResponseHelper $response_helper)
    {
        $this->card_service = $card_service;
        $this->response_helper = $response_helper;
    }

    public function create_card(CardRequest $request)
    {
        try {
            $cardData = $request->validated();
            $cardData['user_id'] = Auth::user()->id;

            $response = $this->card_service->createCard($cardData);

            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to store card.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getImage($id)
    {
        try {
            // Use your existing service method
            return $this->card_service->getCardImageResponse($id);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch card image.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllCards()
    {
        try {
            $response = $this->card_service->getAllCards();
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch cards.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cardsFilter(Request $request)
    {
        $validated = $request->validate([
            'card_name' => 'string|nullable',
            'filterBlocks' => 'array|nullable',
            'filterBlocks.*' => 'string',
            'filterCardTypes' => 'array|nullable',
            'filterCardTypes.*' => 'string',
            'month' => 'integer|nullable',
            'year' => 'integer|nullable',
        ]);

        try {
            $response = $this->card_service->cardsFilter(
                $validated['card_name'] ?? null,
                $validated['filterBlocks'] ?? [],
                $validated['filterCardTypes'] ?? [],
                $validated['month'] ?? null,
                $validated['year'] ?? null,
            );

            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to search cards.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function getAllCardType()
    {

        try {
            $response = $this->card_service->getAllCardType();
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to search cards.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function searchCard(Request $request)
    {
        if ($request->has('card_name')) {
            $validate = $request->validate([
                'card_name' => 'required|string',
                'card_type' => 'nullable|string',
            ]);

            try {
                $response = $this->card_service->getCardByNameAndCardType(
                    $validate['card_name'],
                    $validate['card_type'] ?? null // ✅ FIXED HERE
                );

                return response()->json($response->getData(), $response->getStatusCode());
            } catch (\Throwable $th) {
                return $this->response_helper->fail('Error', $th->getMessage());
            }
        }


        if ($request->has('type_card_id')) {
            $validate = $request->validate([
                'type_card_id' => "required | string",
                "card_type" => "required | string"
            ]);

            try {
                $response = $this->card_service->getCardByIDAndCardType($validate['type_card_id'], $validate['card_type']);

                return response()->json($response->getData(), $response->getStatusCode());
            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'Failed to fetch cards.',
                    'error' => $th->getMessage()
                ], 500);
            }
        }

        return $this->response_helper->fail('To Search Card Required Card_name Or Card ID');
    }

    public function editCard(Request $request, $type_card_id, $card_type)
    {
        try {
            $res = $this->card_service->editCard($type_card_id, $card_type, $request->all());

            return response()->json($res->getData(), $res->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch cards.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteCard(Request $request, $type_card_id, $card_type)
    {

        try {
            $res = $this->card_service->deleteCardByIDAndCardType($type_card_id, $card_type);

            return response()->json($res->getData(), $res->getStatusCode());
        } catch (\Throwable $th) {
            //throw $th;
        }
    }


    public function createCardType(Request $request)
    {
        try {
            // Validate only required & string; uniqueness handled in service
            $request->validate([
                'name' => 'required|string',
            ]);

            // Call service method
            $res = $this->card_service->createCardType($request->name);

            // Return JSON using the service response object
            return response()->json($res->getData(), $res->getStatusCode());
        } catch (\Throwable $th) {
            // Handle unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function cards_summary(Request $request)
    {
        $request->validate([
            'start_date' => 'required|string',
            'end_date' => 'required|string',
        ]);

        try {
            // Parse dates to Y-m-d (DB format)
            $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d');

            $res = $this->card_service->cards_summary($startDate, $endDate);

            return response()->json($res->getData(), $res->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getDuplicateCards(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer',
            'year' => 'required|integer',
        ]);

        try {
            $response = $this->card_service->getDuplicateCards(
                $validated['month'],
                $validated['year']
            );
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch duplicate cards.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkCardExist(CardRequest $request)
    {
        $card_validated = $request->validated();


        try {
            $response = $this->card_service->checkCardExist($card_validated);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch duplicate cards.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
