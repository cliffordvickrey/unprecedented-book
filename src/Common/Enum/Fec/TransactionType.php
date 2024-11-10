<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum TransactionType: string
{
    case _10 = '10'; // Contribution to Independent Expenditure-Only Committees (Super PACs), Political Committees with non-contribution accounts (Hybrid PACs) and nonfederal party "soft money" accounts (1991-2002) from a person (individual, partnership, limited liability company, corporation, labor organization, or any other organization or group of persons)
    case _10J = '10J'; // Memo - Recipient committee's percentage of nonfederal receipt from a person (individual, partnership, limited liability company, corporation, labor organization, or any other organization or group of persons)
    case _11 = '11'; // Native American Tribe contribution
    case _11J = '11J'; // Memo - Recipient committee's percentage of contribution from Native American Tribe given to joint fundraising committee
    case _12 = '12'; // Nonfederal other receipt - Levin Account (Line 2)
    case _13 = '13'; // Inaugural donation accepted
    case _15 = '15'; // Contribution to political committees (other than Super PACs and Hybrid PACs) from an individual, partnership or limited liability company
    case _15C = '15C'; // Contribution from candidate
    case _15E = '15E'; // Earmarked contributions to political committees (other than Super PACs and Hybrid PACs) from an individual, partnership or limited liability company
    case _15F = '15F'; // Loans forgiven by candidate
    case _15I = '15I'; // Earmarked contribution from an individual, partnership or limited liability company received by intermediary committee and passed on in the form of contributor's check (intermediary in)
    case _15J = '15J'; // Memo - Recipient committee's percentage of contribution from an individual, partnership or limited liability company given to joint fundraising committee
    case _15K = '15K'; // Contribution received from registered filer disclosed on authorized committee report
    case _15T = '15T'; // Earmarked contribution from an individual, partnership or limited liability company received by intermediary committee and entered into intermediary's treasury (intermediary treasury in)
    case _15Z = '15Z'; // In-kind contribution received from registered filer
    case _16C = '16C'; // Loan received from the candidate
    case _16F = '16F'; // Loan received from bank
    case _16G = '16G'; // Loan from individual
    case _16H = '16H'; // Loan from registered filers
    case _16J = '16J'; // Loan repayment from individual
    case _16K = '16K'; // Loan repayment from from registered filer
    case _16L = '16L'; // Loan repayment received from unregistered entity
    case _16R = '16R'; // Loan received from registered filers
    case _16U = '16U'; // Loan received from unregistered entity
    case _17R = '17R'; // Contribution refund received from registered entity
    case _17U = '17U'; // Refund/Rebate/Return received from unregistered entity
    case _17Y = '17Y'; // Refund/Rebate/Return from individual or corporation
    case _17Z = '17Z'; // Refund/Rebate/Return from candidate or committee
    case _18G = '18G'; // Transfer in from affiliated committee
    case _18H = '18H'; // Honorarium received
    case _18J = '18J'; // Memo - Recipient committee's percentage of contribution from a registered committee given to joint fundraising committee
    case _18K = '18K'; // Contribution received from registered filer
    case _18L = '18L'; // Bundled contribution
    case _18U = '18U'; // Contribution received from unregistered committee
    case _19 = '19'; // Electioneering communication donation received
    case _19J = '19J'; // Memo - Recipient committee's percentage of Electioneering Communication donation given to joint fundraising committee
    case _20 = '20'; // Nonfederal disbursement - nonfederal party "soft money" accounts (1991-2002)
    case _20A = '20A'; // Nonfederal disbursement - Levin Account (Line 4A) Voter Registration
    case _20B = '20B'; // Nonfederal Disbursement - Levin Account (Line 4B) Voter Identification
    case _20C = '20C'; // Loan repayment made to candidate
    case _20D = '20D'; // Nonfederal disbursement - Levin Account (Line 4D) Generic Campaign
    case _20F = '20F'; // Loan repayment made to banks
    case _20G = '20G'; // Loan repayment made to individual
    case _20R = '20R'; // Loan repayment made to registered filer
    case _20V = '20V'; // Nonfederal disbursement - Levin Account (Line 4C) Get Out The Vote
    case _20Y = '20Y'; // Nonfederal refund
    case _21Y = '21Y'; // Native American Tribe refund
    case _22G = '22G'; // Loan to individual
    case _22H = '22H'; // Loan to candidate or committee
    case _22J = '22J'; // Loan repayment to individual
    case _22K = '22K'; // Loan repayment to candidate or committee
    case _22L = '22L'; // Loan repayment to bank
    case _22R = '22R'; // Contribution refund to unregistered entity
    case _22U = '22U'; // Loan repaid to unregistered entity
    case _22X = '22X'; // Loan made to unregistered entity
    case _22Y = '22Y'; // Contribution refund to an individual, partnership or limited liability company
    case _22Z = '22Z'; // Contribution refund to candidate or committee
    case _23Y = '23Y'; // Inaugural donation refund
    case _24A = '24A'; // Independent expenditure opposing election of candidate
    case _24C = '24C'; // Coordinated party expenditure
    case _24E = '24E'; // Independent expenditure advocating election of candidate
    case _24F = '24F'; // Communication cost for candidate (only for Form 7 filer)
    case _24G = '24G'; // Transfer out to affiliated committee
    case _24H = '24H'; // Honorarium to candidate
    case _24I = '24I'; // Earmarked contributor's check passed on by intermediary committee to intended recipient (intermediary out)
    case _24K = '24K'; // Contribution made to nonaffiliated committee
    case _24N = '24N'; // Communication cost against candidate (only for Form 7 filer)
    case _24P = '24P'; // Contribution made to possible federal candidate including in-kind contributions
    case _24R = '24R'; // Election recount disbursement
    case _24T = '24T'; // Earmarked contribution passed to intended recipient from intermediary's treasury (treasury out)
    case _24U = '24U'; // Contribution made to unregistered entity
    case _24Z = '24Z'; // In-kind contribution made to registered filer
    case _28L = '28L'; // Refund of bundled contribution
    case _29 = '29'; // Electioneering Communication disbursement or obligation
    case _30 = '30'; // Convention Account receipt from an individual, partnership or limited liability company
    case _30E = '30E'; // Convention Account - Earmarked receipt
    case _30F = '30F'; // Convention Account - Memo - Recipient committee's percentage of contributions from a registered committee given to joint fundraising committee
    case _30G = '30G'; // Convention Account - transfer in from affiliated committee
    case _30J = '30J'; // Convention Account - Memo - Recipient committee's percentage of contributions from an individual, partnership or limited liability company given to joint fundraising committee
    case _30K = '30K'; // Convention Account receipt from registered filer
    case _30T = '30T'; // Convention Account receipt from Native American Tribe
    case _31 = '31'; // Headquarters Account receipt from an individual, partnership or limited liability company
    case _31E = '31E'; // Headquarters Account - Earmarked receipt
    case _31F = '31F'; // Headquarters Account - Memo - Recipient committee's percentage of contributions from a registered committee given to joint fundraising committee
    case _31G = '31G'; // Headquarters Account - transfer in from affiliated committee
    case _31J = '31J'; // Headquarters Account - Memo - Recipient committee's percentage of contributions from an individual, partnership or limited liability company given to joint fundraising committee
    case _31K = '31K'; // Headquarters Account receipt from registered filer
    case _31T = '31T'; // Headquarters Account receipt from Native American Tribe
    case _32 = '32'; // Recount Account receipt from an individual, partnership or limited liability company
    case _32E = '32E'; // Recount Account - Earmarked receipt
    case _32F = '32F'; // Recount Account - Memo - Recipient committee's percentage of contributions from a registered committee given to joint fundraising committee
    case _32G = '32G'; // Recount Account - transfer in from affiliated committee
    case _32J = '32J'; // Recount Account - Memo - Recipient committee's percentage of contributions from an individual, partnership or limited liability company given to joint fundraising committee
    case _32K = '32K'; // Recount Account receipt from registered filer
    case _32T = '32T'; // Recount Account receipt from Native American Tribe
    case _40 = '40'; // Convention Account disbursement
    case _40Y = '40Y'; // Convention Account refund to an individual, partnership or limited liability company
    case _40T = '40T'; // Convention Account refund to Native American Tribe
    case _40Z = '40Z'; // Convention Account refund to registered filer
    case _41 = '41'; // Headquarters Account disbursement
    case _41Y = '41Y'; // Headquarters Account refund to an individual, partnership or limited liability company
    case _41T = '41T'; // Headquarters Account refund to Native American Tribe
    case _41Z = '41Z'; // Headquarters Account refund to registered filer
    case _42 = '42'; // Recount Account disbursement
    case _42Y = '42Y'; // Recount Account refund to an individual, partnership or limited liability company
    case _42T = '42T'; // Recount Account refund to Native American Tribe
    case _42Z = '42Z'; // Recount Account refund to registered filer

    public function isContribution(): bool
    {
        return TransactionType::_10 === $this   // super PAC
            || TransactionType::_11 === $this   // Native American tribe
            || TransactionType::_15 === $this   // individual
            || TransactionType::_15C === $this  // from candidate
            || TransactionType::_15E === $this  // earmarked
            || TransactionType::_24I === $this  // intermediary out
            || TransactionType::_24T === $this; // treasury out
    }
}
