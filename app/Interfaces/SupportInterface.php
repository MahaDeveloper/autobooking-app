<?php

namespace App\Interfaces;

interface SupportInterface
{
    public function supportStore($request): string;

    public function supportReply($request): string;
}
