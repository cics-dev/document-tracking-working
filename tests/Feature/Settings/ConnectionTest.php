<?php

use App\Http\Controllers\ConnectionTestController;
use App\Models\User;

test('connection samples require authentication', function () {
    $this->get(route('settings.connection'))->assertRedirect(route('login'));
    $this->getJson(route('settings.connection.sample', ['sample' => true]))->assertUnauthorized();
    $this->postJson(route('settings.connection.sample'))->assertUnauthorized();
});

test('a signed-in user can check latency and transfer fixed-size samples', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('settings.connection'))->assertOk()->assertSee('Run connection test');
    $this->get(route('settings.connection.sample'))->assertNoContent();

    $download = $this->get(route('settings.connection.sample', ['sample' => true]))
        ->assertOk()->assertHeader('Content-Type', 'application/octet-stream');

    expect(strlen($download->getContent()))->toBe(ConnectionTestController::DOWNLOAD_BYTES);

    $endpoint = route('settings.connection.sample');
    $headers = ['CONTENT_TYPE' => 'application/octet-stream'];
    $this->call('POST', $endpoint, [], [], [], $headers, str_repeat('x', ConnectionTestController::UPLOAD_BYTES))
        ->assertNoContent();
    $this->call('POST', $endpoint, [], [], [], $headers, str_repeat('x', ConnectionTestController::UPLOAD_BYTES + 1))
        ->assertBadRequest();
});
