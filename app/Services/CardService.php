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

    public function store(array $data)
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

    public function updateCard($id, array $data)
    {
        $card = $this->cardRepository->updateCard($id, $data);
        return $card
            ? $this->responseHelper->success('Card updated successfully', $card, 200)
            : $this->responseHelper->fail('Card not found', null, 404);
    }

    public function deleteCard($cardId)
    {
        $result = $this->cardRepository->deleteCard($cardId);
        return $result
            ? $this->responseHelper->success('Card deleted successfully', null, 200)
            : $this->responseHelper->fail('Card not found', null, 404);
    }
}
