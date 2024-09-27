<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
    <style>
    .reset-button {
        text-decoration: none;
        background-color: #8c3b0b;
        /* Purple color */
        border: none;
        color: #fffff;
        /* White text */
        padding: 10px 20px;
        text-align: center;
        display: inline-block;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .reset-button:hover {
        background-color: #8c3b0b;
    }
    </style>
</head>

<body>
    <h1>Reset Your Password for eProcurement system</h1>
    <p>Hi <?=(!empty($user)) ? $user['first_name'] : ''?>,</p>
    <p>You recently requested to reset your password for your account on eProcurement.</p>
    <p>Click the link below to set a new password:</p>
    <p><a href="http://197.155.193.45/#/auth/reset-password/<?=($token) ? $token : ''?>" class="reset-button">Reset
            Password</a></p>
    <p>This link will expire in 1 hour.</p>
    <p>If you did not request a password reset, please ignore this email.</p>
    <p>Thanks,</p>
</body>

</html>