<?php
namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Authentication\JWTManager;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Validation\ValidationRules;

class AuthController extends ResourceController
{

    public function register()
    {
        $rules = [
            "username"   => "required|is_unique[users.username]",
            "email"      => "required|valid_email|is_unique[auth_identities.secret]",
            "password"   => "required",
            "first_name" => "required",
            "last_name"  => "required",
        ];

        if (! $this->validate($rules)) {

            $response = [
                "status"  => false,
                "message" => $this->validator->getErrors(),
                "data"    => [],
            ];
            return $this->respond($response, 400);
        } else {

            // User Model
            $userObject = new UserModel();
            // User Entity
            $userEntityObject = new User([
                "first_name"    => $this->request->getVar("first_name"),
                "last_name"     => $this->request->getVar("last_name"),
                "position"      => $this->request->getVar("position") ?? null,
                "date_of_birth" => $this->request->getVar("date_of_birth") ?? null,
                "phone_number"  => $this->request->getVar("phone_number") ?? null,
                "gender"        => $this->request->getVar("gender") ?? null,
                "username"      => $this->request->getVar("username") ?? $this->request->getVar("email"),
                "email"         => $this->request->getVar("email"),
                "password"      => $this->request->getVar("password"),
            ]);

            $roles = $this->request->getVar("roles") ?? [];

            $userObject->save($userEntityObject);
            $newUser = $userObject->findById($userObject->getInsertID());
            if (empty($roles)) {
                $userObject->addToDefaultGroup($newUser);
            } else {
                foreach ($roles as $role) {
                    $newUser->addGroup($role);
                }
            }
            // $userObject->addToDefaultGroup($newUser);

            // $data['user'] = $newUser;
            // $message = view('emails/registration_email', $data);
// send mail to user
            // send_mail($newUser->username, "Inteville Space Registration", $message);

            $response = [
                "status"  => true,
                "message" => "User saved successfully",
                "data"    => [
                    "user" => $newUser,
                ],
            ];
            return $this->respondCreated($response);
        }

        // return $this->respondCreated($response);
    }

    // Post
    public function login()
    {

        if (auth()->loggedIn()) {
            auth()->logout();
        }

        $rules = [
            "email"    => "required|valid_email",
            "password" => "required",
        ];

        if (! $this->validate($rules)) {

            $response = [
                "status"  => false,
                "message" => $this->validator->getErrors(),
                "data"    => [],
            ];
        } else {

            // success
            $credentials = [
                "email"    => $this->request->getVar("email"),
                "password" => $this->request->getVar("password"),
            ];

            $loginAttempt = auth()->attempt($credentials);

            if (! $loginAttempt->isOK()) {

                $response = [
                    "status"  => false,
                    "message" => "Invalid login details",
                    "data"    => [],
                ];
            } else {

                // We have a valid data set
                $userObject = new UserModel();
                $userData   = $userObject->findById(auth()->id());
                $authGroups = auth()->user()->getGroups();
                $token      = $userData->generateAccessToken("thisismysecretkey");

                $auth_token = $token->raw_token;

                $response = [
                    "status"  => true,
                    "message" => "User logged in successfully",
                    "data"    => [
                        "token"      => $auth_token,
                        "user"       => $userData,
                        "authGroups" => $authGroups,
                    ],
                ];
            }
        }

        return $this->respondCreated($response);
    }

    // Get
    public function profile()
    {
        $userId = auth()->id();

        $userObject = new UserModel();

        $userData = $userObject->findById($userId);

        return $this->respond([
            "status"  => true,
            "message" => "Profile information of logged in user",
            "data"    => [
                "user" => $userData,
                // 'you' => auth()->user(),
            ],
        ]);
    }

    // Get
    public function logout()
    {
        auth()->logout();

        auth()->user()->revokeAllAccessTokens();

        session()->destroy();

        return $this->respondCreated([
            "status"  => true,
            "message" => "User logged out successfully",
            "data"    => [],
        ]);
    }

