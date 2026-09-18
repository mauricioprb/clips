<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntrySource;
use App\Policies\OwnedByUserPolicy;
use Carbon\CarbonInterface;
use Database\Factories\TimeEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['date', 'start_minute', 'end_minute', 'description', 'source'])]
#[UsePolicy(OwnedByUserPolicy::class)]
class TimeEntry extends Model
{
    /** @use HasFactory<TimeEntryFactory> */
    use HasFactory;

    use HasUuids;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function durationMinutes(): int
    {
        return $this->end_minute - $this->start_minute;
    }

    public function overlaps(int $startMinute, int $endMinute): bool
    {
        return $startMinute < $this->end_minute && $endMinute > $this->start_minute;
    }

    /** @param Builder<self> $query */
    #[Scope]
    protected function within(Builder $query, CarbonInterface $from, CarbonInterface $until): void
    {
        $query->whereDate('date', '>=', $from->toDateString())->whereDate('date', '<=', $until->toDateString());
    }

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'start_minute' => 'integer',
            'end_minute' => 'integer',
            'source' => EntrySource::class,
        ];
    }
}
