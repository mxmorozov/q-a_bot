<?php

use App\Bot\NonCommandHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

Route::post('/webhook', function () {
    $update = Telegram::commandsHandler(true);
//    Log::debug(print_r($update, true));
    NonCommandHandler::handle($update);
    return 'ok';
});
