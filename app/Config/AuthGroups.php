<?php

declare (strict_types = 1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * Default Group
     */
    public string $defaultGroup = 'vendor';

    /**
     * Groups
     */
    public array $groups = [
        'superadmin'                  => [
            'title'       => 'Super Admin',
            'description' => 'Complete control of the system.',
        ],
        'admin'                       => [
            'title'       => 'Admin',
            'description' => 'Manages users and system settings.',
        ],
        'procurement_manager'         => [
            'title'       => 'Procurement Manager',
            'description' => 'Manages procurement orders and bids.',
        ],
        'procurement_assistant'       => [
            'title'       => 'Procurement Assistant',
            'description' => 'Assists the procurement manager with tasks.',
        ],
        'vendor'                      => [
            'title'       => 'Vendor',
            'description' => 'Can submit and manage bids.',
        ],
        'buyer'                       => [
            'title'       => 'Buyer',
            'description' => 'Creates and manages purchase orders.',
        ],
        'auditor'                     => [
            'title'       => 'Auditor',
            'description' => 'Can review procurement activities.',
        ],
        'quality_assurance_manager'   => [
            'title'       => 'Quality Assurance Manager',
            'description' => 'Oversees quality assurance processes.',
        ],
        'quality_assurance_assistant' => [
            'title'       => 'Quality Assurance Assistant',
            'description' => 'Assists the QA manager with inspections.',
        ],
        'tender_board_member'         => [
            'title'       => 'Tender Board Member',
            'description' => 'Has the authority to approve tenders.',
        ],
    ];

    /**
     * Permissions
     */
    public array $permissions = [
        'admin.access'             => 'Can access the admin panel',
        'system.settings'          => 'Can modify system settings',
        'users.manage'             => 'Can manage users and roles',
        'vendors.manage'           => 'Can manage vendors',
        'bids.create'              => 'Can submit bids',
        'bids.review'              => 'Can review submitted bids',
        'orders.create'            => 'Can create procurement orders',
        'orders.approve'           => 'Can approve procurement orders',
        'audit.access'             => 'Can access audit logs',
        'quality_assurance.manage' => 'Can manage quality assurance processes',
        'quality_assurance.review' => 'Can review quality assurance reports',
        'tenders.approve'          => 'Can approve tenders',
        'tenders.review'           => 'Can review tenders',
    ];

    /**
     * Permissions Matrix
     */
    public array $matrix = [
        'superadmin'                  => [
            'admin.*',
            'system.settings',
            'users.*',
            'vendors.*',
            'bids.*',
            'orders.*',
            'audit.access',
            'quality_assurance.*',
            'tenders.*',
        ],
        'admin'                       => [
            'admin.access',
            'system.settings',
            'users.manage',
            'vendors.manage',
        ],
        'procurement_manager'         => [
            'bids.review',
            'orders.create',
            'orders.approve',
            'vendors.manage',
        ],
        'procurement_assistant'       => [
            'bids.review',
            'orders.create',
        ],
        'vendor'                      => [
            'bids.create',
        ],
        'buyer'                       => [
            'orders.create',
            'bids.review',
        ],
        'auditor'                     => [
            'audit.access',
        ],
        'quality_assurance_manager'   => [
            'quality_assurance.manage',
            'quality_assurance.review',
        ],
        'quality_assurance_assistant' => [
            'quality_assurance.review',
        ],
        'tender_board_member'         => [
            'tenders.approve',
            'tenders.review',
        ],
    ];
}
