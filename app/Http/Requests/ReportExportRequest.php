<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportExportRequest extends FormRequest
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
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'format' => ['required', 'in:csv,pdf'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $start = $this->input('start_date');
            $end = $this->input('end_date');
            if (! is_string($start) || ! is_string($end)) {
                return;
            }
            try {
                $days = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1;
                if ($days > 1096) {
                    $validator->errors()->add('end_date', 'Date range cannot exceed 1096 days.');
                }
            } catch (\Throwable) {
                // date rules already cover invalid values
            }
        });
    }
}
