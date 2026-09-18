<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\HolidayRecurrence;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class HolidayRequest extends FormRequest
{
    /** @return array<string, array<int, string|Enum>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'recurrence' => ['required', Rule::enum(HolidayRecurrence::class)],
            'date' => ['required_unless:recurrence,' . HolidayRecurrence::CorpusChristi->value, 'nullable', 'date_format:Y-m-d'],
        ];
    }
}
