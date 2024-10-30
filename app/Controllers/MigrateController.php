<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Migrations\MigrationRunner;
use Exception;

class MigrateController extends BaseController
{
    public function migrate()
    {
        // Load the migrations library
        $migrate = \Config\Services::migrations();
        try {
            // Run all available migrations
            $migrate->latest();
            // Alternatively, you can specify a specific version:
            // $migrate->version(3);
            echo 'Migrations were executed successfully.<br>';
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}
