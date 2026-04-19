<?php

test('admin login page loads successfully', function () {
    $response = $this->get(route('admin.login'));

    $response
        ->assertOk()
        ->assertSee('Admin Access');
});

test('guest is redirected to admin login for admin dashboard', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('admin.login'));
});
