<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Combined;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

class Donor extends Entity
{
    #[PropMeta(0)]
    public int $id = 0;
    #[PropMeta(11)]
    public string $name = '';
    #[PropMeta(12)]
    public string $address = '';
    #[PropMeta(13)]
    public string $city = '';
    #[PropMeta(14)]
    public string $state = '';
    #[PropMeta(15)]
    public string $zip = '';
    #[PropMeta(16)]
    public string $occupation = '';
    #[PropMeta(17)]
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
