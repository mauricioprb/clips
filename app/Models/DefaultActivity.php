<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Priority;
use App\Policies\OwnedByUserPolicy;
use Database\Factories\DefaultActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['description', 'priority'])]
#[UsePolicy(OwnedByUserPolicy::class)]
class DefaultActivity extends Model
{
    /** @use HasFactory<DefaultActivityFactory> */
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
            'priority' => Priority::class,
        ];
    }
}
