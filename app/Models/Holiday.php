<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HolidayRecurrence;
use App\Policies\OwnedByUserPolicy;
use Database\Factories\HolidayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'recurrence', 'date'])]
#[UsePolicy(OwnedByUserPolicy::class)]
class Holiday extends Model
{
    /** @use HasFactory<HolidayFactory> */
    use HasFactory;

    use HasUuids;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'recurrence' => HolidayRecurrence::class,
            'date' => 'date:Y-m-d',
        ];
    }
}
