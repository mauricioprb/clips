<?php

declare(strict_types=1);

use Appwrite\AppwriteException;

const MEETING = ['date' => '2026-03-02', 'startMinute' => 540, 'endMinute' => 660, 'description' => 'Reunião'];

beforeEach(function () {
    $this->owner = createTestUser();
    $this->other = createTestUser();
});

afterEach(function () {
    deleteTestUser($this->owner);
    deleteTestUser($this->other);
});

/**
 * @return list<array<string, mixed>>
 */
function entriesOn(array $month, string $date): array
{
    foreach ($month['days'] as $day) {
        if ($day['date'] === $date) {
            return $day['entries'];
        }
    }

    return [];
}

it('rejects callers without a session', function () {
    callTimesheet(null, 'GET', '/profile');
})->throws(AppwriteException::class);

it('rejects users outside the team', function () {
    $outsider = createTestUser(member: false);

    try {
        callTimesheet($outsider['session'], 'GET', '/profile');
    } finally {
        deleteTestUser($outsider);
    }
})->throws(AppwriteException::class);

it('creates the profile with the default holidays on first use', function () {
    $profile = callTimesheet($this->owner['session'], 'GET', '/profile');
    $holidays = callTimesheet($this->owner['session'], 'GET', '/holidays/2026')['data'];

    expect($profile['data']['weeklyWorkloadMinutes'])->toBe(1200)
        ->and(array_column($holidays['holidays'], 'name'))->toBe(['Corpus Christi', 'Revolução Farroupilha'])
        ->and($holidays['calendar'])->toContain(['date' => '2026-06-04', 'name' => 'Corpus Christi'])
        ->and($holidays['calendar'])->toContain(['date' => '2026-09-20', 'name' => 'Revolução Farroupilha']);
});

it('logs an entry and shows it in the month', function () {
    $created = callTimesheet($this->owner['session'], 'POST', '/entries', MEETING);
    $month = callTimesheet($this->owner['session'], 'GET', '/months/2026-03')['data'];

    expect($created['status'])->toBe(201)
        ->and($created['data'])->toMatchArray([...MEETING, 'source' => 'manual'])
        ->and(entriesOn($month, '2026-03-02'))->toHaveCount(1)
        ->and($month['days'][1]['minutes'])->toBe(120);
});

it('hides entries from other users', function () {
    callTimesheet($this->owner['session'], 'POST', '/entries', MEETING);

    expect(entriesOn(callTimesheet($this->other['session'], 'GET', '/months/2026-03')['data'], '2026-03-02'))->toBe([]);
});

it('does not let other users change or delete an entry', function () {
    $entryId = callTimesheet($this->owner['session'], 'POST', '/entries', MEETING)['data']['id'];

    expect(callTimesheet($this->other['session'], 'PUT', "/entries/{$entryId}", [...MEETING, 'description' => 'Invadido'])['status'])->toBe(404)
        ->and(callTimesheet($this->other['session'], 'DELETE', "/entries/{$entryId}")['status'])->toBe(404)
        ->and(entriesOn(callTimesheet($this->owner['session'], 'GET', '/months/2026-03')['data'], '2026-03-02')[0]['description'])->toBe('Reunião');
});

it('ignores the userId and source sent by the client', function () {
    $created = callTimesheet($this->owner['session'], 'POST', '/entries', [...MEETING, 'userId' => $this->other['id'], 'source' => 'schedule']);

    expect($created['data']['source'])->toBe('manual')
        ->and(entriesOn(callTimesheet($this->other['session'], 'GET', '/months/2026-03')['data'], '2026-03-02'))->toBe([]);
});

it('refuses a spoofed user id header', function () {
    callFunction('timesheet', $this->other['session'], 'GET', '/months/2026-03', null, ['x-appwrite-user-id' => $this->owner['id']]);
})->throws(AppwriteException::class, 'Headers must contain');

it('rejects overlapping entries', function () {
    callTimesheet($this->owner['session'], 'POST', '/entries', MEETING);

    expect(callTimesheet($this->owner['session'], 'POST', '/entries', [...MEETING, 'startMinute' => 600, 'endMinute' => 720])['status'])->toBe(422);
});

it('rejects a body that is not JSON', function () {
    expect(callFunction('timesheet', $this->owner['session'], 'POST', '/entries', 'isso não é json')['status'])->toBe(400);
});

it('rejects invalid entries', function (array $changes) {
    expect(callTimesheet($this->owner['session'], 'POST', '/entries', [...MEETING, ...$changes])['status'])->toBe(422);
})->with([
    'impossible date' => [['date' => '2026-02-30']],
    'date as text' => [['date' => 'ontem']],
    'end before start' => [['startMinute' => 700, 'endMinute' => 600]],
    'clock as text' => [['startMinute' => '08:00']],
    'start past midnight' => [['startMinute' => 1440, 'endMinute' => 1440]],
    'empty description' => [['description' => '   ']],
    'description as list' => [['description' => ['a']]],
    'long description' => [['description' => str_repeat('a', 256)]],
]);

it('answers 405 and 404 for unknown routes', function () {
    expect(callTimesheet($this->owner['session'], 'DELETE', '/profile')['status'])->toBe(405)
        ->and(callTimesheet($this->owner['session'], 'GET', '/months/2026-13')['status'])->toBe(404)
        ->and(callTimesheet($this->owner['session'], 'GET', '/nada')['status'])->toBe(404);
});

