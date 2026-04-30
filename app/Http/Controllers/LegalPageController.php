<?php

namespace App\Http\Controllers;

use App\Support\CompanyProfile;
use App\Support\ContactDirectory;

class LegalPageController extends Controller
{
    public function terms()
    {
        return $this->render('terms');
    }

    public function privacy()
    {
        return $this->render('privacy');
    }

    public function refund()
    {
        return $this->render('refund');
    }

    public function data()
    {
        return $this->render('data');
    }

    public function contact()
    {
        return $this->render('contact');
    }

    private function render(string $pageKey)
    {
        $profile = CompanyProfile::current();
        $contactGroups = [];
        $contactStats = null;

        if ($pageKey === 'contact') {
            $contactGroups = ContactDirectory::publicDirectory();
            $contactStats = [
                'departments' => count($contactGroups),
                'lines' => collect($contactGroups)->sum('total_entries'),
                'emails' => collect($contactGroups)->sum('email_count'),
            ];
        }

        return view('pages.legal', [
            'pageKey' => $pageKey,
            'contactGroups' => $contactGroups,
            'contactStats' => $contactStats,
            ...$profile,
            ...$this->pageData($pageKey, $profile, $contactStats),
        ]);
    }

    private function pageData(string $pageKey, array $profile, ?array $contactStats): array
    {
        return match ($pageKey) {
            'terms' => [
                'pageTitle' => 'Terms & Conditions',
                'pageDescription' => 'Member eligibility, ERP integration, and online bill payment terms for the club app.',
                'sections' => [
                    [
                        'title' => 'Member Eligibility',
                        'icon' => 'verified_user',
                        'body' => [
                            'Only ' . $profile['companyName'] . ' (' . $profile['shortName'] . ') active members are eligible to access this club app and pay their bills through the SSLCOMMERZ payment gateway.',
                        ],
                    ],
                    [
                        'title' => 'ERP Integration',
                        'icon' => 'hub',
                        'body' => [
                            'This app is fully integrated with Club ERP Software.',
                            'Registered ' . $profile['shortName'] . ' members\' club ID, mobile number, bills, and related information are shown from ERP software and maintained by Club ERP Software.',
                        ],
                    ],
                    [
                        'title' => 'Payment Confirmation',
                        'icon' => 'sms',
                        'body' => [
                            'After completing an online payment, a member will receive a SMS at the registered mobile number and an email at the registered email address.',
                        ],
                    ],
                ],
            ],
            'privacy' => [
                'pageTitle' => 'Privacy Policy',
                'pageDescription' => 'How member information is presented, used, and protected inside the club app.',
                'sections' => [
                    [
                        'title' => 'About the Club',
                        'icon' => 'apartment',
                        'body' => array_values(array_filter([
                            $profile['companyName'] . ' (' . $profile['shortName'] . ') is a non-profit organization which is run by, and for, its members.',
                            $profile['contactSummary'] !== '' ? $profile['contactSummary'] : null,
                            $profile['companyAddressText'] !== '' ? 'Address: ' . $profile['companyAddressText'] : null,
                        ])),
                    ],
                    [
                        'title' => 'Usage of Information',
                        'icon' => 'manage_accounts',
                        'body' => [
                            'Only registered ' . $profile['shortName'] . ' club members are eligible to access their information.',
                        ],
                    ],
                    [
                        'title' => 'Third-Party Policy',
                        'icon' => 'policy',
                        'body' => [
                            'Third parties are not allowed to use this app. Only registered ' . $profile['shortName'] . ' club members are eligible to access their information among themselves.',
                        ],
                    ],
                    [
                        'title' => 'Information Security',
                        'icon' => 'shield_lock',
                        'body' => [
                            'To protect your personal information, reasonable measures and industry-standard practices are used so it is not inappropriately misused, accessed, disclosed, altered, or destroyed.',
                            'If you provide credit card information, it is encrypted using secure socket layer technology (SSL) by SSLCOMMERZ.',
                        ],
                    ],
                ],
            ],
            'refund' => [
                'pageTitle' => 'Return and Refund Policy',
                'pageDescription' => 'Online payments completed through the club app are treated as final transactions.',
                'sections' => [
                    [
                        'title' => 'Refund Terms',
                        'icon' => 'payments',
                        'body' => [
                            'All types of payment through this app are non-refundable.',
                        ],
                    ],
                ],
            ],
            'data' => [
                'pageTitle' => 'Data Policy',
                'pageDescription' => 'How the club stores member data for operations, communication, and member services.',
                'sections' => [
                    [
                        'title' => 'Member Data',
                        'icon' => 'database',
                        'body' => [
                            $profile['companyName'] . ' (' . $profile['shortName'] . ') member data is collected and stored securely for the purpose of membership management, communication, event invitations, and other club facilities.',
                        ],
                    ],
                    [
                        'title' => 'Sharing Policy',
                        'icon' => 'lock_person',
                        'body' => [
                            'Personal information is not shared with third parties. Only registered ' . $profile['shortName'] . ' club members are eligible to access their information among themselves.',
                        ],
                    ],
                ],
            ],
            'contact' => [
                'pageTitle' => 'Contact Us',
                'pageDescription' => 'Reach the club office and browse the live contact directory published from the club system.',
                'sections' => [
                    [
                        'title' => 'Club Information',
                        'icon' => 'support_agent',
                        'body' => array_values(array_filter([
                            $profile['companyName'] . ' is a not-for-profit organization which is run by, and for, its members.',
                            $profile['contactSummary'] !== '' ? $profile['contactSummary'] : null,
                            $profile['companyAddressText'] !== '' ? 'Address: ' . $profile['companyAddressText'] : null,
                        ])),
                    ],
                ],
            ],
            default => abort(404),
        };
    }
}
