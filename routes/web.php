<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlacesController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PassRecoveryController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\FollowQuestionController;
use App\Http\Controllers\FollowTagController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\EventController;

use App\Models\FollowQuestion;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/




Route::controller(HomeController::class)->group(function (){
    Route::get('/', 'index')->name('index');
    Route::get('/about', 'about')->name('about');
});

// Should be merged with user controller?
Route::controller(FeedController::class)->group(function (){
    Route::get('/feed', 'index')->name('feed')->middleware('auth');
});

Route::controller(QuestionController::class)->group(function () {
    Route::get('/questions/add', 'showQuestionForm')->name('question.create');
    Route::get('/questions/{id}', 'show')->name('question.show')->whereNumber('id');
});

Route::controller(UserController::class)->group(function () {
    Route::get('/users/{id}', 'show')->name('user.show')->whereNumber('id');
    Route::post('/users/{id}', 'edit')->name('users.edit')->whereNumber('id');
    Route::post('/users', 'add')->name('users.store');
    //Route::delete('/users/{id}', 'delete')->name('users.delete')->whereNumber('id');
});

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(PassRecoveryController::class)->group(function () {
    Route::get('/recovery', 'showRecoverPage')->name("pass.recover");
    Route::post('/recovery', 'handleRecover');
    Route::get('/recovery/{token}', 'changePasswordPage');
    Route::post('/recovery/{token}', 'changePassword');

});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register'); // this name is useful to redirect users to "register" easily!
    Route::post('/register', 'register');
});

// Search
Route::redirect('/questions', '/search');
Route::controller(SearchController::class)->group(function () {
    Route::get('/search', 'search')->name('search');
});


Route::controller(AdminController::class)->group(function(){
    Route::get('/admin', 'main')->name('admin.main');
    Route::get('/admin/tags', 'tagPanel')->name('admin.tags');
});


Route::controller(PlacesController::class)->group(function(){
    Route::get('/countries/{id}', 'showCountry')->whereNumber('id')->name('country');
    Route::get('/cities/{id}', 'showCity')->whereNumber('id')->name('city');
});

Route::controller(EventController::class)->group(function(){
    Route::post('/events/{id}', 'store')->whereNumber('id')->name('events.store');
});

Route::controller(GoogleController::class)->group(function () {
    Route::get('auth/google', 'redirect')->name('google-auth');
    Route::get('auth/google/call-back', 'callbackGoogle')->name('google-call-back');
});

Route::prefix('/api')->group(function () {
    Route::controller(QuestionController::class)->group(function () {
        Route::post('/questions/{id}', 'update')->whereNumber('id');
        Route::delete('/questions/{id}', 'delete')->whereNumber('id');
        Route::delete('/questions/{id}/author', 'deleteAuthor')->whereNumber('id');
        Route::get('/questions', 'search');
        Route::put('/questions', 'add')->name('question.store');
        Route::get('/questions/{id}/votes', 'getVotes')->whereNumber('id');
        Route::get('/questions/{id}/hasUserVoted', 'hasUserVoted')->whereNumber('id');
    });
    Route::controller(AnswerController::class)->group(function () {
        Route::put('/questions/{id}', 'store')->whereNumber('id')->name('answer.store');
        Route::delete('/answers/{id}', 'delete')->whereNumber('id');
        Route::post('/answers/{id}', 'update')->whereNumber('id');
        Route::post('/answers/{id}/correct', 'markCorrect')->whereNumber('id')->name("answers.markAsCorrect");
        Route::get('/answers/{id}/votes', 'getVotes')->whereNumber('id');
        Route::get('/answers/{id}/hasUserVoted', 'hasUserVoted')->whereNumber('id');
    });
    Route::controller(UserController::class)->group(function () {
        Route::get('/users', 'search');
        Route::post('/users/{id}/image', 'editpf')->whereNumber('id');
        Route::delete('/users/{id}', 'delete')->whereNumber('id');
        Route::post('/users/{id}/block', 'block')->whereNumber('id');
        Route::post('/users/{id}/unblock', 'unblock')->whereNumber('id');
        Route::post('/user/togglenotification', 'toggleNotification')->name('users.notifications.toggle');
    });
    Route::controller(PlacesController::class)->group(function () {
        Route::get('/cities/{id}', 'cities')->whereNumber('id');
        Route::get('/cities/{id}/events', 'events')->whereNumber('id');
        Route::get('/countries/trending', 'trendingLocations');
    });
    Route::controller(CommentController::class)->group(function () {
        Route::put('/posts/{id}', 'store')->whereNumber('id');
        Route::post('/comments/{id}', 'update')->whereNumber('id');
        Route::delete('/comments/{id}', 'delete')->whereNumber('id');
    });

    Route::controller(NotificationController::class)->group(function(){
        Route::get('/notification/votes', 'getVoteNotification');
        Route::get('/notification/answers', 'getVoteNotification');
        Route::put('notification/view', 'markViewed');
        Route::get('notification', 'getNotifications');
    });

    Route::controller(FileController::class)->group(function () {
        Route::post('/image','store');
    });
    Route::controller(VoteController::class)->group(function () {
        Route::post('/posts/{id}/vote', 'vote')->whereNumber('id');
    });
    Route::controller(FollowQuestionController::class)->group(function () {
        Route::post('/questions/{id}/toggleFollow', 'toggleFollow')->whereNumber('id');
        Route::get('/questions/{id}/isFollowing', 'isFollowing')->whereNumber('id');
    });
    Route::controller(FollowTagController::class)->group(function () {
        Route::post('/tags/{id}/toggleFollow', 'toggleFollow')->whereNumber('id');
        Route::get('/tags/{id}/isFollowing', 'isFollowing')->whereNumber('id');
    });
    Route::controller(TagController::class)->group(function () {
        Route::get('/tags', 'search');
        Route::post('/tags', 'store');
        Route::delete('/tags/{id}', 'delete')->whereNumber('id');
    });
});

