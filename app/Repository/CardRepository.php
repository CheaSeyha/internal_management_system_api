<?php

namespace App\Repository;

use App\Models\Building;
use App\Models\Card;
use App\Models\CardType;
use App\Models\Isp;
use App\Models\Room;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class CardRepository
{

    protected $blockRepository;

    public function __construct(BlockRepository $blockRepository)
    {
        $this->blockRepository = $blockRepository;
    }

    public function store(array $data)
    {
        // 1️⃣ Get or create CardType
        $cardType = CardType::where('name', $data['card_type'])->firstOrFail();


        // 2️⃣ Determine next card number
        $lastNumber = Card::where('card_type_id', $cardType->id)->max('card_number') ?? 0;
        $nextNumber = $lastNumber + 1;

        // 3️⃣ Create card
        $card = Card::create([
            'card_type_id' => $cardType->id,
            'card_number' => $nextNumber,
            'card_name' => $data['card_name'],
            'user_id' => auth()->id(),
        ]);

        if (strtolower($data['card_type']) === 'isp') {
            $card->isp_id = Isp::where('isp_name', $data['isp_name'])->first()->id;
            $card->isp_position = $data['isp_position'];
        }

        if (strtolower($data['card_type']) === 'rolling') {
            $card->rolling_link = $data['link'];
        }



        // Store iamge if provided
        if (isset($data['profile_image'])) {
            $file = $data['profile_image'];
            $extension = $file->getClientOriginalExtension();
            $sanitizedName = Str::slug($data['card_name']);
            $filename = "card_type={$data['card_type']},card_id={$nextNumber},card_name={$sanitizedName}.{$extension}";

            $card->profile_image = $file->storeAs(
                'cards/profile_images',
                $filename,
                'private'
            );
        }

        // 4️⃣ Parse blocks and attach
        $blocks = collect($data['block'] ?? [])
            ->map(function ($block) {
                $buildingName = $block['building'];
                $roomNames = $block['rooms'] ?? [];

                $building = Building::where('building_name', $buildingName)->first();
                if (!$building)
                    return null;

                $roomIds = Room::where('building_id', $building->id)
                    ->whereIn('room_name', $roomNames)
                    ->pluck('id')
                    ->toArray();

                // If no rooms, set room_id null for building-only
                if (empty($roomIds)) {
                    return [
                        'building_id' => $building->id,
                        'room_ids' => [null],
                        'label' => $buildingName,
                    ];
                }

                return [
                    'building_id' => $building->id,
                    'room_ids' => $roomIds,
                    'label' => $buildingName . '-' . implode('-', $roomNames),
                ];
            })
            ->filter();




        foreach ($blocks as $block) {
            foreach ($block['room_ids'] as $roomId) {
                $card->buildings()->attach($block['building_id'], [
                    'room_id' => $roomId
                ]);
            }
        }


        // 5️⃣ Prepare response
        $user = auth()->user();
        $blockString = $blocks->pluck('label')->join(','); // combine all blocks
        $card->save();
        return $card->toResponse();
    }


    public function getAllCards()
    {
        // Load cards with relationships
        $cards = Card::with(['user', 'cardType', 'buildings.rooms'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->latest()
            ->paginate(17);
        // Transform each Card model using toResponse()
        $cards->getCollection()->transform(function ($card) {
            return $card->toResponse();
        });

        return $cards;
    }



    public function cardsFilter(
        $searchByName = null,
        array $filterBlocks = [],
        array $filterCardTypes = [],
        $month = null,
        $year = null
    ) {
        $query = Card::with(['user', 'cardType', 'buildings.rooms'])->latest();

        // 🔍 Search by card name or number
        if ($searchByName) {
            $query->where(function ($q) use ($searchByName) {
                $q->where('card_name', 'like', "%{$searchByName}%")
                    ->orWhere('card_number', $searchByName);
            });
        }

        // 🔍 Filter by MULTIPLE card types
        if (!empty($filterCardTypes)) {
            $query->whereHas('cardType', function ($q) use ($filterCardTypes) {
                $q->whereIn('name', $filterCardTypes);
            });
        }

        // 🔍 Filter by MULTIPLE blocks
        if (!empty($filterBlocks)) {
            $query->whereHas('buildings', function ($q) use ($filterBlocks) {
                $q->whereIn('building_name', $filterBlocks);
            });
        }

        // 🔍 Month & year filter
        $query->whereMonth('created_at', $month ?? now()->month)
            ->whereYear('created_at', $year ?? now()->year);

        $cards = $query->paginate(17);

        if ($cards->isEmpty()) {
            return false;
        }

        // Keep filters in pagination
        $cards->appends([
            'searchByName' => $searchByName,
            'filterBlocks' => $filterBlocks,
            'filterCardTypes' => $filterCardTypes,
        ]);

        // Transform response
        $cards->getCollection()->transform(fn($card) => $card->toResponse());

        $cardsArray = $cards->toArray();
        $cardsArray['blocks'] = $this->blockRepository->getAllBuildings($month, $year);
        $cardsArray['cardTypes'] = $this->getAllCardType($month, $year);

        return $cardsArray;
    }



    //not use
    public function getCardById($id)
    {
        $card = Card::with('user')->find('card_number', $id);

        if ($card) {
            $card->create_by = $card->user->name;
            $card->makeHidden('user');
        }

        return $card;
    }

    public function getCardByIDAndCardType(int $typeCardId, string $card_type)
    {
        $card = Card::with('user', 'cardType')
            ->where('card_number', $typeCardId)
            ->whereHas('cardType', function ($q) use ($card_type) {
                $q->where('name', $card_type);
            })
            ->first();

        if ($card) {
            $card->create_by = $card->user->name ?? null;
            $card->makeHidden('user');
        }

        return $card;
    }

    // not use
    public function getCardByNameAndCardType(string $card_name, ?string $card_type = null)
    {
        $cards = Card::with('user')
            ->when($card_type, function ($query, $card_type) {
                return $query->where('card_type', $card_type);
            })
            ->where('card_name', 'like', '%' . $card_name . '%')
            ->get();

        if ($cards->isEmpty()) {
            return false; // or null, depending on your convention
        }

        //show user name that create the card
        foreach ($cards as $card) {
            $card->create_by = $card->user->name;
            $card->makeHidden('user');
        }

        return $cards;
    }

    //not use
    public function getAllCardType($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $cardTypes = CardType::all();

        if ($cardTypes->isEmpty()) {
            return false;
        }

        return $cardTypes->map(function ($type) use ($month, $year) {
            $count = $type->cards()
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();

            return [
                'card_type' => strtoupper($type->name),
                'count' => $count,
            ];
        });
    }




    public function getCardImage($cardId)
    {
        $card = Card::find($cardId);
        return $card && $card->profile_image
            ? [
                'path' => $card->profile_image,
                'mime_type' => Storage::disk('private')->mimeType($card->profile_image)
            ]
            : null;
    }

    public function updateCard($card_type_id, $card_type, array $data)
    {
        // 1️⃣ Find the existing card
        $isCardExist = $this->getCardByIDAndCardType($card_type_id, $card_type);
        if (!$isCardExist)
            return false;

        $card = Card::find($isCardExist['id']);
        if (!$card)
            return false;

        // 2️⃣ Update card type if provided
        if (isset($data['card_type'])) {
            $newCardType = CardType::where('name', $data['card_type'])->first();
            if ($newCardType) {
                $card->card_type_id = $newCardType->id;

                // Ensure unique card_number in new card type
                $exists = Card::where('card_type_id', $newCardType->id)
                    ->where('card_number', $card->card_number)
                    ->where('id', '!=', $card->id)
                    ->exists();

                if ($exists) {
                    $lastNumber = Card::where('card_type_id', $newCardType->id)->max('card_number') ?? 0;
                    $card->card_number = $lastNumber + 1;
                }
            }
        }

        // 3️⃣ Update card name
        if (isset($data['card_name'])) {
            $card->card_name = $data['card_name'];
        }

        // 4️⃣ Update profile image
        if (isset($data['profile_image'])) {
            if ($card->profile_image) {
                Storage::disk('private')->delete($card->profile_image);
            }

            $file = $data['profile_image'];
            $extension = $file->getClientOriginalExtension();
            $sanitizedName = Str::slug($data['card_name'] ?? $card->card_name);
            $filename = "card_type={$data['card_type']},card_id={$card->card_number},card_name={$sanitizedName}.{$extension}";

            $card->profile_image = $file->storeAs(
                'cards/profile_images',
                $filename,
                'private'
            );
        }

        // 5️⃣ Update blocks via pivot table
        if (isset($data['block'])) {
            $card->buildings()->detach();

            // Decode JSON if it came as string
            $blocksData = is_string($data['block']) ? json_decode($data['block'], true) : $data['block'];

            $blocks = collect($blocksData)
                ->map(function ($block) {
                    $buildingName = $block['building'] ?? null;
                    $roomNames = $block['rooms'] ?? [];

                    $building = Building::where('building_name', $buildingName)->first();
                    if (!$building)
                        return null;

                    $roomIds = Room::where('building_id', $building->id)
                        ->whereIn('room_name', $roomNames)
                        ->pluck('id')
                        ->toArray();

                    if (empty($roomIds)) {
                        return [
                            'building_id' => $building->id,
                            'room_ids' => [null],
                        ];
                    }

                    return [
                        'building_id' => $building->id,
                        'room_ids' => $roomIds,
                    ];
                })
                ->filter();

            foreach ($blocks as $block) {
                foreach ($block['room_ids'] as $roomId) {
                    $card->buildings()->attach($block['building_id'], [
                        'room_id' => $roomId
                    ]);
                }
            }
        }


        // 6️⃣ Save and return
        $card->save();

        return $card->toResponse();
    }








    public function deleteCard($card_type_id, $card_type): bool
    {
        $isCardExist = $this->getCardByIDAndCardType($card_type_id, $card_type);

        if (!$isCardExist) {
            return false;
        }

        $card = Card::find($isCardExist['id']);

        if (!$card) {
            return false;
        }

        if ($card->profile_image) {
            Storage::disk('private')->delete($card->profile_image);
        }

        $card->delete(); // triggers cascade in DB
        return true;
    }


    public function createCardType(string $name): CardType
    {
        return CardType::create(['name' => $name]);
    }

    public function exists(string $name): bool
    {
        return CardType::where('name', $name)->exists();
    }



    public function cards_summary($start_date, $end_date)
    {
        $priceMap = [
            'STAFF' => 20,
            'VIP CARD' => 50,
            'DELIVERY' => 80,
            'CAR CARD' => 50,
            'ISP' => 20,
            'ROLLING' => 5,
            'CONSTRUCTION' => 20,

        ];

        /* ---------------------------------
         * Date range
         * --------------------------------- */
        $dates = collect(
            \Carbon\CarbonPeriod::create($start_date, $end_date)
        )->map(fn($d) => $d->format('Y-m-d'));

        /* ---------------------------------
         * Card types
         * --------------------------------- */
        $cardTypes = \App\Models\CardType::select('id', 'name')
            ->get()
            ->mapWithKeys(fn($t) => [strtoupper($t->name) => $t->id]);

        /* ---------------------------------
         * Aggregate by DATE + CARD TYPE
         * --------------------------------- */
        $typeStats = \App\Models\Card::selectRaw('
            DATE(created_at) as date,
            card_type_id,
            COUNT(*) as total
        ')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->groupBy('date', 'card_type_id')
            ->get()
            ->groupBy('date');

        /* ---------------------------------
         * ChartAreaInteractive (FIXED)
         * --------------------------------- */
        $chartDataArea = [];

        foreach ($dates as $date) {
            $row = ['date' => $date];

            foreach ($priceMap as $typeName => $price) {
                $typeId = $cardTypes[$typeName] ?? null;

                $row[$typeName] = ($typeStats[$date] ?? collect())
                    ->firstWhere('card_type_id', $typeId)
                    ->total ?? 0;
            }

            $chartDataArea[] = $row;
        }

        $colors = ['#80bfff', '#d24dff', '#f59e0b', '#ff99cc', '#66ccff', '#ff0066', '#66ff33'];
        $chartConfigArea = [];

        foreach (array_keys($priceMap) as $i => $typeName) {
            $chartConfigArea[$typeName] = [
                'label' => $typeName,
                'color' => $colors[$i % count($colors)],
            ];
        }

        /* ---------------------------------
         * Aggregate by DATE + USER
         * --------------------------------- */
        $userStats = \App\Models\Card::selectRaw('
            DATE(created_at) as date,
            user_id,
            COUNT(*) as total
        ')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->groupBy('date', 'user_id')
            ->get()
            ->groupBy('date');

        $users = \App\Models\User::whereIn(
            'id',
            \App\Models\Card::whereBetween('created_at', [$start_date, $end_date])
                ->distinct()
                ->pluck('user_id')
        )->get();

        /* ---------------------------------
         * ChartBarInteractive (FIXED)
         * --------------------------------- */
        $chartDataBar = [];

        foreach ($dates as $date) {
            $row = ['date' => $date];

            foreach ($users as $user) {
                $row[$user->name] = ($userStats[$date] ?? collect())
                    ->firstWhere('user_id', $user->id)
                    ->total ?? 0;
            }

            $chartDataBar[] = $row;
        }

        $chartConfigBar = [];
        foreach ($users as $i => $user) {
            $chartConfigBar[$user->name] = [
                'label' => $user->name,
                'color' => $colors[$i % count($colors)],
            ];
        }

        /* ---------------------------------
         * Summary (FIXED)
         * --------------------------------- */
        $summaryRaw = \App\Models\Card::selectRaw('card_type_id, COUNT(*) as total')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->groupBy('card_type_id')
            ->get()
            ->keyBy('card_type_id');

        $summary = collect($priceMap)->map(function ($price, $typeName) use ($summaryRaw, $cardTypes) {
            $typeId = $cardTypes[$typeName] ?? null;
            $count = $summaryRaw[$typeId]->total ?? 0;

            return [
                'cardType' => $typeName,
                'cardAmount' => $count,
                'moneyAmount' => $count * $price,
            ];
        })->values();

        /* ---------------------------------
         * Return
         * --------------------------------- */
        return [
            'cards_data' => $summary,
            'ChartAreaInteractive' => [
                'data' => $chartDataArea,
                'config' => $chartConfigArea,
            ],
            'ChartBarInteractive' => [
                'summaryData' => $chartDataBar,
                'config' => $chartConfigBar,
            ],
        ];
    }

    public function getDuplicateCards($month, $year)
    {
        $cards = Card::with(['cardType', 'buildings.rooms', 'user', 'isp'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();

        // Group by Name and Card Type first to reduce comparisons
        $grouped = $cards->groupBy(function ($card) {
            return $card->card_name . '|' . $card->card_type_id;
        });

        $duplicates = collect();

        foreach ($grouped as $key => $group) {
            if ($group->count() < 2) {
                continue; // Unique name+type
            }

            // Within this group, check Block Signatures
            $blockGroups = $group->groupBy(function ($card) {
                // Generate signature: Sorted List of "BuildingID-RoomID"
                $pairs = collect();
                foreach ($card->buildings as $b) {
                    $pairs->push($b->id . '-' . $b->pivot->room_id);
                }

                return $pairs->sort()->implode('|');
            });

            // Any blockGroup with > 1 item is a set of duplicates
            foreach ($blockGroups as $signature => $dupCards) {
                if ($dupCards->count() > 1) {
                    foreach ($dupCards as $card) {
                        $duplicates->push($card);
                    }
                }
            }
        }

        // Pagination Manual Logic
        $page = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 17;

        $currentPageItems = $duplicates->slice(($page - 1) * $perPage, $perPage)->values();

        // Transform items using Model's toResponse
        $transformedItems = $currentPageItems->map(fn($card) => $card->toResponse());

        $paginated = new LengthAwarePaginator(
            $transformedItems,
            $duplicates->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return $paginated->toArray();
    }
}
