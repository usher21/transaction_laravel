<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserPostRequest;
use App\Http\Resources\UserRessource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserRessource::collection(User::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserPostRequest $request)
    {
        $validatedData = $request->validated();
        return new UserRessource(User::create($validatedData));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserRessource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        Validator::make(
            $request->all(),
            [
                "fullname" => "sometimes:required|min:6",
                "email" => "sometimes:required|email|unique:users",
                "phone" => "sometimes:required|unique:users|regex:/^(7[76508]{1})(\\d{7})$/",
                "balance" => "sometimes:required|integer|min:0"
            ],
            [
                "fullname.required" => "Le nom est requis",
                "fullname.min" => "Le nom doit être au minimum 6 caractères",
                "email.required" => "L'adresse email est requis",
                "email.email" => "L'adresse n'est pas valide",
                "email.unique" => "L'adresse doit être unique",
                "phone.required" => "Le numéro de téléphone est requis",
                "phone.unique" => "Le numéro de téléphone doit être unique",
                "phone.regex" => "Le numéro de téléphone n'est pas valide",
                "balance.integer" => "Le solde doit être un nombre entier",
                "balance.min" => "Le solde ne doit pas être négatif",
            ]
        )->validated();

        if (!$user->update($request->only('fullname', "email", "phone", "balance"))) {
            return ['message' => "Une erreur s'est produite, Impossible de modifier l'utilisateur"];
        }

        return new UserRessource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
