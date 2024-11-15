<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecBulk;

use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeFilingFrequency;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeInterestGroupCategory;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeType;

final class CommitteeSummary extends FecBulkEntity
{
    #[PropMeta(1)]
    public string $Link_Image = ''; // Link to committee profile page
    #[PropMeta(2)]
    public string $CMTE_ID = ''; // Committee ID
    #[PropMeta(3)]
    public ?string $CMTE_NM = null; // Committee name
    #[PropMeta(4)]
    public ?CommitteeType $CMTE_TP = null; // Committee type
    #[PropMeta(5)]
    public ?CommitteeDesignation $CMTE_DSGN = null; // Committee designation
    #[PropMeta(6)]
    public ?CommitteeFilingFrequency $CMTE_FILING_FREQ = null; // Filing frequency
    #[PropMeta(7)]
    public ?string $CMTE_ST1 = null; // Address
    #[PropMeta(8)]
    public ?string $CMTE_ST2 = null; // Address
    #[PropMeta(9)]
    public ?string $CMTE_CITY = null; // City
    #[PropMeta(10)]
    public ?string $CMTE_ST = null; // State
    #[PropMeta(11)]
    public ?int $CMTE_ZIP = null; // ZIP Code
    #[PropMeta(12)]
    public ?string $TRES_NM = null; // Treasurer's name
    #[PropMeta(13)]
    public ?string $CAND_ID = null; // Candidate ID
    #[PropMeta(14)]
    public ?int $FEC_ELECTION_YR = null; // FEC election year
    #[PropMeta(15)]
    public ?float $INDV_CONTB = null; // Individual contribution
    #[PropMeta(16)]
    public ?float $PTY_CMTE_CONTB = null; // Party committee contribution
    #[PropMeta(17)]
    public ?float $OTH_CMTE_CONTB = null; // Other committee contribution
    #[PropMeta(18)]
    public ?float $TTL_CONTB = null; // Total contribution
    #[PropMeta(19)]
    public ?float $TRANF_FROM_OTHER_AUTH_CMTE = null; // Transfer from other authorized committee
    #[PropMeta(20)]
    public ?float $OFFSETS_TO_OP_EXP = null; // Offsets to operating expenditure
    #[PropMeta(21)]
    public ?float $OTHER_RECEIPTS = null; // Other receipts
    #[PropMeta(22)]
    public ?float $TTL_RECEIPTS = null; // Total receipts
    #[PropMeta(23)]
    public ?float $TRANF_TO_OTHER_AUTH_CMTE = null; // Transfer to other authorized committee
    #[PropMeta(24)]
    public ?float $OTH_LOAN_REPYMTS = null; // Other loan repayment
    #[PropMeta(25)]
    public ?float $INDV_REF = null; // Individual refund
    #[PropMeta(26)]
    public ?float $POL_PTY_CMTE_REF = null; // Political party committee refund
    #[PropMeta(27)]
    public ?float $TTL_CONTB_REF = null; // Total contribution refund
    #[PropMeta(28)]
    public ?float $OTHER_DISB = null; // Other disbursement
    #[PropMeta(29)]
    public ?float $TTL_DISB = null; // Total disbursement
    #[PropMeta(30)]
    public ?float $NET_CONTB = null; // Net contribution
    #[PropMeta(31)]
    public ?float $NET_OP_EXP = null; // Net operating expenditure
    #[PropMeta(32)]
    public ?float $COH_BOP = null; // Cash on hand beginning of period
    #[PropMeta(33)]
    public ?\DateTimeImmutable $CVG_START_DT = null; // Coverage start date
    #[PropMeta(34)]
    public ?float $COH_COP = null; // Cash on hand closing of period
    #[PropMeta(35)]
    public ?\DateTimeImmutable $CVG_END_DT = null; // Coverage end date
    #[PropMeta(36)]
    public ?float $DEBTS_OWED_BY_CMTE = null; // Debt owed by committee
    #[PropMeta(37)]
    public ?float $DEBTS_OWED_TO_CMTE = null; // Debt owed to committee
    #[PropMeta(38)]
    public ?float $INDV_ITEM_CONTB = null; // Individual itemized contribution
    #[PropMeta(39)]
    public ?float $INDV_UNITEM_CONTB = null; // Individual unitemized contribution
    #[PropMeta(40)]
    public ?float $OTH_LOANS = null; // Other loan
    #[PropMeta(41)]
    public ?float $TRANF_FROM_NONFED_ACCT = null; // Transfer from non federal account
    #[PropMeta(42)]
    public ?float $TRANF_FROM_NONFED_LEVIN = null; // Transfer from non federal Levin account
    #[PropMeta(43)]
    public ?float $TTL_NONFED_TRANF = null; // Total non federal transfer
    #[PropMeta(44)]
    public ?float $LOAN_REPYMTS_RECEIVED = null; // Loan repayments received
    #[PropMeta(45)]
    public ?float $OFFSETS_TO_FNDRSG = null; // Offsets to fundraising expenses (Presidential only)
    #[PropMeta(46)]
    public ?float $OFFSETS_TO_LEGAL_ACCTG = null; // Offsets to legal/accounting expenses (Presidential only)
    #[PropMeta(47)]
    public ?float $FED_CAND_CONTB_REF = null; // Federal candidate contribution refund
    #[PropMeta(48)]
    public ?float $TTL_FED_RECEIPTS = null; // Total federal receipt
    #[PropMeta(49)]
    public ?float $SHARED_FED_OP_EXP = null; // Shared federal operating expenditure
    #[PropMeta(50)]
    public ?float $SHARED_NONFED_OP_EXP = null; // Shared non-federal operating expenditure
    #[PropMeta(51)]
    public ?float $OTH_FED_OPE_EXP = null; // Other federal operating expenditures
    #[PropMeta(52)]
    public ?float $TTL_OP_EXP = null; // Total operating expenditure
    #[PropMeta(53)]
    public ?float $FED_CAND_CMTE_CONTB = null; // Federal candidate committee contribution
    #[PropMeta(54)]
    public ?float $INDT_EXP = null; // Independent expenditures made
    #[PropMeta(55)]
    public ?float $COORD_EXP_BY_PTY_CMTE = null; // Coordinated expenditure (party only)
    #[PropMeta(56)]
    public ?float $LOANS_MADE = null; // Loan made
    #[PropMeta(57)]
    public ?float $SHARED_FED_ACTVY_FED_SHR = null; // Federal share of joint Federal Election Activity
    #[PropMeta(58)]
    public ?float $SHARED_FED_ACTVY_NONFED = null; // Non-federal share of joint Federal Election Activity
    #[PropMeta(59)]
    public ?float $NON_ALLOC_FED_ELECT_ACTVY = null; // Non-allocated Federal Election Activity (Party only)
    #[PropMeta(60)]
    public ?float $TTL_FED_ELECT_ACTVY = null; // Total Federal Election Activity
    #[PropMeta(61)]
    public ?float $TTL_FED_DISB = null; // Total federal disbursement
    #[PropMeta(62)]
    public ?float $CAND_CNTB = null; // Candidate contribution
    #[PropMeta(63)]
    public ?float $CAND_LOAN = null; // Candidate loan
    #[PropMeta(64)]
    public ?float $TTL_LOANS = null; // Total loan
    #[PropMeta(65)]
    public ?float $OP_EXP = null; // Operating expenditure
    #[PropMeta(66)]
    public ?float $CAND_LOAN_REPYMNT = null; // Candidate loan repayment
    #[PropMeta(67)]
    public ?float $TTL_LOAN_REPYMTS = null; // Total loan repayment
    #[PropMeta(68)]
    public ?float $OTH_CMTE_REF = null; // Other committee refund
    #[PropMeta(69)]
    public ?float $TTL_OFFSETS_TO_OP_EXP = null; // Total offsets to operating expenditure
    #[PropMeta(70)]
    public ?float $EXEMPT_LEGAL_ACCTG_DISB = null; // Exempt legal/accounting disbursement (Presidential only)
    #[PropMeta(71)]
    public ?float $FNDRSG_DISB = null; // Fundraising disbursement
    #[PropMeta(72)]
    public ?float $ITEM_REF_REB_RET = null; // Itemized refunds or rebates
    #[PropMeta(73)]
    public ?float $SUBTTL_REF_REB_RET = null; // Subtotal refunds or rebates
    #[PropMeta(74)]
    public ?float $UNITEM_REF_REB_RET = null; // Unitemized refunds or rebates
    #[PropMeta(75)]
    public ?float $ITEM_OTHER_REF_REB_RET = null; // Itemized other refunds or rebates
    #[PropMeta(76)]
    public ?float $UNITEM_OTHER_REF_REB_RET = null; // Unitemized other refunds or rebates
    #[PropMeta(77)]
    public ?float $SUBTTL_OTHER_REF_REB_RETB = null; // Subtotal other refunds or rebates
    #[PropMeta(78)]
    public ?float $ITEM_OTHER_INCOME = null; // Itemized other income
    #[PropMeta(79)]
    public ?float $UNITEM_OTHER_INCOME = null; // Unitemized other income
    #[PropMeta(80)]
    public ?float $EXP_PRIOR_YRS_SUBJECT_LIM = null; // Expenditures subject to limit - prior year (Presidential only)
    #[PropMeta(81)]
    public ?float $EXP_SUBJECT_LIMITS = null; // Expenditures subject to limit
    #[PropMeta(82)]
    public ?float $FED_FUNDS = null; // Federal funds
    #[PropMeta(83)]
    public ?float $ITEM_CONVN_EXP_DISB = null; // Itemized convention expenditure (Convention committee only)
    #[PropMeta(84)]
    public ?float $ITEM_OTHER_DISB = null; // Itemized other disbursement
    #[PropMeta(85)]
    public ?float $SUBTTL_CONVN_EXP_DISB = null; // Subtotal convention expenses
    #[PropMeta(86)]
    public ?float $TTL_EXP_SUBJECT_LIMITS = null; // Total expenditures subject to limit (Presidential only)
    #[PropMeta(87)]
    public ?float $UNITEM_CONVN_EXP_DISB = null; // Unitemized convention expenses
    #[PropMeta(88)]
    public ?float $UNITEM_OTHER_DISB = null; // Unitemized other disbursements
    #[PropMeta(89)]
    public ?float $TTL_COMMUNICATION_COSTS = null; // Total communication cost
    #[PropMeta(90)]
    public ?float $COH_BOY = null; // Cash on hand beginning of year
    #[PropMeta(91)]
    public ?float $COH_COY = null; // Cash on hand closing of year
    #[PropMeta(92)]
    public ?CommitteeInterestGroupCategory $ORG_TYPE = null; // Organization type
}
