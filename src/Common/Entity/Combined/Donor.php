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
    #[PropMeta(12)]
    public string $name = '';
    #[PropMeta(13)]
    public string $address = '';
    #[PropMeta(14)]
    public string $city = '';
    #[PropMeta(15)]
    public string $state = '';
    #[PropMeta(16)]
    public string $zip = '';
    #[PropMeta(17)]
    public string $occupation = '';
    #[PropMeta(18)]
    public string $employer = '';

    public function setId(int $id): void
    {
        $this->id = $id;

        if ('' === $this->name) {
            $this->name = StringUtilities::md5($this->id); // ensure uniqueness
        }
    }

    public static function groupSlugify(string $state, string $surname): string
    {
        return "{$state}_$surname";
    }

    public function getGroupSlug(): string
    {
        return self::groupSlugify($this->state, $this->getSurname());
    }

    public function getSurname(): string
    {
        $nameParts = explode(',', $this->name, 2);

        return array_shift($nameParts);
    }

    public function normalize(): void
    {
        // ensure state is two digits
        if (2 !== \strlen($this->state)) {
            $this->state = 'ZZ';
        }

        // ensure ZIP is zero-padded
        if ('' !== $this->zip && \strlen($this->zip) < 6) {
            $this->zip = str_pad($this->zip, 5, '0');
        } elseif (\strlen($this->zip) > 5) {
            $this->zip = str_pad($this->zip, 9, '0');
        }
    }

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

    public function getZip5(): string
    {
        return StringUtilities::parseZip($this->zip)['zip5'];
    }
}
