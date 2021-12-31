<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|min:6'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name can not be empty',
            'email.required' => 'Email can not be empty',
            'email.email' => 'Email must be an email',
            'password.required' => 'Password can not be empty',
            'password.min' => 'Minimum password is 6 character',
            'password.confirmed' => 'Password confirmation does not match ',
            'password_confirmation.required' => 'Password confirmation can not be empty',
            'password_confirmation.min' => 'Minimum password confirmation is 6 character',
        ];
    }
}
