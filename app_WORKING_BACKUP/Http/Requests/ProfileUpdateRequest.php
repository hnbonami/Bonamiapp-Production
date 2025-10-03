<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Accept legacy 'name' for tests that expect $user->name to be updated
            'name' => ['sometimes','nullable','string','max:255'],
            'voornaam' => ['nullable', 'string', 'max:100'],
            'naam' => ['nullable', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'geboortedatum' => ['nullable', 'date'],
            'geslacht' => ['nullable', 'in:m,v,other,prefer_not_say'],
            'avatar' => ['sometimes','nullable','image','max:4096'],
            'avatar_delete' => ['sometimes','in:0,1'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $voornaam = trim((string) $this->input('voornaam', ''));
        $naam = trim((string) $this->input('naam', ''));

        if (!$this->filled('name') && ($voornaam !== '' || $naam !== '')) {
            $this->merge([
                'name' => trim($voornaam . ' ' . $naam),
            ]);
        }
    }
}
