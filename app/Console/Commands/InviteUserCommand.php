<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\InviteUser;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('users:invite {email} {name} {--admin : Allow the person to invite and manage users}')]
#[Description('Invite a person to Clips by email')]
class InviteUserCommand extends Command
{
    public function handle(InviteUser $inviteUser): int
    {
        $validator = Validator::make(
            ['email' => $this->argument('email'), 'name' => $this->argument('name')],
            ['email' => ['required', 'email', 'max:255', 'unique:' . User::class], 'name' => ['required', 'string', 'max:255']],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        ['url' => $url] = $inviteUser->execute($this->argument('name'), $this->argument('email'), (bool) $this->option('admin'));

        $this->components->info('Invitation sent.');
        $this->line($url);

        return self::SUCCESS;
    }
}
