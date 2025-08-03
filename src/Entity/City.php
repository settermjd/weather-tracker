<?php
declare(strict_types=1);

namespace App\Entity;

class City
{
    private string $city;

    /**
     * @return mixed
     */
    public function getCity(): string
    {
        return $this->city;
    }

}
