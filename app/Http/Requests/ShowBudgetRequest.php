<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'month' => ['sometimes', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->query('month') === null || $this->query('month') === '') {
            $this->merge(['month' => now()->format('Y-m')]);
        }
    }
}
