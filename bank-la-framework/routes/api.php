<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\fund;
use App\Http\Controllers\loan;
use App\Http\Controllers\Auth;
use App\Http\Controllers\profile;
use App\Http\Controllers\transcation;
use App\Http\Controllers\transfer;
use PHPUnit\Framework\Attributes\Group;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [Auth::class, 'login']);
    Route::post('/register', [Auth::class, 'register']);
    Route::post('/logout', [Auth::class, 'logout']);
    Route::post('/forgot-password', [Auth::class, 'forgotPassword']);
    Route::post('/reset-password', [Auth::class, 'resetPassword']);
});

// Routes protected by JWT middleware
Route::middleware('auth:api')->group(function () {
    Route::group(['prefix'=>'transaction'], function () {
        Route::get('/fetch-transactions', [transcation::class, 'fetchTransactions']);
    Route::get('/fetch-transaction-single', [transcation::class, 'fetchTransactionSingle']);
    });

    Route::group(['prefix' => 'funds'], function () {
        Route::post('/initialize-payment', [fund::class, 'initializePayment']);
        Route::post('/verify-payment', [fund::class, 'verifyPayment']);
    });
    Route::group(['prefix' => 'loan'], function () {
        Route::post('/create-loan', [Loan::class, 'createLoan']);
        Route::get('/fetch-loans', [Loan::class, 'fetchLoans']);
        Route::get('/fetch-loan-single', [Loan::class, 'fetchLoanSingle']);
    });
    Route::group(['prefix' => 'profile'], function () {
        Route::post('/update-profile', [profile::class, 'updateUserProfile']);
        Route::get('/get-account-balance', [profile::class, 'getaccountBalance']);
        Route::get('/get-user-profile', [profile::class, 'getUserProfile']);
    });
    Route::group(['prefix' => 'transfer'], function () {
        Route::post('/same-bank-transfer', [transfer::class, 'sameBankTransfer']);
        Route::post('/other-bank-transfer', [transfer::class, 'createTransfer']);
        Route::get('/check-amount', [transfer::class, 'checkAmount']);
        Route::get('/find-account-same', [transfer::class, 'findAccount']);
        Route::get('/find-account-other', [transfer::class, 'resolveAccount']);
        Route::post('/create-transfer-recipient', [transfer::class, 'createTransferRecipient']);
    });
    Route::group(['prefix'=>'loan'],function () {
         Route::post('/delete-loan', [loan::class, 'deleteLoan']);
    Route::post('/update-loan', [loan::class, 'updateLoan']);
    Route::get('/fetch-loan-details', [loan::class, 'fetchLoanDetails']);
    Route::get('/fetch-loans', [loan::class, 'fetchLoans']);
    Route::get('/fetch-loan-single', [loan::class, 'fetchLoanSingle']);
    });

});
