<?php

declare(strict_types=1);

use Appwrite\AppwriteException;

beforeEach(function () {
    $this->admin = createTestUser(roles: ['owner']);
    $this->scholar = createTestUser();
});

afterEach(function () {
    foreach ([$this->admin, $this->scholar] as $user) {
        try {
            deleteTestUser($user);
        } catch (AppwriteException) {
            continue;
        }
    }
});

/**
 * @return array<string, mixed>|null
 */
function memberIn(array $members, string $userId): ?array
{
    return array_find($members, fn(array $member) => $member['userId'] === $userId);
}

it('rejects callers without a session', function () {
    callMembers(null, 'GET');
})->throws(AppwriteException::class);

it('rejects users outside the team', function () {
    $outsider = createTestUser(member: false);

    try {
        callMembers($outsider['session'], 'GET');
    } finally {
        deleteTestUser($outsider);
    }
})->throws(AppwriteException::class);

it('lets only admins list the members', function () {
    $list = callMembers($this->admin['session'], 'GET');
    $scholar = memberIn($list['data']['members'], $this->scholar['id']);

    expect($list['status'])->toBe(200)
        ->and($scholar)->toMatchArray(['isAdmin' => false, 'isSelf' => false, 'status' => 'active'])
        ->and($scholar['email'])->toBe("teste-{$this->scholar['id']}@clips.example")
        ->and(memberIn($list['data']['members'], $this->admin['id']))->toMatchArray(['isAdmin' => true, 'isSelf' => true])
        ->and(callMembers($this->scholar['session'], 'GET')['status'])->toBe(403);
});

it('blocks and unblocks a member', function () {
    $blocked = callMembers($this->admin['session'], 'PATCH', "/{$this->scholar['id']}", ['active' => false]);

    expect($blocked['data'])->toBe(['userId' => $this->scholar['id'], 'active' => false])
        ->and(memberIn(callMembers($this->admin['session'], 'GET')['data']['members'], $this->scholar['id'])['status'])->toBe('blocked')
        ->and(callMembers($this->admin['session'], 'PATCH', "/{$this->scholar['id']}", ['active' => true])['data']['active'])->toBeTrue();
});

it('does not let scholars block anyone', function () {
    expect(callMembers($this->scholar['session'], 'PATCH', "/{$this->admin['id']}", ['active' => false])['status'])->toBe(403)
        ->and(memberIn(callMembers($this->admin['session'], 'GET')['data']['members'], $this->admin['id'])['status'])->toBe('active');
});

it('does not let admins block themselves', function () {
    expect(callMembers($this->admin['session'], 'PATCH', "/{$this->admin['id']}", ['active' => false])['status'])->toBe(403);
});

it('does not block people outside the team', function () {
    $outsider = createTestUser(member: false);

    try {
        expect(callMembers($this->admin['session'], 'PATCH', "/{$outsider['id']}", ['active' => false])['status'])->toBe(404);
    } finally {
        deleteTestUser($outsider);
    }
});

it('rejects invalid status changes', function (?string $body, int $status) {
    expect(callFunction('members', $this->admin['session'], 'PATCH', "/{$this->scholar['id']}", $body)['status'])->toBe($status);
})->with([
    'not json' => ['isso não é json', 400],
    'missing flag' => ['{}', 422],
    'flag as text' => ['{"active":"false"}', 422],
]);

it('answers 405 and 404 for unknown routes', function () {
    expect(callMembers($this->admin['session'], 'POST')['status'])->toBe(405)
        ->and(callMembers($this->admin['session'], 'GET', '/me')['status'])->toBe(405)
        ->and(callMembers($this->admin['session'], 'GET', '/a/b')['status'])->toBe(404);
});

it('deletes the own account only with the right email', function () {
    expect(callMembers($this->scholar['session'], 'DELETE', '/me', ['email' => 'outra@clips.example'])['status'])->toBe(422)
        ->and(callMembers($this->scholar['session'], 'DELETE', '/me', ['email' => strtoupper("teste-{$this->scholar['id']}@clips.example")])['status'])->toBe(204);

    adminUsers()->get($this->scholar['id']);
})->throws(AppwriteException::class);

it('keeps the last admin from deleting the own account', function () {
    $admins = array_filter(callMembers($this->admin['session'], 'GET')['data']['members'], fn(array $member) => $member['isAdmin']);

    if (count($admins) > 1) {
        $this->markTestSkipped('O projeto de teste já tem outro administrador.');
    }

    $email = "teste-{$this->admin['id']}@clips.example";

    expect(callMembers($this->admin['session'], 'DELETE', '/me', ['email' => $email])['status'])->toBe(422);
});
