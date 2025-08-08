<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\BlockController;
use App\Http\Controllers\api\CardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::controller(AuthController::class)->group(function () {
    // Public routes
    // These routes do not require authentication
    // They are used for user registration and login
    // The AuthController handles the logic for these actions
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/refresh-token', 'refreshToken');
});

// Protected routes
// These routes require authentication
// They are used for actions that require a logged-in user
// The AuthController handles the logic for these actions
Route::middleware('auth:api')->group(function () {
    // Add protected routes here, e.g.:
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    //Cards Routes CRUD
    Route::post('/create_card', [CardController::class, 'create_card']);
    Route::get('/cards', [CardController::class, 'getAllCards']);
    Route::post('/card/search', [CardController::class, 'searchCard']);
    Route::delete('/card/delete/{type_card_id}/{card_type}', [CardController::class, 'deleteCard']);
    Route::post('/card/edit/{type_card_id}/{card_type}', [CardController::class, 'editCard']);
    //Cards Routes CRUD

    // Building routes
    Route::get('/blocks/all_buildings', [BlockController::class, 'getAllBuildings']);
    Route::post('/blocks/add_building', [BlockController::class, 'createBuilding']);
    Route::put('/blocks/update_building/{building_id}', [BlockController::class, 'updateBuilding']);
    Route::delete('/blocks/delete_building/{building_id}', [BlockController::class, 'deleteBuilding']);

    // Room routes
    Route::get('/blocks/all_rooms', [BlockController::class, 'getAllRooms']);
    Route::post('/blocks/add_room', [BlockController::class, 'createRoom']);
    Route::delete('/blocks/delete_room/{room_name}/{building_id}', [BlockController::class, 'deleteRoom']);



    Route::get('/cards/{id}/image', [CardController::class, 'getImage'])
        ->name('api.cards.image');
});
