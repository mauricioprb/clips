<?php

declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

use Appwrite\AppwriteException;
use Appwrite\Client;
use Appwrite\ID;
use Appwrite\Query;
use Appwrite\Services\TablesDB;

return function ($context) {
    $databaseId = 'exemplo';
    $tableId = 'notes';
    $titleMaxLength = 255;
    $notesLimit = 100;
    $notFoundStatus = 404;
    $noteIdPattern = '#^/([A-Za-z0-9][A-Za-z0-9._-]{0,35})$#';
    $requestId = $context->req->headers['x-appwrite-execution-id'] ?? '';

    $json = fn(array $body, int $statusCode = 200) => $context->res->text(
        json_encode($body, JSON_THROW_ON_ERROR),
        $statusCode,
        ['content-type' => 'application/json'],
    );

    $userId = $context->req->headers['x-appwrite-user-id'] ?? '';

    if ($userId === '') {
        return $json(['error' => 'Entre na sua conta para continuar.'], 401);
    }

    $tablesDB = new TablesDB(
        (new Client())
            ->setEndpoint(getenv('APPWRITE_FUNCTION_API_ENDPOINT'))
            ->setProject(getenv('APPWRITE_FUNCTION_PROJECT_ID'))
            ->setKey($context->req->headers['x-appwrite-key']),
    );

    $toNote = fn($row) => [
        'id' => $row->id,
        'title' => $row->data['title'],
        'createdAt' => $row->createdAt,
    ];

    $listNotes = fn() => $json([
        'notes' => array_map($toNote, $tablesDB->listRows($databaseId, $tableId, [
            Query::equal('userId', [$userId]),
            Query::orderDesc('$createdAt'),
            Query::limit($notesLimit),
        ])->rows),
    ]);

    $createNote = function () use ($context, $json, $tablesDB, $toNote, $databaseId, $tableId, $titleMaxLength, $notesLimit, $userId) {
        $body = json_decode($context->req->bodyText, true);

        if (!is_array($body)) {
            return $json(['error' => 'Envie os dados da nota em JSON.'], 400);
        }

        $title = is_string($body['title'] ?? null) ? trim($body['title']) : '';

        if ($title === '' || mb_strlen($title) > $titleMaxLength) {
            return $json(['error' => "Informe um título com até {$titleMaxLength} caracteres."], 422);
        }

        $existingNotes = $tablesDB->listRows($databaseId, $tableId, [
            Query::equal('userId', [$userId]),
            Query::limit(1),
        ])->total;

        if ($existingNotes >= $notesLimit) {
            return $json(['error' => "Você atingiu o limite de {$notesLimit} notas. Apague uma para criar outra."], 422);
        }

        $row = $tablesDB->createRow($databaseId, $tableId, ID::unique(), ['userId' => $userId, 'title' => $title]);

        return $json($toNote($row), 201);
    };

    $deleteNote = function (string $noteId) use ($context, $json, $tablesDB, $databaseId, $tableId, $notFoundStatus, $userId) {
        $row = $tablesDB->getRow($databaseId, $tableId, $noteId);

        if ($row->data['userId'] !== $userId) {
            return $json(['error' => 'Nota não encontrada.'], $notFoundStatus);
        }

        $tablesDB->deleteRow($databaseId, $tableId, $noteId);

        return $context->res->text('', 204);
    };

    $methodNotAllowed = fn() => $json(['error' => 'Método não permitido.'], 405);

    try {
        if ($context->req->path === '/') {
            return match ($context->req->method) {
                'GET' => $listNotes(),
                'POST' => $createNote(),
                default => $methodNotAllowed(),
            };
        }

        if (preg_match($noteIdPattern, $context->req->path, $matches) === 1) {
            return $context->req->method === 'DELETE' ? $deleteNote($matches[1]) : $methodNotAllowed();
        }

        return $json(['error' => 'Rota não encontrada.'], $notFoundStatus);
    } catch (Throwable $error) {
        if ($error instanceof AppwriteException && $error->getCode() === $notFoundStatus) {
            return $json(['error' => 'Nota não encontrada.'], $notFoundStatus);
        }

        $context->error("{$requestId} " . $error::class . ": {$error->getMessage()}");

        return $json(['error' => 'Não foi possível concluir a operação.', 'requestId' => $requestId], 500);
    }
};
