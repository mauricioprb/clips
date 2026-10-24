<?php

declare(strict_types=1);

use Appwrite\Client;
use Appwrite\Enums\ExecutionMethod;
use Appwrite\ID;
use Appwrite\Query;
use Appwrite\Services\Functions;
use Appwrite\Services\TablesDB;
use Appwrite\Services\Teams;
use Appwrite\Services\Users;

function testEnv(string $name): string
{
    $value = getenv($name);

    if (!is_string($value) || $value === '') {
        throw new RuntimeException("Defina a variável de ambiente {$name} para rodar os testes de integração.");
    }

    return $value;
}

function projectClient(): Client
{
    return (new Client())
        ->setEndpoint(testEnv('APPWRITE_TEST_ENDPOINT'))
        ->setProject(testEnv('APPWRITE_TEST_PROJECT_ID'));
}

function adminClient(): Client
{
    return projectClient()->setKey(testEnv('APPWRITE_TEST_KEY'));
}

function adminUsers(): Users
{
    return new Users(adminClient());
}

function countRowsOf(string $tableId, string $userId): int
{
    return (new TablesDB(adminClient()))
        ->listRows('clips', $tableId, [Query::equal('userId', [$userId]), Query::limit(1)])
        ->total;
}

function waitUntil(callable $condition, int $timeoutSeconds = 30): bool
{
    $deadline = time() + $timeoutSeconds;

    while (time() < $deadline) {
        if ($condition()) {
            return true;
        }
        usleep(500_000);
    }

    return $condition();
}

/**
 * @param list<string> $roles
 * @return array{id: string, session: string}
 */
function createTestUser(bool $member = true, array $roles = []): array
{
    $userId = ID::unique();
    adminUsers()->create($userId, "teste-{$userId}@clips.example", null, 'senha-de-teste-123', "Bolsista {$userId}");

    if ($member) {
        (new Teams(adminClient()))->createMembership('clips', $roles, null, $userId);
    }

    return ['id' => $userId, 'session' => adminUsers()->createSession($userId)->secret];
}

/**
 * @param array{id: string, session: string} $user
 */
function deleteTestUser(array $user): void
{
    adminUsers()->delete($user['id']);
}

/**
 * @return array{status: int, data: mixed}
 */
function callTimesheet(?string $session, string $method, string $path, ?array $body = null): array
{
    return callFunction('timesheet', $session, $method, $path, $body === null ? null : json_encode($body));
}

/**
 * @param array<string, string> $headers
 * @return array{status: int, data: mixed}
 */
function callFunction(string $functionId, ?string $session, string $method, string $path = '/', ?string $body = null, array $headers = []): array
{
    $client = projectClient();

    if ($session !== null) {
        $client->setSession($session);
    }

    $execution = (new Functions($client))->createExecution(
        $functionId,
        $body,
        false,
        $path,
        ExecutionMethod::{$method}(),
        ['content-type' => 'application/json', ...$headers],
    );

    return [
        'status' => $execution->responseStatusCode,
        'data' => json_decode($execution->responseBody, true),
    ];
}
