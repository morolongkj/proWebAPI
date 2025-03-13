<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper("auth");

        if (! auth("tokens")->loggedIn()) {
            return redirect()->to(base_url("api/invalid-access"));
        }

        $user = auth()->user();

        if ($user && $user->account_status === 'deactivated') {
            auth()->logout();

            return redirect()->to(base_url("api/invalid-access"));
        }

    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
