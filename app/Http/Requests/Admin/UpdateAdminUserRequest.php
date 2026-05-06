<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\SupportedCurrencies;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAdminUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_admin' => $this->boolean('is_admin'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'currency' => ['required', 'string', 'size:3', Rule::in(SupportedCurrencies::codes())],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var User $target */
            $target = $this->route('user');
            $actor = $this->user();

            if (! $this->boolean('is_admin') && $target->isAdmin()) {
                $otherAdmins = User::query()->where('is_admin', true)
                    ->where('id', '!=', $target->id)
                    ->exists();
                if (! $otherAdmins) {
                    $validator->errors()->add('is_admin', 'At least one administrator must remain.');
                }
            }

            if ($target->id === $actor->id && ! $this->boolean('is_admin')) {
                $validator->errors()->add('is_admin', 'You cannot remove your own admin access.');
            }
        });
    }
}
