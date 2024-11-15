<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecApi;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Enum\Fec\TransactionType;

class ScheduleAReceipt extends Entity
{
    #[PropMeta(0)]
    public ?string $candidate_id = null;
    #[PropMeta(1)]
    public float $contribution_receipt_amount = 0.0;
    #[PropMeta(2)]
    public \DateTimeImmutable $contribution_receipt_date;
    #[PropMeta(3)]
    public string $contributor_city = '';
    #[PropMeta(4)]
    public string $contributor_employer = '';
    #[PropMeta(5)]
    public string $contributor_name = '';
    #[PropMeta(6)]
    public string $contributor_occupation = '';
    #[PropMeta(7)]
    public string $contributor_state = '';
    #[PropMeta(8)]
    public string $contributor_street_1 = '';
    #[PropMeta(9)]
    public string $contributor_zip = '';
    #[PropMeta(10, 'receipt_type_full')]
    public string $memo_text = '';
    #[PropMeta(11)]
    public ?TransactionType $receipt_type = null;
}
