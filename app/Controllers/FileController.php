<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class FileController extends ResourceController
{
    public function serveFile($filePath)
    {
        // Base directory for uploads
        $basePath = WRITEPATH;

        // Full path to the file
        $fullPath = realpath($basePath . $filePath);

        // Validate the file exists and is within the allowed directory
        if (!$fullPath || !is_file($fullPath) || strpos($fullPath, realpath($basePath)) !== 0) {
            return $this->failNotFound('File not found.');
        }

        // Serve the file
        return $this->response
            ->setHeader('Content-Type', mime_content_type($fullPath))
            ->setHeader('Content-Disposition', 'attachment; filename="' . basename($fullPath) . '"')
            ->setBody(file_get_contents($fullPath));
    }
}
