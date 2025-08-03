<?php
declare(strict_types=1);

namespace App\Entity;

final readonly class UserCity
{
    private string $city;
    private string $phoneNumber;

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }
}
