<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            "required" => "Le champ :attribute est obligatoire.",
            "string" => "Le champ :attribute doit être une chaîne de caractères.",
            "max" => "Le champ :attribute ne doit pas dépasser :max caractères.",
            "password.min" => "Le mot de passe doit contenir au moins :min caractères.",
            "email" => "Le champ :attribute doit être une adresse e-mail valide.",
            "unique" => "L'adresse e-mail est déjà utilisée.",
            "lowercase" => "Le champ :attribute doit être en minuscules.",
            "confirmed" => "La confirmation du mot de passe ne correspond pas.",
        ]);
        try {
            $validator->validate();
        }
        catch (ValidationException $e) {
            return back()->withErrors($e->errors())->onlyInput('email');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
