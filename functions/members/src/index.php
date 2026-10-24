<?php

declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

use Appwrite\AppwriteException;
use Appwrite\Client;
use Appwrite\Query;
use Appwrite\Services\Teams;
use Appwrite\Services\Users;

return function ($context) {
    $teamId = 'clips';
    $adminRole = 'owner';
    $membersLimit = 100;
    $notFoundStatus = 404;
    $userIdPattern = '#^/([A-Za-z0-9][A-Za-z0-9._-]{0,35})$#';
    $requestId = $context->req->headers['x-appwrite-execution-id'] ?? '';

    $json = fn(array $body, int $statusCode = 200) => $context->res->text(
        json_encode($body, JSON_THROW_ON_ERROR),
        $statusCode,
        ['content-type' => 'application/json'],
    );
    $notFound = fn() => $json(['error' => 'Pessoa não encontrada.'], $notFoundStatus);

    $userId = $context->req->headers['x-appwrite-user-id'] ?? '';

    if ($userId === '') {
        return $json(['error' => 'Entre na sua conta para continuar.'], 401);
    }

    $client = (new Client())
        ->setEndpoint(getenv('APPWRITE_FUNCTION_API_ENDPOINT'))
        ->setProject(getenv('APPWRITE_FUNCTION_PROJECT_ID'))
        ->setKey($context->req->headers['x-appwrite-key']);
    $teams = new Teams($client);
    $users = new Users($client);

    $membershipOf = fn(string $memberId) => $teams->listMemberships($teamId, [
        Query::equal('userId', [$memberId]),
        Query::limit(1),
    ])->memberships[0] ?? null;

    $isAdmin = fn($membership) => $membership !== null && $membership->confirm && in_array($adminRole, $membership->roles, true);

    $listMembers = function () use ($json, $teams, $users, $teamId, $membersLimit, $userId, $isAdmin) {
        $memberships = $teams->listMemberships($teamId, [Query::limit($membersLimit)])->memberships;
        $memberIds = array_map(fn($membership) => $membership->userId, $memberships);
        $blocked = [];

        if ($memberIds !== []) {
            foreach ($users->list([Query::equal('$id', $memberIds), Query::limit($membersLimit)])->users as $user) {
                $blocked[$user->id] = !$user->status;
            }
        }

        $members = array_map(fn($membership) => [
            'userId' => $membership->userId,
            'membershipId' => $membership->id,
            'name' => $membership->userName,
            'email' => $membership->userEmail,
            'isAdmin' => $isAdmin($membership),
            'isSelf' => $membership->userId === $userId,
            'status' => match (true) {
                $blocked[$membership->userId] ?? false => 'blocked',
                !$membership->confirm => 'invitation_pending',
                default => 'active',
            },
            'invitedAt' => $membership->invited,
        ], $memberships);
        usort($members, fn(array $a, array $b) => strcasecmp($a['name'] ?: $a['email'], $b['name'] ?: $b['email']));

        return $json(['members' => $members]);
    };

    $updateStatus = function (string $memberId) use ($context, $json, $notFound, $users, $userId, $membershipOf) {
        if ($memberId === $userId) {
            return $json(['error' => 'Você não pode bloquear nem desbloquear a própria conta.'], 403);
        }

        $body = json_decode($context->req->bodyText, true);

        if (!is_array($body)) {
            return $json(['error' => 'Envie os dados em JSON.'], 400);
        }

        if (!is_bool($body['active'] ?? null)) {
            return $json(['error' => 'Informe se o acesso fica ativo ou bloqueado.'], 422);
        }

        if ($membershipOf($memberId) === null) {
            return $notFound();
        }

        $user = $users->updateStatus($memberId, $body['active']);

        return $json(['userId' => $user->id, 'active' => $user->status]);
    };

    $deleteOwnAccount = function () use ($context, $json, $teams, $users, $teamId, $membersLimit, $userId, $isAdmin, $membershipOf) {
        $body = json_decode($context->req->bodyText, true);

        if (!is_array($body)) {
            return $json(['error' => 'Envie os dados em JSON.'], 400);
        }

        $user = $users->get($userId);

        if (!is_string($body['email'] ?? null) || mb_strtolower(trim($body['email'])) !== mb_strtolower($user->email)) {
            return $json(['error' => 'Digite o e-mail da sua conta para confirmar a exclusão.'], 422);
        }

        if ($isAdmin($membershipOf($userId))) {
            $admins = array_filter($teams->listMemberships($teamId, [Query::limit($membersLimit)])->memberships, $isAdmin);

            if (count($admins) === 1) {
                return $json(['error' => 'Torne outra pessoa administradora antes de excluir a sua conta.'], 422);
            }
        }

        $users->delete($userId);

        return $context->res->text('', 204);
    };

    try {
        if ($context->req->path === '/me') {
            return $context->req->method === 'DELETE'
                ? $deleteOwnAccount()
                : $json(['error' => 'Método não permitido.'], 405);
        }

        $isMemberPath = preg_match($userIdPattern, $context->req->path, $matches) === 1;

        if ($context->req->path !== '/' && !$isMemberPath) {
            return $json(['error' => 'Rota não encontrada.'], $notFoundStatus);
        }

        if (!$isAdmin($membershipOf($userId))) {
            return $json(['error' => 'Só administradores podem gerenciar usuários.'], 403);
        }

        return match (true) {
            !$isMemberPath && $context->req->method === 'GET' => $listMembers(),
            $isMemberPath && $context->req->method === 'PATCH' => $updateStatus($matches[1]),
            default => $json(['error' => 'Método não permitido.'], 405),
        };
    } catch (Throwable $error) {
        if ($error instanceof AppwriteException && $error->getCode() === $notFoundStatus) {
            return $notFound();
        }

        $context->error("{$requestId} " . $error::class . ": {$error->getMessage()}");

        return $json(['error' => 'Não foi possível concluir a operação.', 'requestId' => $requestId], 500);
    }
};
