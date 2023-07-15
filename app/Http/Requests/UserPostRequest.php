<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "fullname" => "required|min:6",
            "email" => "required|email|unique:users",
            "phone" => "required|unique:users|regex:/^(7[76508]{1})(\\d{7})$/",
            "balance" => "integer|min:0",
            "picture" => "min:3"
        ];
    }

    public function messages()
    {
        return [
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
        ];
    }
}
