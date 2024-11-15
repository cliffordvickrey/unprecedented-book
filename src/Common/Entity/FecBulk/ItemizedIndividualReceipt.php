<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecBulk;

use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Enum\Fec\AmendmentIndicator;
use CliffordVickrey\Book2024\Common\Enum\Fec\EntityType;
use CliffordVickrey\Book2024\Common\Enum\Fec\MemoCode;
use CliffordVickrey\Book2024\Common\Enum\Fec\ReportType;
use CliffordVickrey\Book2024\Common\Enum\Fec\TransactionType;

final class ItemizedIndividualReceipt extends FecBulkEntity
{
    #[PropMeta(1)]
    public string $CMTE_ID = ''; // Filer identification number
    #[PropMeta(2)]
    public ?AmendmentIndicator $AMNDT_IND = null; // Amendment indicator
    #[PropMeta(3)]
    public ?ReportType $RPT_TP = null; // Report type
    #[PropMeta(4)]
    public ?string $TRANSACTION_PGI = null; // Primary-general indicator
    #[PropMeta(5)]
    public ?string $IMAGE_NUM = null; // Image number
    #[PropMeta(6)]
    public ?TransactionType $TRANSACTION_TP = null; // Transaction type
    #[PropMeta(7)]
    public ?EntityType $ENTITY_TP = null; // Entity type
    #[PropMeta(8)]
    public ?string $NAME = null; // Contributor/Lender/Transfer Name
    #[PropMeta(9)]
    public ?string $CITY = null; // City
    #[PropMeta(10)]
    public ?string $STATE = null; // State
    #[PropMeta(11)]
    public ?string $ZIP_CODE = null; // ZIP Code
    #[PropMeta(12)]
    public ?string $EMPLOYER = null; // Employer
    #[PropMeta(13)]
    public ?string $OCCUPATION = null; // occupation
    #[PropMeta(14)]
    public ?\DateTimeImmutable $TRANSACTION_DT = null; // Transaction date (MMDDYYYY)
    #[PropMeta(15)]
    public ?float $TRANSACTION_AMT = null; // Transaction amount
    #[PropMeta(16)]
    public ?string $OTHER_ID = null; // Other identification number
    #[PropMeta(17)]
    public ?string $TRAN_ID = null; // Transaction ID
    #[PropMeta(18)]
    public ?int $FILE_NUM = null; // File number / Report ID
    #[PropMeta(19)]
    public ?MemoCode $MEMO_CD = null; // Memo code
    #[PropMeta(20)]
    public ?string $MEMO_TEXT = null; // Memo text
    #[PropMeta(21)]
    public int $SUB_ID = 0; // FEC record number
}
