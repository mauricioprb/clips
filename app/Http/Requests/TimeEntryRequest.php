<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\TimeEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TimeEntryRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'start_minute' => ['required', 'integer', 'between:0,1439'],
            'end_minute' => ['required', 'integer', 'max:1440', 'gt:start_minute'],
            'description' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->overlapsAnotherEntry()) {
                    return;
                }

                $validator->errors()->add('start_minute', __('validation.custom.start_minute.overlap'));
            },
        ];
    }

    private function overlapsAnotherEntry(): bool
    {
        $current = $this->route('timeEntry');

        return $this->user()->timeEntries()
            ->whereDate('date', $this->string('date')->toString())
            ->when($current instanceof TimeEntry, fn ($query) => $query->whereKeyNot($current->id))
            ->where('start_minute', '<', $this->integer('end_minute'))
            ->where('end_minute', '>', $this->integer('start_minute'))
            ->exists();
    }
}
