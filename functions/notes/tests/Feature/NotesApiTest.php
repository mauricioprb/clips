<?php

declare(strict_types=1);

use Appwrite\AppwriteException;

beforeEach(function () {
    $this->owner = createTestUser();
    $this->other = createTestUser();
});

afterEach(function () {
    deleteTestUser($this->owner);
    deleteTestUser($this->other);
});

it('rejects callers without a session', function () {
    callNotes(null, 'GET');
})->throws(AppwriteException::class);

it('creates and lists the owner notes', function () {
    $created = callNotes($this->owner['session'], 'POST', '/', json_encode(['title' => 'Primeira nota']));

    expect($created['status'])->toBe(201)
        ->and($created['data']['title'])->toBe('Primeira nota')
        ->and(callNotes($this->owner['session'], 'GET')['data']['notes'])->toHaveCount(1);
});

it('hides notes from other users', function () {
    callNotes($this->owner['session'], 'POST', '/', json_encode(['title' => 'Nota privada']));

    expect(callNotes($this->other['session'], 'GET')['data']['notes'])->toBe([]);
});

it('does not let other users delete a note', function () {
    $noteId = callNotes($this->owner['session'], 'POST', '/', json_encode(['title' => 'Nota privada']))['data']['id'];

    expect(callNotes($this->other['session'], 'DELETE', "/{$noteId}")['status'])->toBe(404)
        ->and(callNotes($this->owner['session'], 'GET')['data']['notes'])->toHaveCount(1);
});

it('ignores the userId sent by the client', function () {
    callNotes($this->owner['session'], 'POST', '/', json_encode([
        'title' => 'Tentativa de gravar em nome de outro',
        'userId' => $this->other['id'],
    ]));

    expect(callNotes($this->other['session'], 'GET')['data']['notes'])->toBe([])
        ->and(callNotes($this->owner['session'], 'GET')['data']['notes'])->toHaveCount(1);
});

it('rejects a body that is not JSON', function () {
    expect(callNotes($this->owner['session'], 'POST', '/', 'isso não é json')['status'])->toBe(400);
});

it('rejects invalid titles', function (mixed $title) {
    expect(callNotes($this->owner['session'], 'POST', '/', json_encode(['title' => $title]))['status'])->toBe(422);
})->with([
    'empty' => '',
    'blank' => '   ',
    'list' => [['a']],
    'number' => 42,
    'too long' => str_repeat('a', 256),
]);

it('answers 405 and 404 for unknown routes', function () {
    expect(callNotes($this->owner['session'], 'PUT')['status'])->toBe(405)
        ->and(callNotes($this->owner['session'], 'GET', '/a/b')['status'])->toBe(404);
});

it('deletes the notes of a deleted user', function () {
    $user = createTestUser();
    callNotes($user['session'], 'POST', '/', json_encode(['title' => 'Primeira']));
    callNotes($user['session'], 'POST', '/', json_encode(['title' => 'Segunda']));

    expect(countNotesOf($user['id']))->toBe(2);

    adminUsers()->delete($user['id']);

    expect(waitUntil(fn() => countNotesOf($user['id']) === 0))->toBeTrue();
});

it('does not let users call the cleanup function', function () {
    callFunction('user-cleanup', $this->owner['session'], 'POST');
})->throws(AppwriteException::class);
