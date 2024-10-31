<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecBulk;

use CliffordVickrey\Book2024\Common\Entity\PropOrder;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeFilingFrequency;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeInterestGroupCategory;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeType;

final class CommitteeSummary extends FecBulkEntity
{
    #[PropOrder(1)]
    public string $Link_Image = ''; // Link to committee profile page
    #[PropOrder(2)]
    public string $CMTE_ID = ''; // Committee ID
    #[PropOrder(3)]
    public ?string $CMTE_NM = null; // Committee name
    #[PropOrder(4)]
    public ?CommitteeType $CMTE_TP = null; // Committee type
    #[PropOrder(5)]
    public ?CommitteeDesignation $CMTE_DSGN = null; // Committee designation
    #[PropOrder(6)]
    public ?CommitteeFilingFrequency $CMTE_FILING_FREQ = null; // Filing frequency
    #[PropOrder(7)]
    public ?string $CMTE_ST1 = null; // Address
    #[PropOrder(8)]
    public ?string $CMTE_ST2 = null; // Address
    #[PropOrder(9)]
    public ?string $CMTE_CITY = null; // City
    #[PropOrder(10)]
    public ?string $CMTE_ST = null; // State
    #[PropOrder(11)]
    public ?int $CMTE_ZIP = null; // ZIP Code
    #[PropOrder(12)]
    public ?string $TRES_NM = null; // Treasurer's name
    #[PropOrder(13)]
    public ?string $CAND_ID = null; // Candidate ID
    #[PropOrder(14)]
    public ?int $FEC_ELECTION_YR = null; // FEC election year
    #[PropOrder(15)]
    public ?float $INDV_CONTB = null; // Individual contribution
    #[PropOrder(16)]
    public ?float $PTY_CMTE_CONTB = null; // Party committee contribution
    #[PropOrder(17)]
    public ?float $OTH_CMTE_CONTB = null; // Other committee contribution
    #[PropOrder(18)]
    public ?float $TTL_CONTB = null; // Total contribution
    #[PropOrder(19)]
    public ?float $TRANF_FROM_OTHER_AUTH_CMTE = null; // Transfer from other authorized committee
    #[PropOrder(20)]
    public ?float $OFFSETS_TO_OP_EXP = null; // Offsets to operating expenditure
    #[PropOrder(21)]
    public ?float $OTHER_RECEIPTS = null; // Other receipts
    #[PropOrder(22)]
    public ?float $TTL_RECEIPTS = null; // Total receipts
    #[PropOrder(23)]
    public ?float $TRANF_TO_OTHER_AUTH_CMTE = null; // Transfer to other authorized committee
    #[PropOrder(24)]
    public ?float $OTH_LOAN_REPYMTS = null; // Other loan repayment
    #[PropOrder(25)]
    public ?float $INDV_REF = null; // Individual refund
    #[PropOrder(26)]
    public ?float $POL_PTY_CMTE_REF = null; // Political party committee refund
    #[PropOrder(27)]
    public ?float $TTL_CONTB_REF = null; // Total contribution refund
    #[PropOrder(28)]
    public ?float $OTHER_DISB = null; // Other disbursement
    #[PropOrder(29)]
    public ?float $TTL_DISB = null; // Total disbursement
    #[PropOrder(30)]
    public ?float $NET_CONTB = null; // Net contribution
    #[PropOrder(31)]
    public ?float $NET_OP_EXP = null; // Net operating expenditure
    #[PropOrder(32)]
    public ?float $COH_BOP = null; // Cash on hand beginning of period
    #[PropOrder(33)]
    public ?\DateTimeImmutable $CVG_START_DT = null; // Coverage start date
    #[PropOrder(34)]
    public ?float $COH_COP = null; // Cash on hand closing of period
    #[PropOrder(35)]
    public ?\DateTimeImmutable $CVG_END_DT = null; // Coverage end date
    #[PropOrder(36)]
    public ?float $DEBTS_OWED_BY_CMTE = null; // Debt owed by committee
    #[PropOrder(37)]
    public ?float $DEBTS_OWED_TO_CMTE = null; // Debt owed to committee
    #[PropOrder(38)]
    public ?float $INDV_ITEM_CONTB = null; // Individual itemized contribution
    #[PropOrder(39)]
    public ?float $INDV_UNITEM_CONTB = null; // Individual unitemized contribution
    #[PropOrder(40)]
    public ?float $OTH_LOANS = null; // Other loan
    #[PropOrder(41)]
    public ?float $TRANF_FROM_NONFED_ACCT = null; // Transfer from non federal account
    #[PropOrder(42)]
    public ?float $TRANF_FROM_NONFED_LEVIN = null; // Transfer from non federal Levin account
    #[PropOrder(43)]
    public ?float $TTL_NONFED_TRANF = null; // Total non federal transfer
    #[PropOrder(44)]
    public ?float $LOAN_REPYMTS_RECEIVED = null; // Loan repayments received
    #[PropOrder(45)]
    public ?float $OFFSETS_TO_FNDRSG = null; // Offsets to fundraising expenses (Presidential only)
    #[PropOrder(46)]
    public ?float $OFFSETS_TO_LEGAL_ACCTG = null; // Offsets to legal/accounting expenses (Presidential only)
    #[PropOrder(47)]
    public ?float $FED_CAND_CONTB_REF = null; // Federal candidate contribution refund
    #[PropOrder(48)]
    public ?float $TTL_FED_RECEIPTS = null; // Total federal receipt
    #[PropOrder(49)]
    public ?float $SHARED_FED_OP_EXP = null; // Shared federal operating expenditure
    #[PropOrder(50)]
    public ?float $SHARED_NONFED_OP_EXP = null; // Shared non-federal operating expenditure
    #[PropOrder(51)]
    public ?float $OTH_FED_OPE_EXP = null; // Other federal operating expenditures
    #[PropOrder(52)]
    public ?float $TTL_OP_EXP = null; // Total operating expenditure
    #[PropOrder(53)]
    public ?float $FED_CAND_CMTE_CONTB = null; // Federal candidate committee contribution
    #[PropOrder(54)]
    public ?float $INDT_EXP = null; // Independent expenditures made
    #[PropOrder(55)]
    public ?float $COORD_EXP_BY_PTY_CMTE = null; // Coordinated expenditure (party only)
    #[PropOrder(56)]
    public ?float $LOANS_MADE = null; // Loan made
    #[PropOrder(57)]
    public ?float $SHARED_FED_ACTVY_FED_SHR = null; // Federal share of joint Federal Election Activity
    #[PropOrder(58)]
    public ?float $SHARED_FED_ACTVY_NONFED = null; // Non-federal share of joint Federal Election Activity
    #[PropOrder(59)]
    public ?float $NON_ALLOC_FED_ELECT_ACTVY = null; // Non-allocated Federal Election Activity (Party only)
    #[PropOrder(60)]
    public ?float $TTL_FED_ELECT_ACTVY = null; // Total Federal Election Activity
    #[PropOrder(61)]
    public ?float $TTL_FED_DISB = null; // Total federal disbursement
    #[PropOrder(62)]
    public ?float $CAND_CNTB = null; // Candidate contribution
    #[PropOrder(63)]
    public ?float $CAND_LOAN = null; // Candidate loan
    #[PropOrder(64)]
    public ?float $TTL_LOANS = null; // Total loan
    #[PropOrder(65)]
    public ?float $OP_EXP = null; // Operating expenditure
    #[PropOrder(66)]
    public ?float $CAND_LOAN_REPYMNT = null; // Candidate loan repayment
    #[PropOrder(67)]
    public ?float $TTL_LOAN_REPYMTS = null; // Total loan repayment
    #[PropOrder(68)]
    public ?float $OTH_CMTE_REF = null; // Other committee refund
    #[PropOrder(69)]
    public ?float $TTL_OFFSETS_TO_OP_EXP = null; // Total offsets to operating expenditure
    #[PropOrder(70)]
    public ?float $EXEMPT_LEGAL_ACCTG_DISB = null; // Exempt legal/accounting disbursement (Presidential only)
    #[PropOrder(71)]
    public ?float $FNDRSG_DISB = null; // Fundraising disbursement
    #[PropOrder(72)]
    public ?float $ITEM_REF_REB_RET = null; // Itemized refunds or rebates
    #[PropOrder(73)]
    public ?float $SUBTTL_REF_REB_RET = null; // Subtotal refunds or rebates
    #[PropOrder(74)]
    public ?float $UNITEM_REF_REB_RET = null; // Unitemized refunds or rebates
    #[PropOrder(75)]
    public ?float $ITEM_OTHER_REF_REB_RET = null; // Itemized other refunds or rebates
    #[PropOrder(76)]
    public ?float $UNITEM_OTHER_REF_REB_RET = null; // Unitemized other refunds or rebates
    #[PropOrder(77)]
    public ?float $SUBTTL_OTHER_REF_REB_RETB = null; // Subtotal other refunds or rebates
    #[PropOrder(78)]
    public ?float $ITEM_OTHER_INCOME = null; // Itemized other income
    #[PropOrder(79)]
    public ?float $UNITEM_OTHER_INCOME = null; // Unitemized other income
    #[PropOrder(80)]
    public ?float $EXP_PRIOR_YRS_SUBJECT_LIM = null; // Expenditures subject to limit - prior year (Presidential only)
    #[PropOrder(81)]
    public ?float $EXP_SUBJECT_LIMITS = null; // Expenditures subject to limit
    #[PropOrder(82)]
    public ?float $FED_FUNDS = null; // Federal funds
    #[PropOrder(83)]
    public ?float $ITEM_CONVN_EXP_DISB = null; // Itemized convention expenditure (Convention committee only)
    #[PropOrder(84)]
    public ?float $ITEM_OTHER_DISB = null; // Itemized other disbursement
    #[PropOrder(85)]
    public ?float $SUBTTL_CONVN_EXP_DISB = null; // Subtotal convention expenses
    #[PropOrder(86)]
    public ?float $TTL_EXP_SUBJECT_LIMITS = null; // Total expenditures subject to limit (Presidential only)
    #[PropOrder(87)]
    public ?float $UNITEM_CONVN_EXP_DISB = null; // Unitemized convention expenses
    #[PropOrder(88)]
    public ?float $UNITEM_OTHER_DISB = null; // Unitemized other disbursements
    #[PropOrder(89)]
    public ?float $TTL_COMMUNICATION_COSTS = null; // Total communication cost
    #[PropOrder(90)]
    public ?float $COH_BOY = null; // Cash on hand beginning of year
    #[PropOrder(91)]
    public ?float $COH_COY = null; // Cash on hand closing of year
    #[PropOrder(92)]
    public ?CommitteeInterestGroupCategory $ORG_TYPE = null; // Organization type
}
