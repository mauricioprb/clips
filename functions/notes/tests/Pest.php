<?php

declare(strict_types=1);

use Appwrite\Client;
use Appwrite\Enums\ExecutionMethod;
use Appwrite\ID;
use Appwrite\Query;
use Appwrite\Services\Functions;
use Appwrite\Services\TablesDB;
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

function adminUsers(): Users
{
    return new Users(projectClient()->setKey(testEnv('APPWRITE_TEST_KEY')));
}

function countNotesOf(string $userId): int
{
    return (new TablesDB(projectClient()->setKey(testEnv('APPWRITE_TEST_KEY'))))
        ->listRows('exemplo', 'notes', [Query::equal('userId', [$userId]), Query::limit(1)])
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
 * @return array{id: string, session: string}
 */
function createTestUser(): array
{
    $userId = ID::unique();
    adminUsers()->create($userId, "teste-{$userId}@exemplo.com", null, 'senha-de-teste-123', 'Teste');

    return ['id' => $userId, 'session' => adminUsers()->createSession($userId)->secret];
}

/**
 * @param array{id: string, session: string} $user
 */
function deleteTestUser(array $user): void
{
    foreach (callNotes($user['session'], 'GET')['data']['notes'] as $note) {
        callNotes($user['session'], 'DELETE', "/{$note['id']}");
    }

    adminUsers()->delete($user['id']);
}

/**
 * @return array{status: int, data: mixed}
 */
function callNotes(?string $session, string $method, string $path = '/', ?string $body = null): array
{
    return callFunction('notes', $session, $method, $path, $body);
}

/**
 * @return array{status: int, data: mixed}
 */
function callFunction(string $functionId, ?string $session, string $method, string $path = '/', ?string $body = null): array
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
        ['content-type' => 'application/json'],
    );

    return [
        'status' => $execution->responseStatusCode,
        'data' => json_decode($execution->responseBody, true),
    ];
}
