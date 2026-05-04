<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class PortalImageDirectory
{
    public const BASE_DIRECTORY = 'images';
    public const MEMBER_DIRECTORY = 'member-directory';
    public const EMPLOYEE_DIRECTORY = 'employee-directory';
    public const FACILITIES_DIRECTORY = 'facilities';
    public const GALLERY_DIRECTORY = 'gallery';
    public const DRESS_CODE_DIRECTORY = 'dress-code';
    public const GENERAL_RULES_DIRECTORY = 'rules';

    private const UPLOAD_TARGETS = [
        'member_photo' => [
            'label' => 'Member Photo',
            'folder' => self::MEMBER_DIRECTORY,
        ],
        'spouse1_photo' => [
            'label' => 'Spouse 1 Photo',
            'folder' => 'spouse1',
        ],
        'spouse2_photo' => [
            'label' => 'Spouse 2 Photo',
            'folder' => 'spouse2',
        ],
        'employee_photo' => [
            'label' => 'Employee Photo',
            'folder' => self::EMPLOYEE_DIRECTORY,
        ],
        'facilities_photo' => [
            'label' => 'Facilities',
            'folder' => self::FACILITIES_DIRECTORY,
        ],
        'gallery_photo' => [
            'label' => 'Gallery',
            'folder' => self::GALLERY_DIRECTORY,
        ],
        'dress_code_photo' => [
            'label' => 'Dress Code',
            'folder' => self::DRESS_CODE_DIRECTORY,
        ],
        'general_rules_photo' => [
            'label' => 'General Rules',
            'folder' => self::GENERAL_RULES_DIRECTORY,
        ],
        'children1_photo' => [
            'label' => 'Children 1 Photo',
            'folder' => 'ch1',
        ],
        'children2_photo' => [
            'label' => 'Children 2 Photo',
            'folder' => 'ch2',
        ],
        'children3_photo' => [
            'label' => 'Children 3 Photo',
            'folder' => 'ch3',
        ],
        'children4_photo' => [
            'label' => 'Children 4 Photo',
            'folder' => 'ch4',
        ],
        'children5_photo' => [
            'label' => 'Children 5 Photo',
            'folder' => 'ch5',
        ],
        'children6_photo' => [
            'label' => 'Children 6 Photo',
            'folder' => 'ch6',
        ],
        'children7_photo' => [
            'label' => 'Children 7 Photo',
            'folder' => 'ch7',
        ],
        'children8_photo' => [
            'label' => 'Children 8 Photo',
            'folder' => 'ch8',
        ],
        'children9_photo' => [
            'label' => 'Children 9 Photo',
            'folder' => 'ch9',
        ],
        'children10_photo' => [
            'label' => 'Children 10 Photo',
            'folder' => 'ch10',
        ],
    ];

    public static function uploadTargets(): array
    {
        return self::UPLOAD_TARGETS;
    }

    public static function uploadTargetKeys(): array
    {
        return array_keys(self::UPLOAD_TARGETS);
    }

    public static function folderForTarget(string $target): ?string
    {
        return self::UPLOAD_TARGETS[$target]['folder'] ?? null;
    }

    public static function labelForTarget(string $target): ?string
    {
        return self::UPLOAD_TARGETS[$target]['label'] ?? null;
    }

    public static function labelForFolder(string $folder): string
    {
        foreach (self::UPLOAD_TARGETS as $target) {
            if ($target['folder'] === $folder) {
                return $target['label'];
            }
        }

        return $folder;
    }

    public static function photoFolders(): array
    {
        return array_values(array_unique(array_column(self::UPLOAD_TARGETS, 'folder')));
    }

    public static function ensurePhotoDirectories(): void
    {
        foreach (self::photoFolders() as $folder) {
            File::ensureDirectoryExists(static::absoluteDirectory($folder));
        }
    }

    public static function relativeDirectory(string $folder): string
    {
        return self::BASE_DIRECTORY.'/'.$folder;
    }

    public static function absoluteDirectory(string $folder): string
    {
        return public_path(static::relativeDirectory($folder));
    }

    public static function relativePath(string $folder, string $filename): string
    {
        return static::relativeDirectory($folder).'/'.$filename;
    }

    public static function isManagedRelativePath(string $relativePath): bool
    {
        $segments = array_values(array_filter(explode('/', str_replace('\\', '/', trim($relativePath, '/')))));

        return count($segments) === 3
            && $segments[0] === self::BASE_DIRECTORY
            && in_array($segments[1], static::photoFolders(), true)
            && basename($segments[2]) === $segments[2];
    }
}
