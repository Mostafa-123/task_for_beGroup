<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use function App\apiResponse;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->prefix('tasks')->controller(TaskController::class)->group(function () {

    Route::get('/',  'getAll');

    Route::get('/{id}',  'get');

    Route::post('/',  'create');

    Route::put('{id}',  'update');

    Route::patch('{id}',  'update');

    Route::delete('{id}',  'delete');

    Route::get('/user/created','user_created_tasks');

    Route::get('/user/assigned','user_assigned_tasks');

});


Route::any('{url}', function () {
    return apiResponse(401, "", "this url not found check parmater");
})->where('url', '.*')->middleware('api');
