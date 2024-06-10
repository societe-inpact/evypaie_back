<?php

use Illuminate\Support\Facades\Route;

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




Route::post("/mapping", [App\Http\Controllers\API\MappingController::class, 'getMapping']);
Route::post("/store-mapping", [App\Http\Controllers\API\MappingController::class, 'storeMapping']);
Route::put("/update-mapping/{id}", [App\Http\Controllers\API\MappingController::class, 'updateMapping']);

Route::get("/absences", [App\Http\Controllers\API\AbsenceController::class, 'getAbsences']);
Route::get("/custom-absences", [App\Http\Controllers\API\AbsenceController::class, 'getCustomAbsences']);
Route::post("/custom-absences/create", [App\Http\Controllers\API\AbsenceController::class, 'createCustomAbsence']);
Route::get("/hours", [App\Http\Controllers\API\HourController::class, 'getHours']);
Route::get("/custom-hours", [App\Http\Controllers\API\HourController::class, 'getCustomHours']);
Route::post("/custom-hours/create", [App\Http\Controllers\API\HourController::class, 'createCustomHour']);
Route::get("/variables-elements", [App\Http\Controllers\API\VariablesElementsController::class, 'getVariablesElements']);
Route::post("/variables-elements/create", [App\Http\Controllers\API\VariablesElementsController::class, 'createVariableElement']);

Route::get("/companies", [App\Http\Controllers\API\CompanyController::class, 'getCompanies']);
Route::post("/company/create", [App\Http\Controllers\API\CompanyController::class, 'createCompany']);
Route::post("/company_folder/create", [App\Http\Controllers\API\CompanyFolderController::class, 'createCompanyFolder']);

Route::group(['middleware' => 'cors'], function () {
    Route::post('/login', [App\Http\Controllers\API\ApiAuthController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\API\ApiAuthController::class, 'register']);
});

Route::post("/import", [App\Http\Controllers\ConvertController::class, 'importFile']);
Route::post("/convert", [App\Http\Controllers\ConvertController::class, 'convertFile']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post("/logout", [App\Http\Controllers\API\ApiAuthController::class, 'logout']);
    Route::get("/user", [App\Http\Controllers\API\ApiAuthController::class, 'getUser']);
});
