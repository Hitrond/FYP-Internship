<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        if (! class_exists(Socialite::class)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Social login is not configured. Please contact the administrator.']);
        }

        return Socialite::driver($this->normalizeProvider($provider))->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        if (! class_exists(Socialite::class)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Social login is not configured. Please contact the administrator.']);
        }

        $socialUser = Socialite::driver($this->normalizeProvider($provider))->stateless()->user();

        $email = $socialUser->getEmail();

        if (! $email) {
            return redirect()->route('login')
                ->withErrors(['email' => 'No email address was returned from the provider.']);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Account not provisioned. Please contact the administrator.']);
        }

        Auth::login($user, true);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function normalizeProvider(string $provider): string
    {
        if ($provider === 'microsoft') {
            return 'azure';
        }

        return $provider;
    }
}
