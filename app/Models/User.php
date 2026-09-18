<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'advisor_name',
    'scholarship_name',
    'laboratories',
    'weekly_workload_minutes',
    'is_admin',
    'is_active',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasUuids;
    use Notifiable;

    /** @return HasMany<TimeEntry, $this> */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /** @return HasMany<WeeklySlot, $this> */
    public function weeklySlots(): HasMany
    {
        return $this->hasMany(WeeklySlot::class);
    }

    /** @return HasMany<DefaultActivity, $this> */
    public function defaultActivities(): HasMany
    {
        return $this->hasMany(DefaultActivity::class);
    }

    /** @return HasMany<Holiday, $this> */
    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    public function status(): UserStatus
    {
        return match (true) {
            ! $this->is_active => UserStatus::Blocked,
            $this->password_set_at === null => UserStatus::InvitationPending,
            default => UserStatus::Active,
        };
    }

    public function hasScholarshipProfile(): bool
    {
        return filled($this->advisor_name) && filled($this->scholarship_name) && filled($this->laboratories);
    }

    public function dailyWorkloadMinutes(): int
    {
        return intdiv($this->weekly_workload_minutes, 5);
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'laboratories' => 'array',
            'weekly_workload_minutes' => 'integer',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'invitation_sent_at' => 'datetime',
            'password_set_at' => 'datetime',
        ];
    }
}
