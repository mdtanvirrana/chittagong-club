<?php

use App\Models\AdminUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

function fakeAdminUser(): AdminUser
{
    return new AdminUser([
        'PrvcusID' => 'admin',
        'userid' => 'admin',
        'username' => 'Admin Test',
        'Password' => 'secret',
    ]);
}

test('admin picture upload stores files in the selected folder', function () {
    $targetFile = public_path('images/member-directory/member-upload-test-1001.jpg');
    File::delete($targetFile);

    $response = $this
        ->actingAs(fakeAdminUser(), 'admin')
        ->post(route('admin.pictures.store'), [
            'image_type' => 'member_photo',
            'images' => [
                UploadedFile::fake()->image('member-upload-test-1001.jpg'),
            ],
        ]);

    $response
        ->assertRedirect(route('admin.pictures.index', ['folder' => 'member-directory']))
        ->assertSessionHas('status');

    expect(is_file($targetFile))->toBeTrue();

    File::delete($targetFile);
});

test('admin picture delete removes files from nested image folders', function () {
    $targetDirectory = public_path('images/ch3');
    $targetFile = $targetDirectory.'/member-child-photo-delete-test.jpg';

    File::ensureDirectoryExists($targetDirectory);
    File::put($targetFile, 'child-photo-delete-test');

    $response = $this
        ->actingAs(fakeAdminUser(), 'admin')
        ->delete(route('admin.pictures.destroy'), [
            'relative_path' => 'images/ch3/member-child-photo-delete-test.jpg',
        ]);

    $response->assertRedirect(route('admin.pictures.index', ['page' => 1]));

    expect(is_file($targetFile))->toBeFalse();
});

test('admin picture browser filters by folder, search, and paginates', function () {
    $targetDirectory = public_path('images/member-directory');
    File::ensureDirectoryExists($targetDirectory);

    $createdFiles = [];

    for ($i = 1; $i <= 49; $i++) {
        $filename = sprintf('member-browser-test-%02d.jpg', $i);
        $path = $targetDirectory.'/'.$filename;
        File::put($path, 'browser-test-'.$i);
        touch($path, time() + $i);
        $createdFiles[] = $path;
    }

    $response = $this
        ->actingAs(fakeAdminUser(), 'admin')
        ->get(route('admin.pictures.index', [
            'folder' => 'member-directory',
            'q' => 'member-browser-test',
            'page' => 2,
        ]));

    $response
        ->assertOk()
        ->assertSee('Member Photo')
        ->assertSee('member-browser-test-01.jpg')
        ->assertDontSee('member-browser-test-49.jpg');

    foreach ($createdFiles as $path) {
        File::delete($path);
    }
});
