<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Repository\CardRepository;
use Illuminate\Support\Facades\Storage;

class CardService
{
    protected $cardRepository;
    protected $responseHelper;

    public function __construct(CardRepository $cardRepository, ResponseHelper $responseHelper)
    {
        $this->cardRepository = $cardRepository;
        $this->responseHelper = $responseHelper;
    }

    public function createCard(array $data)
    {
        $card = $this->cardRepository->store($data);
        return $this->responseHelper->success('Card created successfully', $card, 201);
    }

    public function getAllCards()
    {
        $cards = $this->cardRepository->getAllCards();
        return $this->responseHelper->success('Cards retrieved successfully', $cards, 200);
    }

    public function getCardById($id)
    {
        $card = $this->cardRepository->getCardById($id);
        return $card
            ? $this->responseHelper->success('Card retrieved successfully', $card, 200)
            : $this->responseHelper->fail('Card not found', null, 404);
    }

    public function getCardByIDAndCardType(int $type_card_id, string $card_type,)
    {
        $card = $this->cardRepository->getCardByIDAndCardType($type_card_id, $card_type,);

        return $card
            ? $this->responseHelper->success('Card retrieved successfully', $card, 200)
            : $this->responseHelper->fail('Card not found', null, 404);
    }

    public function getCardByNameAndCardType(string $card_name, ?string $card_type = null)
    {
        $card = $this->cardRepository->getCardByNameAndCardType($card_name, $card_type);

        return $card
            ? $this->responseHelper->success('Card retrieved successfully', $card, 200)
            : $this->responseHelper->fail('Card not found', null, 404);
    }



    public function getCardImageResponse($cardId)
    {
        $imageData = $this->cardRepository->getCardImage($cardId);

        if (!$imageData) {
            return $this->responseHelper->fail('Image not found', null, 404);
        }

        return response()->file(
            Storage::disk('private')->path($imageData['path']),
            ['Content-Type' => $imageData['mime_type']]
        );
    }

    public function editCard(int $type_card_id, string $card_type, $request)
    {
        $result = $this->cardRepository->updateCard($type_card_id, $card_type, $request);

        return $result
            ? $this->responseHelper->success('Card Updated successfully', $result, 200)
            : $this->responseHelper->fail('Card not found', null, 404);
    }

    public function deleteCardByIDAndCardType(int $type_card_id, string $card_type)
    {
        $result = $this->cardRepository->deleteCard($type_card_id, $card_type);

        return $result
            ? $this->responseHelper->success('Card deleted successfully', null, 200)
            : $this->responseHelper->fail('Card not found', null, 404);
    }

    public function cardsFilter($searchByName, $filter, $filterValue, $month = null, $year = null)
    {

        $result = $this->cardRepository->cardsFilter($searchByName, $filter, $filterValue, $month, $year);
        // $result = $this->cardRepository->cardsFilter();

        return $result
            ? $this->responseHelper->success('Cards found', $result, 200)
            : $this->responseHelper->fail('Cards not found', null, 404);
    }


    public function getAllCardType()
    {
        $result = $this->cardRepository->getAllCardType();

        return $result
            ? $this->responseHelper->success('All Card Type found', $result, 200)
            : $this->responseHelper->fail('All Card Type not found', null, 404);
    }

    public function createCardType(string $name)
    {
        // Check if card type already exists
        if ($this->cardRepository->exists($name)) {
            return $this->responseHelper->fail('Card type already exists', null, 400);
        }

        // Create new card type
        $result = $this->cardRepository->createCardType($name);

        return $result
            ? $this->responseHelper->success('Card type created successfully', $result, 201)
            : $this->responseHelper->fail('Failed to create card type', null, 500);
    }
}
