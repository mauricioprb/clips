<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WeeklySlotRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'weekday' => ['required', 'integer', 'between:1,5'],
            'start_minute' => ['required', 'integer', 'between:0,1439'],
            'end_minute' => ['required', 'integer', 'max:1440', 'gt:start_minute'],
            'description' => ['required', 'string', 'max:255'],
            'valid_from' => ['nullable', 'date_format:Y-m-d'],
            'valid_until' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:valid_from'],
        ];
    }
}
