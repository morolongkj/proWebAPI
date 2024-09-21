<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ImageController extends Controller
{
    public function serveImage($filename)
    {
        $filePath = WRITEPATH . 'uploads/' . $filename;

        if (!file_exists($filePath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException($filename);
        }

        return $this->response->setHeader('Content-Type', mime_content_type($filePath))
                             ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                             ->setBody(file_get_contents($filePath));
    }
}
