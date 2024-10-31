<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecApi;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\PropOrder;
use CliffordVickrey\Book2024\Common\Enum\Fec\TransactionType;

class ScheduleAReceipt extends Entity
{
    #[PropOrder(0)]
    public ?string $candidate_id = null;
    #[PropOrder(1)]
    public float $contribution_receipt_amount = 0.0;
    #[PropOrder(2)]
    public \DateTimeImmutable $contribution_receipt_date;
    #[PropOrder(3)]
    public string $contributor_city = '';
    #[PropOrder(4)]
    public string $contributor_employer = '';
    #[PropOrder(5)]
    public string $contributor_name = '';
    #[PropOrder(6)]
    public string $contributor_occupation = '';
    #[PropOrder(7)]
    public string $contributor_state = '';
    #[PropOrder(8)]
    public string $contributor_street_1 = '';
    #[PropOrder(9)]
    public string $contributor_zip = '';
    #[PropOrder(10)]
    public string $memo_text = '';
    #[PropOrder(11)]
    public ?TransactionType $receipt_type = null;
}
