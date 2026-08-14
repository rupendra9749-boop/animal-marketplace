<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BreedController;
use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BreedingProfileController;
use App\Http\Controllers\Api\BreedingRequestController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ChatController;

// ===== Auth (public) =====
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ===== Public browse (no login needed) =====
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/breeds', [BreedController::class, 'index']);
Route::get('/animals', [AnimalController::class, 'index']);
Route::get('/animals/{id}', [AnimalController::class, 'show']);
Route::get('/breeding-profiles', [BreedingProfileController::class, 'index']);
Route::get('/breeding-profiles/{id}', [BreedingProfileController::class, 'show']);
Route::get('/users/{userId}/reviews', [ReviewController::class, 'userReviews']);

// ===== Protected (JWT required) =====
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Sell module
    Route::post('/animals', [AnimalController::class, 'store']);
    Route::put('/animals/{id}', [AnimalController::class, 'update']);
    Route::delete('/animals/{id}', [AnimalController::class, 'destroy']);
    Route::get('/my-listings', [AnimalController::class, 'myListings']);

    // Offers (negotiation)
    Route::post('/offers', [OfferController::class, 'store']);
    Route::get('/offers/received', [OfferController::class, 'received']);
    Route::get('/offers/sent', [OfferController::class, 'sent']);
    Route::put('/offers/{id}/respond', [OfferController::class, 'respond']);

    // Orders / checkout
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/my-orders', [OrderController::class, 'myOrders']);
    Route::get('/orders/seller-orders', [OrderController::class, 'sellerOrders']);

    // Breeding match
    Route::post('/breeding-profiles', [BreedingProfileController::class, 'store']);
    Route::post('/breeding-requests', [BreedingRequestController::class, 'store']);
    Route::put('/breeding-requests/{id}/respond', [BreedingRequestController::class, 'respond']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{animalId}', [WishlistController::class, 'destroy']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{animalId}', [CartController::class, 'destroy']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markRead']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Chat
    Route::get('/chats/{userId}', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);
});
