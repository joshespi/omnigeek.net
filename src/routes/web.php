<?php

use App\Livewire\AdminCategories;
use App\Livewire\CategoryFeed;
use App\Livewire\Feed;
use App\Livewire\GeekProfile;
use App\Livewire\ShowPost;
use App\Livewire\Subscribe;
use App\Livewire\TagFeed;
use App\Livewire\TagIndex;
use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Support\Facades\Route;

Route::get('/', Feed::class)->name('home');
Route::get('post/{post}', ShowPost::class)->name('posts.show');
Route::get('geeks/{user}', GeekProfile::class)->name('geeks.show');

Route::get('categories', fn () => view('categories.index', [
    'categories' => Category::withCount('posts')->orderBy('name')->get(),
]))->name('categories.index');
Route::get('categories/{category}', CategoryFeed::class)->name('categories.show');

Route::get('tags', TagIndex::class)->name('tags.index');
Route::get('tags/{tag}', TagFeed::class)->name('tags.show');

Route::get('subscribe', Subscribe::class)->name('subscribe');
Route::get('subscribe/confirm/{token}', function (string $token) {
    $sub = Subscription::where('token', $token)->firstOrFail();
    $sub->forceFill(['confirmed_at' => $sub->confirmed_at ?? now()])->save();

    return view('subscriptions.confirmed');
})->name('subscriptions.confirm');
Route::get('subscribe/unsubscribe/{token}', function (string $token) {
    Subscription::where('token', $token)->firstOrFail()->delete();

    return view('subscriptions.unsubscribed');
})->name('subscriptions.unsubscribe');

Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin.home')->name('home');
    Route::get('categories', AdminCategories::class)->name('categories');
});

Route::get('dashboard', Feed::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
