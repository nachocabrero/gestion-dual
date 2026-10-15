<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Preparar los datos antes de la validación.
     */
    protected function prepareForValidation(): void
    {
        $linkedin = trim($this->linkedin_url ?? '');
        $github = trim($this->github_url ?? '');
        $portfolio = trim($this->portfolio_url ?? '');

        $this->merge([
            'linkedin_url' => ($linkedin === 'https://' || $linkedin === 'http://' || $linkedin === '') ? null : $this->linkedin_url,
            'github_url' => ($github === 'https://' || $github === 'http://' || $github === '') ? null : $this->github_url,
            'portfolio_url' => ($portfolio === 'https://' || $portfolio === 'http://' || $portfolio === '') ? null : $this->portfolio_url,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'cv' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
