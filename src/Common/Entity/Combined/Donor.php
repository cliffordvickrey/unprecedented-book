<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Combined;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;
use Webmozart\Assert\Assert;

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
            $this->name = StringUtilities::nonce($this->id); // ensure uniqueness
        }
    }

    public function getSurname(): string
    {
        $nameParts = explode(',', $this->name, 2);

        return trim(array_shift($nameParts));
    }

    public function getNormalizedSurname(): string
    {
        $surname = preg_replace('/[\'’`]/', '', $this->getSurname());
        Assert::string($surname);
        $surname = preg_replace('/[^A-Z]/', '_', $surname);
        Assert::string($surname);

        if ('' === $surname) {
            return '_';
        }

        return $surname;
    }

    public function normalize(): void
    {
        // ensure state is two digits
        if (!preg_match('/^[A-Z]{2}$/', $this->state)) {
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
