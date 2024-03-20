<?php

namespace App\Interfaces;

interface AuthenticateInterface
{
    public function otpRequest($request): array;
    public function checkOtp($request): array;
}
