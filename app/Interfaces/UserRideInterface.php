<?php

namespace App\Interfaces;

interface UserRideInterface
{
    // public function checkArea($request): array;
    public function rideFare($request,$ride): array;
    public function rideReview($request,$ride_id): array;
}
