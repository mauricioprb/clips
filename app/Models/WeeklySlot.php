<?php

declare(strict_types=1);

namespace App\Models;

use App\Policies\OwnedByUserPolicy;
use Carbon\CarbonInterface;
use Database\Factories\WeeklySlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['weekday', 'start_minute', 'end_minute', 'description', 'valid_from', 'valid_until'])]
#[UsePolicy(OwnedByUserPolicy::class)]
class WeeklySlot extends Model
{
    /** @use HasFactory<WeeklySlotFactory> */
    use HasFactory;

    use HasUuids;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appliesTo(CarbonInterface $day): bool
    {
        return $day->dayOfWeek === $this->weekday
            && ($this->valid_from === null || $day->greaterThanOrEqualTo($this->valid_from))
            && ($this->valid_until === null || $day->lessThanOrEqualTo($this->valid_until));
    }

    public function durationMinutes(): int
    {
        return $this->end_minute - $this->start_minute;
    }

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'start_minute' => 'integer',
            'end_minute' => 'integer',
            'valid_from' => 'date:Y-m-d',
            'valid_until' => 'date:Y-m-d',
        ];
    }
}
