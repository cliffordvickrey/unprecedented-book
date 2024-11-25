<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\ValueObject;

use CliffordVickrey\Book2024\Common\Entity\Entity;

class CommitteeReceiptReport extends Entity
{
    public int $cycle = 0;
    public string $genre = '';
    public string $committee_slug = '';
    public string $committee_id = '';
    public string $committee_name = '';
    public string $committee_designation = '';
    public ?string $candidate_slug = null;
    public ?string $candidate_id = null;
    public ?string $candidate_name = null;
    public ?string $candidate_party = null;
    public ?string $candidate_office = null;
    public ?string $candidate_jurisdiction = null;
    public float $self_receipts = 0.0;
    public float $itemized_receipts = 0.0;
    public float $un_itemized_receipts = 0.0;
    public float $total_indiv_receipts = 0.0;
    public float $imputed_self_receipts = 0.0;
    public float $imputed_itemized_receipts = 0.0;
    public float $imputed_un_itemized_receipts = 0.0;
    public float $imputed_total_indiv_receipts = 0.0;
    public float $imputed_coverage = 0.0;
    public float $itemized_act_blue_receipts = 0.0;
    public float $un_itemized_act_blue_receipts = 0.0;
    public float $itemized_win_red_receipts = 0.0;
    public float $un_itemized_win_red_receipts = 0.0;
    public float $large_itemized_receipts_in_bulk_file = 0.0;
    public float $small_itemized_receipts_in_bulk_file = 0.0;
}
