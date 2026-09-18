<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Priority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class DefaultActivityRequest extends FormRequest
{
    /** @return array<string, array<int, string|Enum>> */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'priority' => ['required', Rule::enum(Priority::class)],
        ];
    }
}
