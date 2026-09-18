<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\HolidayRecurrence;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InviteUser
{
    public function __construct(
        private readonly SendInvitation $sendInvitation,
    ) {}

    /** @return array{user: User, url: string} */
    public function execute(string $name, string $email, bool $isAdmin = false): array
    {
        $user = DB::transaction(function () use ($name, $email, $isAdmin): User {
            $user = User::create([
                'name' => $name,
                'email' => Str::lower($email),
                'password' => Str::password(64),
                'is_admin' => $isAdmin,
                'is_active' => true,
            ]);

            $user->holidays()->createMany([
                ['name' => 'Revolução Farroupilha', 'recurrence' => HolidayRecurrence::Yearly, 'date' => now()->year . '-09-20'],
                ['name' => 'Corpus Christi', 'recurrence' => HolidayRecurrence::CorpusChristi, 'date' => null],
            ]);

            return $user;
        });

        return ['user' => $user, 'url' => $this->sendInvitation->execute($user)];
    }
}
