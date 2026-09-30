<?php

use App\Http\Controllers\Admin\AnimalController as AdminAnimalController;
use App\Http\Controllers\Admin\BreedingController as AdminBreedingController;
use App\Http\Controllers\Admin\CaretakerController as AdminCaretakerController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\SellerController as AdminSellerController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VetController as AdminVetController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Breeder\AnimalController as BreederAnimalController;
use App\Http\Controllers\Breeder\DashboardController as BreederDashboardController;
use App\Http\Controllers\BreedingController;
use App\Http\Controllers\Caretaker\DashboardController as CaretakerDashboardController;
use App\Http\Controllers\Caretaker\ProfileController as CaretakerProfileController;
use App\Http\Controllers\CaretakerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\ProfileController as DoctorProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seller\AnimalController as SellerAnimalController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\SaleController as SellerSaleController;
use App\Http\Controllers\VetController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// The real home page lives at /home. The bare app root ("…/marketplace", no trailing slash) is a
// directory to Apache, which redirects it to http:// behind Cloudflare - and the Android app
// blocks plain http. Every generated link therefore points at /home instead.
Route::get('/', fn () => redirect()->route('home'));
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/animals/{animal:slug}', [HomeController::class, 'show'])->name('animals.show');

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->whereIn('locale', array_keys(config('app.locales')))->name('language.switch');

Route::get('/locations/cities', [LocationController::class, 'cities'])->name('locations.cities');
Route::post('/location', [LocationController::class, 'select'])->name('location.select');
Route::post('/location/detect', [LocationController::class, 'detect'])->middleware('throttle:30,1')->name('location.detect');
Route::delete('/location', [LocationController::class, 'clear'])->name('location.clear');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::view('/privacy', 'pages.privacy')->name('privacy');
// Advertisement landing pages (links for Facebook / Instagram / WhatsApp posts).
Route::get('/promo', [PromoController::class, 'show'])->name('promo');
Route::get('/promo/{theme}', [PromoController::class, 'show'])->whereIn('theme', PromoController::THEMES)->name('promo.theme');
Route::view('/delete-account', 'pages.account-deletion')->name('account.deletion');
Route::post('/contact', [PageController::class, 'sendContact'])->middleware('throttle:5,1')->name('contact.send');

Route::get('/breeding', [BreedingController::class, 'browse'])->name('breeding.browse');
Route::get('/breeding/match', [BreedingController::class, 'index'])->name('breeding.index');
Route::post('/breeding/cart', [BreedingController::class, 'addPairToCart'])->middleware('auth')->name('breeding.cart');

Route::get('/vets', [VetController::class, 'index'])->name('vets.index');
Route::middleware('auth')->group(function () {
    Route::get('/vets/register', [VetController::class, 'create'])->name('vets.create');
    Route::post('/vets', [VetController::class, 'store'])->middleware(['role:doctor', 'throttle:5,1'])->name('vets.store');
    Route::post('/vets/ai-search', [VetController::class, 'aiSearch'])->middleware('throttle:6,1')->name('vets.ai-search');
});
Route::get('/vets/{vet:slug}', [VetController::class, 'show'])->name('vets.show');

Route::get('/caretakers', [CaretakerController::class, 'index'])->name('caretakers.index');
Route::middleware('auth')->group(function () {
    Route::get('/caretakers/register', [CaretakerController::class, 'create'])->name('caretakers.create');
    Route::post('/caretakers', [CaretakerController::class, 'store'])->middleware(['role:caretaker', 'throttle:5,1'])->name('caretakers.store');
});
Route::get('/caretakers/{caretaker:slug}', [CaretakerController::class, 'show'])->name('caretakers.show');

Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');
Route::post('/compare/from-cart', [CompareController::class, 'fromCart'])->name('compare.from-cart');
Route::delete('/compare', [CompareController::class, 'clear'])->name('compare.clear');
Route::post('/compare/{animal}', [CompareController::class, 'store'])->name('compare.store');
Route::delete('/compare/{animal}', [CompareController::class, 'destroy'])->name('compare.destroy');

Route::get('/dashboard', function () {
    return redirect()->route(Auth::user()->homeRoute());
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'panel:buyer'])->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account.dashboard');
    Route::post('/account/become-seller', [AccountController::class, 'becomeSeller'])->name('account.become-seller');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{animal}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{animal}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{animal}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [OrderController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{animal}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{animal}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [MessageController::class, 'reply'])->name('messages.reply');
    Route::post('/animals/{animal}/messages', [MessageController::class, 'start'])->name('animals.messages.start');
    Route::post('/messages/direct/{user}', [MessageController::class, 'startDirect'])->name('messages.direct');
    Route::post('/messages/provider/{user}', [MessageController::class, 'startWithProvider'])->name('messages.provider');
    Route::get('/support', [MessageController::class, 'contactSupport'])->name('support');
});

Route::middleware(['auth', 'role:seller', 'panel:seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/sales', [SellerSaleController::class, 'index'])->name('sales.index');
    Route::post('/orders/{order}/approve', [SellerSaleController::class, 'approve'])->name('sales.approve');
    Route::post('/orders/{order}/decline', [SellerSaleController::class, 'decline'])->name('sales.decline');
    Route::resource('animals', SellerAnimalController::class);
});

Route::middleware(['auth', 'role:breeder', 'panel:breeder'])->prefix('breeder')->name('breeder.')->group(function () {
    Route::get('/dashboard', [BreederDashboardController::class, 'index'])->name('dashboard');
    Route::resource('animals', BreederAnimalController::class);
});

Route::middleware(['auth', 'role:doctor', 'panel:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DoctorProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [DoctorProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'role:caretaker', 'panel:caretaker'])->prefix('caretaker')->name('caretaker.')->group(function () {
    Route::get('/dashboard', [CaretakerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [CaretakerProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [CaretakerProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/sellers/create', [AdminSellerController::class, 'create'])->name('sellers.create');
    Route::post('/sellers', [AdminSellerController::class, 'store'])->name('sellers.store');

    Route::resource('categories', AdminCategoryController::class)->except('show');

    Route::get('/animals', [AdminAnimalController::class, 'index'])->name('animals.index');
    Route::get('/animals/{animal}', [AdminAnimalController::class, 'show'])->name('animals.show');
    Route::patch('/animals/{animal}', [AdminAnimalController::class, 'update'])->name('animals.update');
    Route::delete('/animals/{animal}', [AdminAnimalController::class, 'destroy'])->name('animals.destroy');

    Route::get('/breeding', [AdminBreedingController::class, 'index'])->name('breeding.index');

    Route::resource('caretakers', AdminCaretakerController::class)->except('show');
    Route::patch('/caretakers/{caretaker}/toggle', [AdminCaretakerController::class, 'toggle'])->name('caretakers.toggle');

    Route::resource('vets', AdminVetController::class)->except('show');
    Route::patch('/vets/{vet}/toggle', [AdminVetController::class, 'toggle'])->name('vets.toggle');
    Route::patch('/vets/{vet}/verify', [AdminVetController::class, 'verify'])->name('vets.verify');
    Route::post('/vets/ai-search', [AdminVetController::class, 'aiSearch'])->name('vets.ai-search');

    Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::delete('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
});

require __DIR__.'/auth.php';

// Unknown addresses still go through the web middleware, so the "page not found" screen speaks the chosen language.
Route::fallback(fn () => abort(404));
