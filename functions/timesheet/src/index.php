<?php

declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

use Appwrite\AppwriteException;
use Appwrite\Client;
use Appwrite\ID;
use Appwrite\Query;
use Appwrite\Services\TablesDB;
use Appwrite\Services\Users;
use Clips\DefaultActivity;
use Clips\EntrySource;
use Clips\Holiday;
use Clips\HolidayCalendar;
use Clips\HolidayRecurrence;
use Clips\Month;
use Clips\MonthFiller;
use Clips\MonthlyReport;
use Clips\MonthSummary;
use Clips\Priority;
use Clips\TimeEntry;
use Clips\WeeklySlot;

return function ($context) {
    $databaseId = 'clips';
    $notFoundStatus = 404;
    $conflictStatus = 409;
    $descriptionMaxLength = 255;
    $holidayNameMaxLength = 120;
    $profileTextMaxLength = 255;
    $laboratoryMaxLength = 120;
    $laboratoriesLimit = 10;
    $itemsLimit = 50;
    $entriesPerDayLimit = 24;
    $monthEntriesLimit = 1000;
    $batchSize = 100;
    $defaultWeeklyWorkloadMinutes = 1200;
    $workdaysPerWeek = 5;
    $rowId = '([A-Za-z0-9][A-Za-z0-9._-]{0,35})';
    $requestId = $context->req->headers['x-appwrite-execution-id'] ?? '';

    $json = fn(array $body, int $statusCode = 200) => $context->res->text(
        json_encode($body, JSON_THROW_ON_ERROR),
        $statusCode,
        ['content-type' => 'application/json'],
    );
    $invalid = fn(string $message) => $json(['error' => $message], 422);
    $notFound = fn() => $json(['error' => 'Registro não encontrado.'], $notFoundStatus);

    $userId = $context->req->headers['x-appwrite-user-id'] ?? '';

    if ($userId === '') {
        return $json(['error' => 'Entre na sua conta para continuar.'], 401);
    }

    $client = (new Client())
        ->setEndpoint(getenv('APPWRITE_FUNCTION_API_ENDPOINT'))
        ->setProject(getenv('APPWRITE_FUNCTION_PROJECT_ID'))
        ->setKey($context->req->headers['x-appwrite-key']);
    $tablesDB = new TablesDB($client);

    $readBody = function () use ($context): ?array {
        $body = json_decode($context->req->bodyText, true);

        return is_array($body) ? $body : null;
    };

    $text = function (mixed $value, int $maxLength): ?string {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' || mb_strlen($value) > $maxLength ? null : $value;
    };

    $integerBetween = fn(mixed $value, int $min, int $max): ?int => is_int($value) && $value >= $min && $value <= $max ? $value : null;

    $date = function (mixed $value): ?string {
        if (!is_string($value) || preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $parts) !== 1) {
            return null;
        }

        return checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1]) ? $value : null;
    };

    $ownRows = fn(string $tableId, array $queries = [], ?int $limit = null) => $tablesDB->listRows($databaseId, $tableId, [
        Query::equal('userId', [$userId]),
        ...$queries,
        Query::limit($limit ?? $itemsLimit),
    ])->rows;

    $ownedRow = function (string $tableId, string $id) use ($tablesDB, $databaseId, $userId) {
        $row = $tablesDB->getRow($databaseId, $tableId, $id);

        return $row->data['userId'] === $userId ? $row : null;
    };

    $deleteOwned = function (string $tableId, string $id) use ($context, $tablesDB, $databaseId, $ownedRow, $notFound) {
        if ($ownedRow($tableId, $id) === null) {
            return $notFound();
        }

        $tablesDB->deleteRow($databaseId, $tableId, $id);

        return $context->res->text('', 204);
    };

    $profile = null;
    $profileRow = function () use (&$profile, $tablesDB, $databaseId, $userId, $notFoundStatus, $conflictStatus, $defaultWeeklyWorkloadMinutes) {
        if ($profile !== null) {
            return $profile;
        }

        try {
            return $profile = $tablesDB->getRow($databaseId, 'profiles', $userId);
        } catch (AppwriteException $error) {
            if ($error->getCode() !== $notFoundStatus) {
                throw $error;
            }
        }

        $transaction = $tablesDB->createTransaction();

        try {
            $tablesDB->createRow($databaseId, 'profiles', $userId, [
                'userId' => $userId,
                'weeklyWorkloadMinutes' => $defaultWeeklyWorkloadMinutes,
            ], transactionId: $transaction->id);
            $tablesDB->createRows($databaseId, 'holidays', [
                ['userId' => $userId, 'name' => 'Revolução Farroupilha', 'recurrence' => HolidayRecurrence::Yearly->value, 'date' => gmdate('Y') . '-09-20'],
                ['userId' => $userId, 'name' => 'Corpus Christi', 'recurrence' => HolidayRecurrence::CorpusChristi->value, 'date' => null],
            ], $transaction->id);
            $tablesDB->updateTransaction($transaction->id, commit: true);
        } catch (AppwriteException $error) {
            if ($error->getCode() !== $conflictStatus) {
                throw $error;
            }
        }

        return $profile = $tablesDB->getRow($databaseId, 'profiles', $userId);
    };

    $toProfile = fn($row) => [
        'advisorName' => $row->data['advisorName'] ?? null,
        'scholarshipName' => $row->data['scholarshipName'] ?? null,
        'laboratories' => $row->data['laboratories'] ?? [],
        'weeklyWorkloadMinutes' => $row->data['weeklyWorkloadMinutes'],
    ];

    $entryFrom = fn($row) => new TimeEntry(
        $row->data['date'],
        $row->data['startMinute'],
        $row->data['endMinute'],
        $row->data['description'],
        EntrySource::from($row->data['source']),
        $row->id,
    );

    $toEntry = fn(TimeEntry $entry) => [
        'id' => $entry->id,
        'date' => $entry->date,
        'startMinute' => $entry->startMinute,
        'endMinute' => $entry->endMinute,
        'description' => $entry->description,
        'source' => $entry->source->value,
    ];

    $slotFrom = fn($row) => new WeeklySlot(
        $row->data['weekday'],
        $row->data['startMinute'],
        $row->data['endMinute'],
        $row->data['description'],
        $row->data['validFrom'] ?? null,
        $row->data['validUntil'] ?? null,
    );

    $toSlot = fn($row) => [
        'id' => $row->id,
        'weekday' => $row->data['weekday'],
        'startMinute' => $row->data['startMinute'],
        'endMinute' => $row->data['endMinute'],
        'description' => $row->data['description'],
        'validFrom' => $row->data['validFrom'] ?? null,
        'validUntil' => $row->data['validUntil'] ?? null,
    ];

    $activityFrom = fn($row) => new DefaultActivity($row->data['description'], Priority::from($row->data['priority']));

    $toActivity = fn($row) => [
        'id' => $row->id,
        'description' => $row->data['description'],
        'priority' => $row->data['priority'],
    ];

    $holidayFrom = fn($row) => new Holiday($row->data['name'], HolidayRecurrence::from($row->data['recurrence']), $row->data['date'] ?? null);

    $toHoliday = fn($row) => [
        'id' => $row->id,
        'name' => $row->data['name'],
        'recurrence' => $row->data['recurrence'],
        'date' => $row->data['date'] ?? null,
    ];

    $calendar = fn() => new HolidayCalendar(array_map($holidayFrom, $ownRows('holidays')));

    $monthEntries = fn(Month $month) => array_map($entryFrom, $ownRows('timeEntries', [
        Query::greaterThanEqual('date', $month->firstDay()),
        Query::lessThanEqual('date', $month->lastDay()),
    ], $monthEntriesLimit));

    $summaryOf = fn(Month $month, $profile) => MonthSummary::of(
        $month,
        $monthEntries($month),
        $calendar(),
        intdiv($profile->data['weeklyWorkloadMinutes'], $workdaysPerWeek),
    );

    $showMonth = function (string $value) use ($json, $notFound, $profileRow, $summaryOf, $ownRows, $toEntry, $workdaysPerWeek) {
        $month = Month::fromString($value);

        if ($month === null) {
            return $notFound();
        }

        $profile = $profileRow();
        $summary = $summaryOf($month, $profile);
        $slots = $ownRows('weeklySlots');
        $activities = $ownRows('defaultActivities');
        $suggestions = array_values(array_unique(array_map(fn($row) => $row->data['description'], [...$activities, ...$slots])));
        sort($suggestions);

        return $json([
            'month' => $month->format('Y-m'),
            'days' => array_map(fn(array $day) => [
                'date' => $day['date'],
                'isWorkday' => $day['isWorkday'],
                'holiday' => $day['holiday'],
                'minutes' => $day['minutes'],
                'entries' => array_map($toEntry, $day['entries']),
            ], $summary['days']),
            'loggedMinutes' => $summary['loggedMinutes'],
            'workdayMinutes' => $summary['workdayMinutes'],
            'targetMinutes' => $summary['targetMinutes'],
            'dailyTargetMinutes' => intdiv($profile->data['weeklyWorkloadMinutes'], $workdaysPerWeek),
            'setup' => [
                'profile' => ($profile->data['advisorName'] ?? '') !== ''
                    && ($profile->data['scholarshipName'] ?? '') !== ''
                    && ($profile->data['laboratories'] ?? []) !== [],
                'schedule' => $slots !== [],
                'activities' => $activities !== [],
            ],
            'suggestions' => $suggestions,
        ]);
    };

    $fillMonth = function (string $value) use ($json, $notFound, $tablesDB, $databaseId, $userId, $batchSize, $profileRow, $monthEntries, $calendar, $ownRows, $slotFrom, $activityFrom, $workdaysPerWeek) {
        $month = Month::fromString($value);

        if ($month === null) {
            return $notFound();
        }

        $plan = MonthFiller::fill(
            $month,
            array_map($slotFrom, $ownRows('weeklySlots')),
            array_map($activityFrom, $ownRows('defaultActivities')),
            $monthEntries($month),
            $calendar(),
            intdiv($profileRow()->data['weeklyWorkloadMinutes'], $workdaysPerWeek),
        );

        if ($plan['create'] === [] && $plan['reschedule'] === []) {
            return $json(['created' => 0]);
        }

        $toRow = fn(TimeEntry $entry) => [
            'userId' => $userId,
            'date' => $entry->date,
            'startMinute' => $entry->startMinute,
            'endMinute' => $entry->endMinute,
            'description' => $entry->description,
            'source' => $entry->source->value,
        ];
        $transaction = $tablesDB->createTransaction();

        foreach (array_chunk($plan['create'], $batchSize) as $entries) {
            $tablesDB->createRows($databaseId, 'timeEntries', array_map($toRow, $entries), $transaction->id);
        }

        foreach (array_chunk($plan['reschedule'], $batchSize) as $entries) {
            $tablesDB->upsertRows($databaseId, 'timeEntries', array_map(fn(TimeEntry $entry) => ['$id' => $entry->id, ...$toRow($entry)], $entries), $transaction->id);
        }

        $tablesDB->updateTransaction($transaction->id, commit: true);

        return $json(['created' => count($plan['create'])]);
    };

    $monthReport = function (string $value) use ($json, $notFound, $client, $userId, $profileRow, $summaryOf, $toProfile) {
        $month = Month::fromString($value);

        if ($month === null) {
            return $notFound();
        }

        $profile = $profileRow();
        $user = (new Users($client))->get($userId);
        $pdf = MonthlyReport::pdf(
            ['name' => $user->name !== '' ? $user->name : $user->email, ...$toProfile($profile)],
            $month,
            $summaryOf($month, $profile),
        );

        return $json(['fileName' => "relatorio-{$month->format('Y-m')}.pdf", 'content' => base64_encode($pdf)]);
    };

    $entryInput = function (array $body) use ($text, $integerBetween, $date, $descriptionMaxLength): array|string {
        $entryDate = $date($body['date'] ?? null);
        $start = $integerBetween($body['startMinute'] ?? null, 0, 1439);
        $end = $integerBetween($body['endMinute'] ?? null, 1, 1440);
        $description = $text($body['description'] ?? null, $descriptionMaxLength);

        return match (true) {
            $entryDate === null => 'Informe a data no formato AAAA-MM-DD.',
            $start === null || $end === null || $end <= $start => 'Informe um horário de início anterior ao de término.',
            $description === null => "Descreva a atividade com até {$descriptionMaxLength} caracteres.",
            default => ['date' => $entryDate, 'startMinute' => $start, 'endMinute' => $end, 'description' => $description],
        };
    };

    $saveEntry = function (?string $id) use ($json, $invalid, $notFound, $readBody, $entryInput, $ownRows, $ownedRow, $tablesDB, $databaseId, $userId, $entriesPerDayLimit, $entryFrom, $toEntry) {
        if ($id !== null && $ownedRow('timeEntries', $id) === null) {
            return $notFound();
        }

        $body = $readBody();

        if ($body === null) {
            return $json(['error' => 'Envie os dados do lançamento em JSON.'], 400);
        }

        $input = $entryInput($body);

        if (is_string($input)) {
            return $invalid($input);
        }

        $sameDay = array_filter(
            $ownRows('timeEntries', [Query::equal('date', [$input['date']])], $entriesPerDayLimit + 1),
            fn($row) => $row->id !== $id,
        );

        if (array_any($sameDay, fn($row) => $input['startMinute'] < $row->data['endMinute'] && $input['endMinute'] > $row->data['startMinute'])) {
            return $invalid('Este horário se sobrepõe a outro lançamento do dia.');
        }

        if (count($sameDay) >= $entriesPerDayLimit) {
            return $invalid("Cada dia aceita até {$entriesPerDayLimit} lançamentos.");
        }

        $data = [...$input, 'userId' => $userId, 'source' => EntrySource::Manual->value];
        $row = $id === null
            ? $tablesDB->createRow($databaseId, 'timeEntries', ID::unique(), $data)
            : $tablesDB->updateRow($databaseId, 'timeEntries', $id, $data);

        return $json($toEntry($entryFrom($row)), $id === null ? 201 : 200);
    };

    $showProfile = fn() => $json($toProfile($profileRow()));

    $updateProfile = function () use ($json, $invalid, $readBody, $text, $tablesDB, $databaseId, $userId, $profileRow, $toProfile, $profileTextMaxLength, $laboratoryMaxLength, $laboratoriesLimit) {
        $body = $readBody();

        if ($body === null) {
            return $json(['error' => 'Envie os dados do perfil em JSON.'], 400);
        }

        $advisorName = $text($body['advisorName'] ?? null, $profileTextMaxLength);
        $scholarshipName = $text($body['scholarshipName'] ?? null, $profileTextMaxLength);
        $laboratories = is_array($body['laboratories'] ?? null) && array_is_list($body['laboratories'])
            ? array_map(fn($laboratory) => $text($laboratory, $laboratoryMaxLength), $body['laboratories'])
            : [null];
        $weeklyWorkload = $body['weeklyWorkloadMinutes'] ?? null;

        $error = match (true) {
            $advisorName === null => "Informe o nome do orientador com até {$profileTextMaxLength} caracteres.",
            $scholarshipName === null => "Informe o nome da bolsa com até {$profileTextMaxLength} caracteres.",
            $laboratories === [] || count($laboratories) > $laboratoriesLimit || in_array(null, $laboratories, true)
                => "Informe de 1 a {$laboratoriesLimit} laboratórios ou salas, cada um com até {$laboratoryMaxLength} caracteres.",
            count(array_unique($laboratories)) !== count($laboratories) => 'Os laboratórios ou salas não podem se repetir.',
            !is_int($weeklyWorkload) || $weeklyWorkload < 60 || $weeklyWorkload > 2640 || $weeklyWorkload % 30 !== 0
                => 'Informe a carga horária semanal entre 1 e 44 horas, em intervalos de meia hora.',
            default => null,
        };

        if ($error !== null) {
            return $invalid($error);
        }

        $row = $tablesDB->updateRow($databaseId, 'profiles', $userId, [
            'advisorName' => $advisorName,
            'scholarshipName' => $scholarshipName,
            'laboratories' => $laboratories,
            'weeklyWorkloadMinutes' => $weeklyWorkload,
        ]);

        return $json($toProfile($row));
    };

    $listSlots = function () use ($json, $ownRows, $toSlot) {
        $slots = array_map($toSlot, $ownRows('weeklySlots'));
        usort($slots, fn(array $a, array $b) => [$a['weekday'], $a['startMinute']] <=> [$b['weekday'], $b['startMinute']]);

        return $json(['slots' => $slots]);
    };

    $saveSlot = function (?string $id) use ($json, $invalid, $notFound, $readBody, $text, $integerBetween, $date, $ownRows, $ownedRow, $tablesDB, $databaseId, $userId, $descriptionMaxLength, $itemsLimit, $toSlot) {
        if ($id !== null && $ownedRow('weeklySlots', $id) === null) {
            return $notFound();
        }

        $body = $readBody();

        if ($body === null) {
            return $json(['error' => 'Envie os dados do horário em JSON.'], 400);
        }

        $weekday = $integerBetween($body['weekday'] ?? null, 1, 5);
        $start = $integerBetween($body['startMinute'] ?? null, 0, 1439);
        $end = $integerBetween($body['endMinute'] ?? null, 1, 1440);
        $description = $text($body['description'] ?? null, $descriptionMaxLength);
        $validFrom = ($body['validFrom'] ?? null) === null ? null : $date($body['validFrom']);
        $validUntil = ($body['validUntil'] ?? null) === null ? null : $date($body['validUntil']);

        $error = match (true) {
            $weekday === null => 'Escolha um dia da semana de segunda a sexta.',
            $start === null || $end === null || $end <= $start => 'Informe um horário de início anterior ao de término.',
            $description === null => "Descreva a atividade com até {$descriptionMaxLength} caracteres.",
            ($body['validFrom'] ?? null) !== null && $validFrom === null,
            ($body['validUntil'] ?? null) !== null && $validUntil === null => 'Informe as datas de validade no formato AAAA-MM-DD.',
            $validFrom !== null && $validUntil !== null && $validUntil < $validFrom => 'O fim da validade precisa ser depois do início.',
            $id === null && count($ownRows('weeklySlots')) >= $itemsLimit => "A grade semanal aceita até {$itemsLimit} horários.",
            default => null,
        };

        if ($error !== null) {
            return $invalid($error);
        }

        $data = [
            'userId' => $userId,
            'weekday' => $weekday,
            'startMinute' => $start,
            'endMinute' => $end,
            'description' => $description,
            'validFrom' => $validFrom,
            'validUntil' => $validUntil,
        ];
        $row = $id === null
            ? $tablesDB->createRow($databaseId, 'weeklySlots', ID::unique(), $data)
            : $tablesDB->updateRow($databaseId, 'weeklySlots', $id, $data);

        return $json($toSlot($row), $id === null ? 201 : 200);
    };

    $listActivities = function () use ($json, $ownRows, $toActivity) {
        $activities = array_map($toActivity, $ownRows('defaultActivities'));
        usort($activities, fn(array $a, array $b) => Priority::from($b['priority'])->weight() <=> Priority::from($a['priority'])->weight()
            ?: strcmp($a['description'], $b['description']));

        return $json(['activities' => $activities]);
    };

    $saveActivity = function (?string $id) use ($json, $invalid, $notFound, $readBody, $text, $ownRows, $ownedRow, $tablesDB, $databaseId, $userId, $descriptionMaxLength, $itemsLimit, $toActivity) {
        if ($id !== null && $ownedRow('defaultActivities', $id) === null) {
            return $notFound();
        }

        $body = $readBody();

        if ($body === null) {
            return $json(['error' => 'Envie os dados da atividade em JSON.'], 400);
        }

        $description = $text($body['description'] ?? null, $descriptionMaxLength);
        $priority = is_string($body['priority'] ?? null) ? Priority::tryFrom($body['priority']) : null;

        $error = match (true) {
            $description === null => "Descreva a atividade com até {$descriptionMaxLength} caracteres.",
            $priority === null => 'Escolha a prioridade alta, média ou baixa.',
            $id === null && count($ownRows('defaultActivities')) >= $itemsLimit => "A lista aceita até {$itemsLimit} atividades padrão.",
            default => null,
        };

        if ($error !== null) {
            return $invalid($error);
        }

        $data = ['userId' => $userId, 'description' => $description, 'priority' => $priority->value];
        $row = $id === null
            ? $tablesDB->createRow($databaseId, 'defaultActivities', ID::unique(), $data)
            : $tablesDB->updateRow($databaseId, 'defaultActivities', $id, $data);

        return $json($toActivity($row), $id === null ? 201 : 200);
    };

    $listHolidays = function (string $year) use ($json, $invalid, $ownRows, $toHoliday, $holidayFrom) {
        $year = (int) $year;

        if ($year < 2000 || $year > 2100) {
            return $invalid('Escolha um ano entre 2000 e 2100.');
        }

        $rows = $ownRows('holidays', [Query::orderAsc('name')]);
        $calendar = (new HolidayCalendar(array_map($holidayFrom, $rows)))->forYear($year);

        return $json([
            'year' => $year,
            'holidays' => array_map($toHoliday, $rows),
            'calendar' => array_map(fn(string $date, string $name) => ['date' => $date, 'name' => $name], array_keys($calendar), $calendar),
        ]);
    };

    $createHoliday = function () use ($json, $invalid, $readBody, $text, $date, $ownRows, $tablesDB, $databaseId, $userId, $holidayNameMaxLength, $itemsLimit, $toHoliday) {
        $body = $readBody();

        if ($body === null) {
            return $json(['error' => 'Envie os dados do feriado em JSON.'], 400);
        }

        $name = $text($body['name'] ?? null, $holidayNameMaxLength);
        $recurrence = is_string($body['recurrence'] ?? null) ? HolidayRecurrence::tryFrom($body['recurrence']) : null;
        $holidayDate = $recurrence === HolidayRecurrence::CorpusChristi ? null : $date($body['date'] ?? null);

        $error = match (true) {
            $name === null => "Informe o nome do feriado com até {$holidayNameMaxLength} caracteres.",
            $recurrence === null => 'Escolha se o feriado acontece uma vez, todo ano ou no Corpus Christi.',
            $recurrence !== HolidayRecurrence::CorpusChristi && $holidayDate === null => 'Informe a data do feriado no formato AAAA-MM-DD.',
            count($ownRows('holidays')) >= $itemsLimit => "A lista aceita até {$itemsLimit} feriados.",
            default => null,
        };

        if ($error !== null) {
            return $invalid($error);
        }

        $row = $tablesDB->createRow($databaseId, 'holidays', ID::unique(), [
            'userId' => $userId,
            'name' => $name,
            'recurrence' => $recurrence->value,
            'date' => $holidayDate,
        ]);

        return $json($toHoliday($row), 201);
    };

    $routes = [
        '#^/months/(\d{4}-\d{2})$#' => ['GET' => $showMonth],
        '#^/months/(\d{4}-\d{2})/fill$#' => ['POST' => $fillMonth],
        '#^/months/(\d{4}-\d{2})/report$#' => ['GET' => $monthReport],
        '#^/entries$#' => ['POST' => fn() => $saveEntry(null)],
        "#^/entries/{$rowId}$#" => ['PUT' => $saveEntry, 'DELETE' => fn(string $id) => $deleteOwned('timeEntries', $id)],
        '#^/profile$#' => ['GET' => $showProfile, 'PUT' => $updateProfile],
        '#^/weekly-slots$#' => ['GET' => $listSlots, 'POST' => fn() => $saveSlot(null)],
        "#^/weekly-slots/{$rowId}$#" => ['PUT' => $saveSlot, 'DELETE' => fn(string $id) => $deleteOwned('weeklySlots', $id)],
        '#^/default-activities$#' => ['GET' => $listActivities, 'POST' => fn() => $saveActivity(null)],
        "#^/default-activities/{$rowId}$#" => ['PUT' => $saveActivity, 'DELETE' => fn(string $id) => $deleteOwned('defaultActivities', $id)],
        '#^/holidays$#' => ['POST' => $createHoliday],
        '#^/holidays/(\d{4})$#' => ['GET' => $listHolidays],
        "#^/holidays/{$rowId}$#" => ['DELETE' => fn(string $id) => $deleteOwned('holidays', $id)],
    ];

    try {
        $pathExists = false;

        foreach ($routes as $pattern => $handlers) {
            if (preg_match($pattern, $context->req->path, $matches) !== 1) {
                continue;
            }

            if (isset($handlers[$context->req->method])) {
                $profileRow();

                return $handlers[$context->req->method](...array_slice($matches, 1));
            }

            $pathExists = true;
        }

        return $pathExists
            ? $json(['error' => 'Método não permitido.'], 405)
            : $json(['error' => 'Rota não encontrada.'], $notFoundStatus);
    } catch (Throwable $error) {
        if ($error instanceof AppwriteException && $error->getCode() === $notFoundStatus) {
            return $notFound();
        }

        $context->error("{$requestId} " . $error::class . ": {$error->getMessage()}");

        return $json(['error' => 'Não foi possível concluir a operação.', 'requestId' => $requestId], 500);
    }
};
