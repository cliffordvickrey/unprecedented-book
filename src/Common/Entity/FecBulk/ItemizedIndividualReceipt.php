<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecBulk;

use CliffordVickrey\Book2024\Common\Entity\PropOrder;
use CliffordVickrey\Book2024\Common\Enum\Fec\AmendmentIndicator;
use CliffordVickrey\Book2024\Common\Enum\Fec\EntityType;
use CliffordVickrey\Book2024\Common\Enum\Fec\MemoCode;
use CliffordVickrey\Book2024\Common\Enum\Fec\ReportType;
use CliffordVickrey\Book2024\Common\Enum\Fec\TransactionType;

final class ItemizedIndividualReceipt extends FecBulkEntity
{
    #[PropOrder(1)]
    public string $CMTE_ID = ''; // Filer identification number
    #[PropOrder(2)]
    public ?AmendmentIndicator $AMNDT_IND = null; // Amendment indicator
    #[PropOrder(3)]
    public ?ReportType $RPT_TP = null; // Report type
    #[PropOrder(4)]
    public ?string $TRANSACTION_PGI = null; // Primary-general indicator
    #[PropOrder(5)]
    public ?string $IMAGE_NUM = null; // Image number
    #[PropOrder(6)]
    public ?TransactionType $TRANSACTION_TP = null; // Transaction type
    #[PropOrder(7)]
    public ?EntityType $ENTITY_TP = null; // Entity type
    #[PropOrder(8)]
    public ?string $NAME = null; // Contributor/Lender/Transfer Name
    #[PropOrder(9)]
    public ?string $CITY = null; // City
    #[PropOrder(10)]
    public ?string $STATE = null; // State
    #[PropOrder(11)]
    public ?string $ZIP_CODE = null; // ZIP Code
    #[PropOrder(12)]
    public ?string $EMPLOYER = null; // Employer
    #[PropOrder(13)]
    public ?string $OCCUPATION = null; // occupation
    #[PropOrder(14)]
    public ?\DateTimeImmutable $TRANSACTION_DT = null; // Transaction date (MMDDYYYY)
    #[PropOrder(15)]
    public ?float $TRANSACTION_AMT = null; // Transaction amount
    #[PropOrder(16)]
    public ?string $OTHER_ID = null; // Other identification number
    #[PropOrder(17)]
    public ?string $TRAN_ID = null; // Transaction ID
    #[PropOrder(18)]
    public ?int $FILE_NUM = null; // File number / Report ID
    #[PropOrder(19)]
    public ?MemoCode $MEMO_CD = null; // Memo code
    #[PropOrder(20)]
    public ?string $MEMO_TEXT = null; // Memo text
    #[PropOrder(21)]
    public int $SUB_ID = 0; // FEC record number
}
