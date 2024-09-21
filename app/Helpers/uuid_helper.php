<?php

use Ramsey\Uuid\Uuid;

if (!function_exists('uuid_v4')) {
    function uuid_v4()
    {
        return Uuid::uuid4()->toString();
    }
}

function hello()
{
    return "Hello Function";
}
