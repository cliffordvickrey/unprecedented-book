<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Combined;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\PropOrder;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

class Donor extends Entity
{
    #[PropOrder(0)]
    public int $id = 0;
    #[PropOrder(12)]
    public string $name = '';
    #[PropOrder(13)]
    public string $address = '';
    #[PropOrder(14)]
    public string $city = '';
    #[PropOrder(15)]
    public string $state = '';
    #[PropOrder(16)]
    public string $zip = '';
    #[PropOrder(17)]
    public string $occupation = '';
    #[PropOrder(18)]
    public string $employer = '';

    public function getDonorHash(): string
    {
        return StringUtilities::md5([
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'occupation' => $this->occupation,
            'employer' => $this->employer,
        ]);
    }

    public function getSurname(): string
    {
        $nameParts = explode(',', $this->name, 2);

        return array_shift($nameParts);
    }

    public function getZip5(): string
    {
        return StringUtilities::parseZip($this->zip)['zip5'];
    }
}
