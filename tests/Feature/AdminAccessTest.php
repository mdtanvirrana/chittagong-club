<?php

use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

test('admin can sign in with the Users_App md5 credential', function () {
    $guard = \Mockery::mock();
    $credentialLookup = \Mockery::mock();

    Auth::shouldReceive('guard')
        ->twice()
        ->with('admin')
        ->andReturn($guard);

    $guard->shouldReceive('check')
        ->once()
        ->andReturnFalse();

    $guard->shouldReceive('login')
        ->once()
        ->with(\Mockery::on(function (AdminUser $admin): bool {
            return $admin->userid === AdminUser::LOGIN_ID
                && $admin->PrvcusID === AdminUser::LOGIN_ID;
        }));

    DB::shouldReceive('table')
        ->once()
        ->with('Users_App')
        ->andReturn($credentialLookup);

    $credentialLookup->shouldReceive('where')
        ->once()
        ->with('PrvcusID', AdminUser::LOGIN_ID)
        ->andReturnSelf();
    $credentialLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'PrvcusID' => AdminUser::LOGIN_ID,
            'Password' => md5('123456'),
        ]);

    $response = $this->post(route('admin.login.store'), [
        'login' => 'admin',
        'password' => '123456',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
});

test('admin login fails when password does not match the md5 credential', function () {
    $guard = \Mockery::mock();
    $credentialLookup = \Mockery::mock();

    Auth::shouldReceive('guard')
        ->once()
        ->with('admin')
        ->andReturn($guard);

    $guard->shouldReceive('check')
        ->once()
        ->andReturnFalse();
    $guard->shouldNotReceive('login');

    DB::shouldReceive('table')
        ->once()
        ->with('Users_App')
        ->andReturn($credentialLookup);

    $credentialLookup->shouldReceive('where')
        ->once()
        ->with('PrvcusID', AdminUser::LOGIN_ID)
        ->andReturnSelf();
    $credentialLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'PrvcusID' => AdminUser::LOGIN_ID,
            'Password' => md5('123456'),
        ]);

    $response = $this
        ->from(route('admin.login'))
        ->post(route('admin.login.store'), [
            'login' => 'admin',
            'password' => 'wrong-password',
        ]);

    $response
        ->assertRedirect(route('admin.login'))
        ->assertSessionHasErrors([
            'login' => 'Invalid credentials',
        ]);
});

test('admin login rejects non-admin user ids', function () {
    $guard = \Mockery::mock();

    Auth::shouldReceive('guard')
        ->once()
        ->with('admin')
        ->andReturn($guard);

    $guard->shouldReceive('check')
        ->once()
        ->andReturnFalse();
    $guard->shouldNotReceive('login');

    DB::shouldReceive('table')->never();

    $response = $this
        ->from(route('admin.login'))
        ->post(route('admin.login.store'), [
            'login' => 'member-1001',
            'password' => '123456',
        ]);

    $response
        ->assertRedirect(route('admin.login'))
        ->assertSessionHasErrors([
            'login' => 'Invalid credentials',
        ]);
});
