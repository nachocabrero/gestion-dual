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

        $cleanPortfolio = ($portfolio === 'https://' || $portfolio === 'http://' || $portfolio === '') ? null : $portfolio;
        if ($cleanPortfolio && !preg_match('~^https?://~i', $cleanPortfolio)) {
            $cleanPortfolio = 'https://' . $cleanPortfolio;
        }

        $cleanLinkedin = ($linkedin === 'https://' || $linkedin === 'http://' || $linkedin === '') ? null : $linkedin;
        if ($cleanLinkedin && !preg_match('~^https?://~i', $cleanLinkedin)) {
            $cleanLinkedin = 'https://' . $cleanLinkedin;
        }

        $cleanGithub = ($github === 'https://' || $github === 'http://' || $github === '') ? null : $github;
        if ($cleanGithub && !preg_match('~^https?://~i', $cleanGithub)) {
            $cleanGithub = 'https://' . $cleanGithub;
        }

        $this->merge([
            'linkedin_url' => $cleanLinkedin,
            'github_url' => $cleanGithub,
            'portfolio_url' => $cleanPortfolio,
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
            'empresa_practicas_primero' => ['nullable', 'string', 'max:255'],
            'horas_practicas_primero' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'sin_practicas_primero' => ['nullable', 'boolean'],
            'repitiendo_modulos_primero' => ['nullable', 'boolean'],
            'domicilio' => ['nullable', 'string', 'max:500'],
            'fecha_nacimiento' => ['nullable', 'date'],
        ];
    }

    /**
     * Mensajes personalizados para las reglas de validación.
     */
    public function messages(): array
    {
        return [
            'avatar.image' => 'El archivo seleccionado para el avatar debe ser una imagen válida.',
            'avatar.mimes' => 'El avatar debe estar en formato JPG, PNG o WebP.',
            'avatar.max' => 'La foto de perfil no puede superar los 2 MB (2048 KB) de tamaño.',
            'cv.file' => 'El archivo seleccionado como CV/documento no es válido.',
            'cv.mimes' => 'El documento debe estar en formato PDF, JPG, PNG o WebP.',
            'cv.max' => 'El documento no puede superar los 5 MB (5120 KB) de tamaño.',
        ];
    }
}
