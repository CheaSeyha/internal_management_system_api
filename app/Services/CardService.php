<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Models\Card;
use App\Repository\CardRepository;

class CardService
{
    protected $cardRepository;
    protected $responseHelper;
    /**
     * Create a new class instance.
     */
    public function __construct(CardRepository $cardRepository, ResponseHelper $responseHelper)
    {
        //
        $this->cardRepository = $cardRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($cardRequest)
    {
        $card = $this->cardRepository->store($cardRequest);
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
        if ($card) {
            return $this->responseHelper->success('Card retrieved successfully', $card, 200);
        }
        return $this->responseHelper->fail('Card not found', null, 404);
    }

    public function updateCard($id, $cardRequest)
    {
        $card = $this->cardRepository->getCardById($id);
        if (!$card) {
            return $this->responseHelper->fail('Card not found', null, 404);
        }

        // Update the card with the new data
        $this->cardRepository->updateCard($id, $cardRequest);
        $updatedCard = $this->cardRepository->getCardById($id); // Fetch the updated card

        return $this->responseHelper->success('Card updated successfully', $updatedCard, 200);
    }

}