it('fills the month around manual entries and only once', function () {
    callTimesheet($this->owner['session'], 'POST', '/entries', MEETING);
    callTimesheet($this->owner['session'], 'POST', '/default-activities', ['description' => 'Pesquisa', 'priority' => 'medium']);

    $first = callTimesheet($this->owner['session'], 'POST', '/months/2026-03/fill');
    $second = callTimesheet($this->owner['session'], 'POST', '/months/2026-03/fill');
    $monday = entriesOn(callTimesheet($this->owner['session'], 'GET', '/months/2026-03')['data'], '2026-03-02');

    expect($first['data']['created'])->toBe(23)
        ->and($second['data']['created'])->toBe(0)
        ->and(array_map(fn(array $entry) => [$entry['startMinute'], $entry['endMinute'], $entry['source']], $monday))
        ->toBe([[480, 540, 'activity'], [540, 660, 'manual'], [660, 720, 'activity']]);
});

it('generates the monthly report as a PDF', function () {
    callTimesheet($this->owner['session'], 'POST', '/entries', MEETING);

    $report = callTimesheet($this->owner['session'], 'GET', '/months/2026-03/report');

    expect($report['status'])->toBe(200)
        ->and($report['data']['fileName'])->toBe('relatorio-2026-03.pdf')
        ->and(substr(base64_decode($report['data']['content']), 0, 5))->toBe('%PDF-');
});

it('updates the scholarship profile', function () {
    $profile = [
        'advisorName' => 'Profa. Ana Souza',
        'scholarshipName' => 'PROBIC',
        'laboratories' => ['Lab. de Nanociências', 'Sala 204'],
        'weeklyWorkloadMinutes' => 1200,
    ];

    $updated = callTimesheet($this->owner['session'], 'PUT', '/profile', $profile);
    $month = callTimesheet($this->owner['session'], 'GET', '/months/2026-03')['data'];

    expect($updated['data'])->toBe($profile)
        ->and($month['setup']['profile'])->toBeTrue();
});

it('rejects invalid profiles', function (array $changes) {
    $profile = ['advisorName' => 'Profa. Ana Souza', 'scholarshipName' => 'PROBIC', 'laboratories' => ['Sala 204'], 'weeklyWorkloadMinutes' => 1200];

    expect(callTimesheet($this->owner['session'], 'PUT', '/profile', [...$profile, ...$changes])['status'])->toBe(422);
})->with([
    'no advisor' => [['advisorName' => '']],
    'no laboratory' => [['laboratories' => []]],
    'repeated laboratory' => [['laboratories' => ['Sala 204', 'Sala 204']]],
    'laboratories as object' => [['laboratories' => ['sala' => 'Sala 204']]],
    'workload off the half hour' => [['weeklyWorkloadMinutes' => 1210]],
    'workload too high' => [['weeklyWorkloadMinutes' => 2670]],
    'workload as hours' => [['weeklyWorkloadMinutes' => '20']],
]);

it('keeps weekly slots and default activities private', function () {
    $slotId = callTimesheet($this->owner['session'], 'POST', '/weekly-slots', ['weekday' => 2, 'startMinute' => 840, 'endMinute' => 960, 'description' => 'Aula'])['data']['id'];
    $activityId = callTimesheet($this->owner['session'], 'POST', '/default-activities', ['description' => 'Pesquisa', 'priority' => 'high'])['data']['id'];

    expect(callTimesheet($this->other['session'], 'GET', '/weekly-slots')['data']['slots'])->toBe([])
        ->and(callTimesheet($this->other['session'], 'GET', '/default-activities')['data']['activities'])->toBe([])
        ->and(callTimesheet($this->other['session'], 'DELETE', "/weekly-slots/{$slotId}")['status'])->toBe(404)
        ->and(callTimesheet($this->other['session'], 'PUT', "/default-activities/{$activityId}", ['description' => 'Outra', 'priority' => 'low'])['status'])->toBe(404)
        ->and(callTimesheet($this->owner['session'], 'GET', '/weekly-slots')['data']['slots'])->toHaveCount(1);
});

it('rejects invalid weekly slots, activities and holidays', function (string $path, array $body) {
    expect(callTimesheet($this->owner['session'], 'POST', $path, $body)['status'])->toBe(422);
})->with([
    'slot on saturday' => ['/weekly-slots', ['weekday' => 6, 'startMinute' => 840, 'endMinute' => 960, 'description' => 'Aula']],
    'slot validity reversed' => ['/weekly-slots', ['weekday' => 2, 'startMinute' => 840, 'endMinute' => 960, 'description' => 'Aula', 'validFrom' => '2026-05-01', 'validUntil' => '2026-04-01']],
    'unknown priority' => ['/default-activities', ['description' => 'Pesquisa', 'priority' => 'urgente']],
    'holiday without date' => ['/holidays', ['name' => 'Recesso', 'recurrence' => 'once']],
    'unknown recurrence' => ['/holidays', ['name' => 'Recesso', 'recurrence' => 'monthly', 'date' => '2026-07-10']],
]);

it('deletes the data of a deleted user', function () {
    $user = createTestUser();
    callTimesheet($user['session'], 'POST', '/entries', MEETING);
    callTimesheet($user['session'], 'POST', '/weekly-slots', ['weekday' => 2, 'startMinute' => 840, 'endMinute' => 960, 'description' => 'Aula']);
    callTimesheet($user['session'], 'POST', '/default-activities', ['description' => 'Pesquisa', 'priority' => 'low']);
    $tables = ['profiles', 'timeEntries', 'weeklySlots', 'defaultActivities', 'holidays'];

    expect(array_map(fn(string $table) => countRowsOf($table, $user['id']), $tables))->toBe([1, 1, 1, 1, 2]);

    deleteTestUser($user);

    expect(waitUntil(fn() => array_sum(array_map(fn(string $table) => countRowsOf($table, $user['id']), $tables)) === 0))->toBeTrue();
});
