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

    public function getCardByIDAndCardType(int $type_card_id, string $card_type, )
    {
        $card = $this->cardRepository->getCardByIDAndCardType($type_card_id, $card_type, );

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

    public function editCard(int $type_card_id,string $card_type,$request)
    {
        $result = $this->cardRepository->updateCard($type_card_id,$card_type,$request);

        return $result
            ? $this->responseHelper->success('Card Updated successfully', $result, 200)
            : $this->responseHelper->fail('Card not found', null, 404);
    }

    public function deleteCardByIDAndCardType(int $type_card_id, string $card_type)
    {
        $result = $this->cardRepository->deleteCard($type_card_id,$card_type);

        return $result
            ? $this->responseHelper->success('Card deleted successfully', null, 200)
            : $this->responseHelper->fail('Card not found', null, 404);
    }
}
