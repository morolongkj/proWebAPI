<?php
if (!function_exists('send_mail')) {
    function send_mail($receipient, $subject, $message)
    {
        $email = \Config\Services::email();
        $email->setTo($receipient);
        $email->setFrom('info@ndsoeprocurement.online', 'eProcurement System');

        $email->setSubject($subject);
        $email->setMessage($message);
        $email->send();
        // if ($email->send()) {
        //     // echo 'Email successfully sent';
        // } else {
        //     $data = $email->printDebugger(['headers']);
        //     // print_r($data);
        // }
    }
}