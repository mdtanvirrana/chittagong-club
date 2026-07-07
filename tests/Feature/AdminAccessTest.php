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

test('admin session lasts seven days while member inactivity timeout stays short', function () {
    expect(config('session.lifetime'))->toBe(10080)
        ->and(config('auth.member_session_lifetime'))->toBe(5);
});

test('admin can sign in with the Users_App md5 credential', function () {
    $adminId = 'admin';
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
        ->with(\Mockery::on(fn (AdminUser $admin): bool => $admin->userid === $adminId
            && $admin->PrvcusID === $adminId
            && $admin->is_admin === true));

    DB::shouldReceive('table')
        ->once()
        ->with('Users_App')
        ->andReturn($credentialLookup);

    $credentialLookup->shouldReceive('where')
        ->once()
        ->with('PrvcusID', $adminId)
        ->andReturnSelf();
    $credentialLookup->shouldReceive('where')
        ->once()
        ->with('is_admin', 1)
        ->andReturnSelf();
    $credentialLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'PrvcusID' => $adminId,
            'Password' => md5('123456'),
            'is_admin' => 1,
        ]);

    $response = $this->post(route('admin.login.store'), [
        'login' => 'admin',
        'password' => '123456',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
});

test('admin login fails when password does not match the md5 credential', function () {
    $adminId = 'admin';
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
        ->with('PrvcusID', $adminId)
        ->andReturnSelf();
    $credentialLookup->shouldReceive('where')
        ->once()
        ->with('is_admin', 1)
        ->andReturnSelf();
    $credentialLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'PrvcusID' => $adminId,
            'Password' => md5('123456'),
            'is_admin' => 1,
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

test('admin login rejects user ids without admin access', function () {
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
        ->with('PrvcusID', 'member-1001')
        ->andReturnSelf();
    $credentialLookup->shouldReceive('where')
        ->once()
        ->with('is_admin', 1)
        ->andReturnSelf();
    $credentialLookup->shouldReceive('first')
        ->once()
        ->andReturnNull();

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