    public function accessDenied()
    {
        return $this->respondCreated([
            "status"  => false,
            "message" => "Invalid access",
            "data"    => [],
        ]);
    }

    /**
     * Authenticate Existing User and Issue JWT.
     */
    public function jwtLogin(): ResponseInterface
    {
        // if (auth()->loggedIn()) {
        //     auth()->blacklistToken();
        // }
        // Get the validation rules
        $rules = $this->getValidationRules();

        // Validate credentials
        if (! $this->validateData($this->request->getJSON(true), $rules, [], config('Auth')->DBGroup)) {
            return $this->fail(
                ['errors' => $this->validator->getErrors()],
                $this->codes['unauthorized']
            );
        }

        // Get the credentials for login
        $credentials             = $this->request->getJsonVar(setting('Auth.validFields'));
        $credentials             = array_filter($credentials);
        $credentials['password'] = $this->request->getJsonVar('password');

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        // Check the credentials
        $result = $authenticator->attempt($credentials);

        // Credentials mismatch.
        if (! $result->isOK()) {
            // @TODO Record a failed login attempt

            return $this->failUnauthorized($result->reason());
        }

        // Credentials match.
        // @TODO Record a successful login attempt

        $user = $result->extraInfo();
        // $authGroups = auth()->user()->getGroups();
        /** @var JWTManager $manager */
        $manager = service('jwtmanager');

        // $user = auth()->user();
        $claims = [
            'email' => $user->email,
            // 'groups' => $user->getGroups(),
        ];

        // Generate JWT and return to client
        $jwt = $manager->generateToken($user, $claims);

        $response = [
            "status"  => true,
            "message" => "User logged in successfully",
            "data"    => [
                "token" => $jwt,
                "user"  => $user,
                "roles" => $user->getGroups(),
            ],
        ];

        return $this->respond($response);
    }

    /**
     * Returns the rules that should be used for validation.
     *
     * @return array<string, array<string, array<string>|string>>
     * @phpstan-return array<string, array<string, string|list<string>>>
     */
    protected function getValidationRules(): array
    {
        $rules = new ValidationRules();

        return $rules->getLoginRules();
    }

    public function requestPasswordReset()
    {
        $rules = [
            "email" => "required",
        ];

        if (! $this->validate($rules)) {

            $response = [
                "status"  => false,
                "message" => $this->validator->getErrors(),
            ];
            return $this->respond($response);

        } else {

            $email = $this->request->getVar('email');
            // Generate a token
            $token = bin2hex(random_bytes(32));

            $userModel = new UserModel();

            // Store the token in the database
            $user = $userModel->findByUsername($email);
            if (! $user) {
                return $this->failNotFound('User not found');
            }

            $userModel->setResetToken($user['id'], $token);

            $data['user']  = $user;
            $data['token'] = $token;

            $message = view('emails/reset_password_email', $data);

            send_mail($email, "Reset Password", $message);

            $response = [
                "message" => "Email with reset token is sent successfully.",
            ];

            return $this->respond($response, ResponseInterface::HTTP_OK);
        }
    }

    public function resetPassword()
    {
        $rules = [
            "token"    => "required",
            "password" => "required",
        ];

        if (! $this->validate($rules)) {
            $response = [
                "status"  => false,
                "message" => $this->validator->getErrors(),
            ];
            return $this->respond($response);
        } else {
            $token    = $this->request->getVar('token');
            $password = $this->request->getVar('password');

            $userModel = new UserModel();
            $auser     = $userModel->findByToken($token);

            if (empty($auser)) {
                return $this->failNotFound('User not found or the link is expired.');
            }

            $userProvider = auth()->getProvider();
            $user         = $userProvider->findById($auser['id']);

            if ($user) {
                $user->setPassword($password);
                $userProvider->save($user);
                // Handle success message
                $data = [
                    'user' => $user,
                ];
                return $this->respond($data);

            } else {
                // Handle user not found error
                return $this->failNotFound('User not found');
            }
            return $this->failNotFound('Some error occured.');
        }
    }

}
