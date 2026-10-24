<?php

declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

use Appwrite\Client;
use Appwrite\Query;
use Appwrite\Services\TablesDB;

return function ($context) {
    $databaseId = 'exemplo';
    $tablesWithUserData = ['notes'];
    $userDeletedEvent = '#^users\.[^.]+\.delete$#';

    $isUserDeletedEvent = ($context->req->headers['x-appwrite-trigger'] ?? '') === 'event'
        && preg_match($userDeletedEvent, $context->req->headers['x-appwrite-event'] ?? '') === 1;

    if (!$isUserDeletedEvent) {
        return $context->res->text('', 400);
    }

    $user = json_decode($context->req->bodyText, true);
    $userId = is_array($user) && is_string($user['$id'] ?? null) ? $user['$id'] : '';

    if ($userId === '') {
        $context->error('Evento de exclusão sem o ID do usuário.');

        return $context->res->text('', 400);
    }

    $tablesDB = new TablesDB(
        (new Client())
            ->setEndpoint(getenv('APPWRITE_FUNCTION_API_ENDPOINT'))
            ->setProject(getenv('APPWRITE_FUNCTION_PROJECT_ID'))
            ->setKey($context->req->headers['x-appwrite-key']),
    );

    foreach ($tablesWithUserData as $tableId) {
        do {
            $deleted = $tablesDB->deleteRows($databaseId, $tableId, [Query::equal('userId', [$userId])])->total;
            $context->log("{$tableId}: {$deleted} linhas apagadas do usuário {$userId}.");
        } while ($deleted > 0);
    }

    return $context->res->text('', 204);
};
