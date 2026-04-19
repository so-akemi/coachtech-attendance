<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Http\Responses\LogoutResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use App\Http\Responses\LoginResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Laravel\Fortify\Http\Requests\LoginRequest::class,
            \App\Http\Requests\LoginRequest::class
        );

        //$this->app->singleton(
            //\Laravel\Fortify\Http\Requests\RegisterRequest::class,
            //\App\Http\Requests\RegisterRequest::class
        //);

        $this->app->instance(LogoutResponseContract::class, new LogoutResponse);

        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
         return view('auth.register');
        });

        Fortify::loginView(function () {
            if (request()->is('admin/*')) {
                return view('admin.auth.login');
            }
            return view('auth.login');
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        RateLimiter::for('login', function (Request $request) {
         $email = (string) $request->email;

         return Limit::perMinute(10)->by($email . $request->ip());
        });

        Fortify::authenticateThrough(function (Request $request) {
            return array_filter([
                // ★ 工程1: 自作FormRequestによるバリデーション
                //function ($request, $next) {
                    //$loginRequest = new \App\Http\Requests\LoginRequest();
                    //$request->validate($loginRequest->rules(), $loginRequest->messages());
                    //return $next($request);
                //},

                // ★ 工程2: Fortify標準の認証処理（メール認証やレートリミットを含む）
                config('fortify.limiters.login') ? \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class : null,
                \Laravel\Fortify\Actions\PrepareAuthenticatedSession::class,
                \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
                \Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable::class,
            ]);
        });
    }
}
