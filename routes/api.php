<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BlockController;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\ISPController;
use App\Http\Controllers\Api\V1\PositionController;
use App\Http\Controllers\Api\V1\StaffController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public routes — no auth required
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register',       'register');
        Route::post('/login',          'login');
        Route::post('/refresh-token',  'refreshToken');
    });

    // Protected routes — requires valid Passport Bearer token
    Route::middleware('auth:api')->group(function () {

        // Auth
        Route::get('/user',    [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        // ✅ Removed broken: Route::post('', [AuthController::class, '']);

        // Cards
        Route::post('/cards_summary',                          [CardController::class, 'cards_summary']);
        Route::get('/check_card_exist',                        [CardController::class, 'checkCardExist']);
        Route::post('/create_card',                            [CardController::class, 'create_card']);
        Route::get('/cards',                                   [CardController::class, 'getAllCards']);
        Route::get('/cards/get_all_card_type',                 [CardController::class, 'getAllCardType']);
        Route::post('/card/search',                            [CardController::class, 'searchCard']);
        Route::post('/card/cards_filter',                      [CardController::class, 'cardsFilter']);
        Route::post('/card/get_duplicate_cards',               [CardController::class, 'getDuplicateCards']);
        Route::delete('/card/delete/{type_card_id}/{card_type}', [CardController::class, 'deleteCard']);
        Route::post('/card/edit/{type_card_id}/{card_type}',   [CardController::class, 'editCard']);
        Route::post('/card/createCardType',                    [CardController::class, 'createCardType']);
        Route::get('/cards/{id}/image',                        [CardController::class, 'getImage'])->name('api.cards.image');

        // Buildings
        Route::get('/blocks/all_buildings',                        [BlockController::class, 'getAllBuildings']);
        Route::post('/blocks/add_building',                        [BlockController::class, 'createBuilding']);
        Route::put('/blocks/update_building/{building_id}',        [BlockController::class, 'updateBuilding']);
        Route::delete('/blocks/delete_building/{building_id}',     [BlockController::class, 'deleteBuilding']);

        // Rooms
        Route::get('/blocks/all_rooms',                                        [BlockController::class, 'getAllRooms']);
        Route::post('/blocks/add_room',                                        [BlockController::class, 'createRoom']);
        Route::delete('/blocks/delete_room/{room_name}/{building_id}',         [BlockController::class, 'deleteRoom']);

        // ISP
        Route::prefix('isp')->group(function () {
            Route::get('/all_isps',            [ISPController::class, 'getAllISPs']);
            Route::post('/add_isp',            [ISPController::class, 'addISP']);
            Route::put('/update_isp/{isp_id}', [ISPController::class, 'updateISP']);
            Route::delete('/delete_isp/{isp_id}', [ISPController::class, 'deleteISP']);
        });

        // Positions — admin only
        Route::prefix('position')->middleware('CheckUserRoleBase')->group(function () {
            Route::get('/all_positions',                   [PositionController::class, 'getAllPositions']);
            Route::post('/add_position',                   [PositionController::class, 'addPosition']);
            Route::put('/update_position/{position_id}',   [PositionController::class, 'updatePosition']);
            Route::delete('/delete_position/{position_id}', [PositionController::class, 'deletePosition']);
        });

        // Departments — admin only
        Route::middleware('CheckUserRoleBase')->group(function () {
            Route::get('/department',                     [DepartmentController::class, 'getAllDepartments']);
            Route::post('/department',                    [DepartmentController::class, 'addDepartment']);
            Route::put('/department/{department_id}',   [DepartmentController::class, 'updateDepartment']);
            Route::delete('/department/{department_id}', [DepartmentController::class, 'deleteDepartment']);
        });

        // Staff — admin only
        Route::middleware('CheckUserRoleBase')->group(function () {
            Route::post('/staff',                            [StaffController::class, 'addNewStaff']);
            Route::get('/staff',                             [StaffController::class, 'getAllStaff']);
            Route::patch('/staff/{staff_id}',                  [StaffController::class, 'updateStaff']);
            Route::post('/staff/search',                     [StaffController::class, 'searchStaff']);
            Route::delete('/staff/{staff_id}',                   [StaffController::class, 'deleteStaffs']);
            Route::get('/staff/image_profile/{staff_id}',    [StaffController::class, 'getProfileImage']);
        });
    });
});
