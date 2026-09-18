<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DefaultActivity;
use App\Models\Holiday;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\WeeklySlot;
use Illuminate\Auth\Access\Response;

class OwnedByUserPolicy
{
    public function update(User $user, TimeEntry|WeeklySlot|DefaultActivity|Holiday $model): Response
    {
        return $this->owns($user, $model);
    }

    public function delete(User $user, TimeEntry|WeeklySlot|DefaultActivity|Holiday $model): Response
    {
        return $this->owns($user, $model);
    }

    private function owns(User $user, TimeEntry|WeeklySlot|DefaultActivity|Holiday $model): Response
    {
        return $model->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }
}
