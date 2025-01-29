<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile;

use CliffordVickrey\Book2024\Common\Entity\Entity;

abstract class DonorProfileCycle extends Entity
{
    public bool $nonPresDemocratic = false;
    public bool $nonPresRepublican = false;
    public bool $nonPresThirdParty = false;
    public bool $pac = false;
    public bool $party = false;
    public bool $presOtherDemocratic = false;
    public bool $presOtherRepublican = false;
    public bool $presOtherThirdParty = false;
}