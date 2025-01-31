<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile;

use CliffordVickrey\Book2024\Common\Enum\PartyType;

class DonorProfileCycle2016 extends DonorProfileCycle
{
    #[RecipientAttribute(
        slug: 'ben_carson',
        party: PartyType::republican,
        startDate: '2015-05-03',
        endDate: '2016-03-04',
        committeeIds: ['C00573519', 'C00569905', 'C00548420', 'C00577296', 'C00586685', 'C00574277']
    )]
    public bool $presBenCarson = false;
    #[RecipientAttribute(
        slug: 'bernie_sanders',
        party: PartyType::democrat,
        startDate: '2015-04-30',
        endDate: '2016-07-12',
        committeeIds: [
            'C00577130',
            'C00492595',
            'C00600817',
            'C00406553',
            'C00590646',
            'C00590620',
            'C00555615',
            'C00589226',
            'C00609602',
            'C00589937',
            'C00590240'
        ]
    )]
    public bool $presBernieSanders = false;
    #[RecipientAttribute(
        slug: 'bobby_jindal',
        party: PartyType::republican,
        startDate: '2015-06-24',
        endDate: '2015-11-17',
        committeeIds: ['C00580159', 'C00571711', 'C00559237']
    )]
    public bool $presBobbyJindal = false;
    #[RecipientAttribute(
        slug: 'carly_fiorina',
        party: PartyType::republican,
        startDate: '2015-05-04',
        endDate: '2016-02-10',
        committeeIds: ['C00577312', 'C00573154', 'C00564534']
    )]
    public bool $presCarlyFiorina = false;
    #[RecipientAttribute(
        slug: 'chris_christie',
        party: PartyType::republican,
        startDate: '2015-06-30',
        endDate: '2016-02-10',
        committeeIds: ['C00574251', 'C00573055', 'C00571778']
    )]
    public bool $presChrisChristie = false;
    #[RecipientAttribute(
        slug: 'evan_mcmullin',
        party: PartyType::other,
        startDate: '2016-08-08',
        committeeIds: ['C00623884']
    )]
    public bool $presEvanMcMullin = false;
    #[RecipientAttribute(
        slug: 'george_pataki',
        party: PartyType::republican,
        startDate: '2015-05-28',
        endDate: '2015-12-29',
        committeeIds: ['C00578245', 'C00571356']
    )]
    public bool $presGeorgePataki = false;
    #[RecipientAttribute(
        slug: 'donald_trump',
        party: PartyType::republican,
        startDate: '2015-06-16',
        committeeIds: [
            'C00618371',
            'C00580100',
            'C00618389',
            'C00608489',
            'C00574533',
            'C00618876',
            'C00575373',
            'C00580373',
            'C00589879',
            'C00614370',
            'C00612903',
            'C00586826',
            'C00616789',
            'C00628396',
            'C00591610',
            'C00591610',
            'C00607283',
            'C00587881'
        ]
    )]
    public bool $presDonaldTrump = false;
    #[RecipientAttribute(
        slug: 'hillary_clinton',
        party: PartyType::democrat,
        startDate: '2015-04-12',
        committeeIds: [
            'C00575795',
            'C00586537',
            'C00495861',
            'C00578997',
            'C00540997',
            'C00559765',
            'C00570549',
            'C00605204',
            'C00566034',
            'C00573741'
        ])]
    public bool $presHillaryClinton = false;
    #[RecipientAttribute(
        slug: 'jeb_bush',
        party: PartyType::republican,
        startDate: '2015-06-14',
        endDate: '2016-02-20',
        committeeIds: ['C00579458', 'C00571372', 'C00571950']
    )]
    public bool $presJebBush = false;
    #[RecipientAttribute(
        slug: 'jim_webb',
        party: PartyType::democrat,
        startDate: '2015-07-02',
        endDate: '2015-10-20',
        committeeIds: ['C00581215', 'C00430819']
    )]
    public bool $presJimWebb = false;
    #[RecipientAttribute(
        slug: 'jill_stein',
        party: PartyType::other,
        startDate: '2015-06-22',
        committeeIds: ['C00581199']
    )]
    public bool $presJillStein = false;
    #[RecipientAttribute(
        slug: 'jim_gilmore',
        party: PartyType::republican,
        startDate: '2015-07-29',
        endDate: '2016-02-12',
        committeeIds: ['C00582668', 'C00568840']
    )]
    public bool $presJimGilmore = false;
    #[RecipientAttribute(
        slug: 'john_kasich',
        party: PartyType::republican,
        startDate: '2015-07-21',
        endDate: '2016-05-04',
        committeeIds: ['C00581876', 'C00581868', 'C00582973']
    )]
    public bool $presJohnKasich = false;
    #[RecipientAttribute(
        slug: 'lindsey_graham',
        party: PartyType::republican,
        startDate: '2015-07-21',
        endDate: '2016-05-04',
        committeeIds: ['C00578757', 'C00573733', 'C00388934', 'C00543157']
    )]
    public bool $presLindseyGraham = false;
    #[RecipientAttribute(
        slug: 'marco_rubio',
        party: PartyType::republican,
        startDate: '2015-06-01',
        endDate: '2015-12-21',
        committeeIds: ['C00458844', 'C00541292', 'C00500025', 'C00591214']
    )]
    public bool $presMarcoRubio = false;
    #[RecipientAttribute(party: PartyType::democrat, committeeIds: ['C00578658', 'C00578724', 'C00525220'])]
    public bool $presMartinOMalley = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00577981', 'C00573923', 'C00448373', 'C00579243'])]
    public bool $presMikeHuckabee = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00575449', 'C00525899', 'C00532572', 'C00493924', 'C00572867', 'C00538827', 'C00573410'])]
    public bool $presRandPaul = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00500587', 'C00580092', 'C00573634', 'C00566497'])]
    public bool $presRickPerry = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00496034', 'C00528307', 'C00580324', 'C00582742'])]
    public bool $presRickSantorum = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00574624', 'C00575423', 'C00592337', 'C00575431', 'C00609511', 'C00575415', 'C00536540', 'C00587022', 'C00554725', 'C00570325', 'C00576157'])]
    public bool $presTedCruz = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00580480', 'C00576108', 'C00572792', 'C00573147', 'C00574251'])]
    public bool $presScottWalker = false;
}
