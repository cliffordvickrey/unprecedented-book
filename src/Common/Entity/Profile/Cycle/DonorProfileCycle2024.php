<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile\Cycle;

use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfileAmount;
use CliffordVickrey\Book2024\Common\Enum\PartyType;

class DonorProfileCycle2024 extends DonorProfileCycle
{
    public int $cycle = 2024;
    #[RecipientAttribute(
        slug: 'asa_hutchinson',
        party: PartyType::republican,
        startDate: '2023-04-26',
        endDate: '2024-01-16',
        committeeIds: ['C00798173', 'C00837104']
    )]
    public DonorProfileAmount $presAsaHutchinson;
    #[RecipientAttribute(
        slug: 'chase_russell_oliver',
        party: PartyType::thirdParty,
        startDate: '2023-04-04',
        committeeIds: ['C00837625']
    )]
    public DonorProfileAmount $presChaseOliver;
    #[RecipientAttribute(
        slug: 'chris_christie',
        party: PartyType::republican,
        startDate: '2023-06-06',
        endDate: '2024-01-10',
        committeeIds: ['C00841593', 'C00571778', 'C00842237']
    )]
    public DonorProfileAmount $presChrisChristie;
    #[RecipientAttribute(
        slug: 'cornel_west',
        party: PartyType::thirdParty,
        startDate: '2023-06-05',
        committeeIds: ['C00843508']
    )]
    public DonorProfileAmount $presCornelWest;
    #[RecipientAttribute(
        slug: 'dean_phillips',
        party: PartyType::democratic,
        startDate: '2023-10-27',
        endDate: '2024-03-06',
        committeeIds: ['C00854778', 'C00858779', 'C00857383']
    )]
    public DonorProfileAmount $presDeanPhillips;
    #[RecipientAttribute(
        slug: 'donald_trump',
        party: PartyType::republican,
        startDate: '2022-11-15',
        committeeIds: [
            'C00828541',
            'C00867937',
            'C00873893',
            'C00770941',
            'C00580100',
            'C00618371',
            'C00855114',
            'C00825851',
            'C00879510',
            'C00878801',
            'C00762591',
            'C00867036',
            'C00883520',
            'C00881987',
            'C00891291',
            'C00580100',
            'C00881805',
            'C00888172',
            'C00540898',
            'C00889931',
            'C00494021',
            'C00634261',
            'C00771477',
            'C00887703',
            'C00637512',
            'C00544767',
            'C00750224',
            'C00887729',
            'C00692467',
            'C00880815',
            'C00886515',
            'C00886317',
            'C00883561',
            'C00885855',
            'C00608489',
            'C00887745',
            'C00881631',
            'C00875963',
            'C00885665',
            'C00621755',
            'C00790477',
        ]
    )]
    public DonorProfileAmount $presDonaldTrump;
    #[RecipientAttribute(
        slug: 'doug_burgum',
        party: PartyType::republican,
        startDate: '2023-06-07',
        endDate: '2023-12-04',
        committeeIds: ['C00842344', 'C00842302']
    )]
    public DonorProfileAmount $presDougBurgum;
    #[RecipientAttribute(
        slug: 'francis_x_suarez',
        party: PartyType::republican,
        startDate: '2023-06-14',
        endDate: '2023-08-29',
        committeeIds: ['C00842971', 'C00801803']
    )]
    public DonorProfileAmount $presFrancisSuarez;
    #[RecipientAttribute(
        slug: 'jill_stein',
        party: PartyType::thirdParty,
        startDate: '2023-11-09',
        committeeIds: ['C00856112']
    )]
    public DonorProfileAmount $presJillStein;
    #[RecipientAttribute(
        slug: 'joe_biden',
        party: PartyType::democratic,
        startDate: '2023-04-25',
        endDate: '2024-07-20',
        committeeIds: [
            'C00703975',
            'C00744946',
            'C00838912',
            'C00857177',
            'C00845776',
            'C00778381',
            'C00669259',
            'C00492140',
            'C00631549',
            'C00725820',
            'C00753558',
            'C00495861',
            'C00885947',
            'C00752048',
            'C00547349',
            'C00827253',
            'C00701888',
            'C00883827',
            'C00875815',
            'C00819631',
            'C00844282',
            'C00874115',
            'C00882381',
            'C00887182',
            'C00718353',
            'C00885137',
            'C00812495',
            'C00886093',
            'C00695528',
            'C00887968',
            'C00879973',
            'C00882233',
            'C00853713',
            'C00825737',
            'C00890392',
            'C00887414',
            'C00891259',
            'C00874750',
            'C00531624',
            'C00842575',
            'C00800995',
            'C00884239',
            'C00703082',
            'C00736637',
            'C00739557',
            'C00891325',
            'C00882019',
            'C00884817',
            'C00877027',
            'C00887992',
            'C00752089',
            'C00827030',
            'C00889279',
            'C00877886',
            'C00891937',
            'C00876854',
            'C00891044',
            'C00875443',
            'C00528448',
            'C00891028',
            'C00887208',
            'C00891382',
            'C00889907',
            'C00881573',
        ]
    )]
    public DonorProfileAmount $presJoeBiden;
    #[RecipientAttribute(
        slug: 'kamala_harris',
        party: PartyType::democratic,
        startDate: '2024-07-21',
        committeeIds: [
            'C00703975',
            'C00744946',
            'C00838912',
            'C00669259',
            'C00492140',
            'C00631549',
            'C00725820',
            'C00753558',
            'C00495861',
            'C00885947',
            'C00752048',
            'C00547349',
            'C00827253',
            'C00701888',
            'C00883827',
            'C00875815',
            'C00819631',
            'C00844282',
            'C00874115',
            'C00882381',
            'C00887182',
            'C00718353',
            'C00885137',
            'C00812495',
            'C00886093',
            'C00695528',
            'C00887968',
            'C00879973',
            'C00882233',
            'C00853713',
            'C00825737',
            'C00890392',
            'C00887414',
            'C00891259',
            'C00874750',
            'C00531624',
            'C00842575',
            'C00800995',
            'C00884239',
            'C00703082',
            'C00736637',
            'C00739557',
            'C00891325',
            'C00882019',
            'C00884817',
            'C00877027',
            'C00887992',
            'C00752089',
            'C00827030',
            'C00889279',
            'C00877886',
            'C00891937',
            'C00876854',
            'C00891044',
            'C00875443',
            'C00528448',
            'C00891028',
            'C00887208',
            'C00891382',
            'C00889907',
            'C00881573',
        ]
    )]
    public DonorProfileAmount $presKamalaHarris;
    #[RecipientAttribute(
        slug: 'marianne_williamson',
        party: PartyType::democratic,
        startDate: '2023-03-04',
        endDate: '2024-07-29',
        committeeIds: ['C00834424', 'C00867770']
    )]
    public DonorProfileAmount $presMarianneWilliamson;
    #[RecipientAttribute(
        slug: 'mike_pence',
        party: PartyType::republican,
        startDate: '2023-06-05',
        endDate: '2024-10-28',
        committeeIds: ['C00842039', 'C00839464', 'C00640664']
    )]
    public DonorProfileAmount $presMikePence;
    #[RecipientAttribute(
        slug: 'nikki_haley',
        party: PartyType::republican,
        startDate: '2023-02-14',
        endDate: '2024-03-06',
        committeeIds: ['C00833392', 'C00828061', 'C00765982', 'C00858381']
    )]
    public DonorProfileAmount $presNikkiHaley;
    #[RecipientAttribute(
        slug: 'rfk_jr',
        party: PartyType::thirdParty,
        startDate: '2023-04-19',
        endDate: '2024-08-23',
        committeeIds: [
            'C00836916',
            'C00821439',
            'C00838805',
            'C00840660',
            'C00843938',
            'C00851451',
            'C00858670',
            'C00870410',
        ],
        description: 'RFK JR'
    )]
    public DonorProfileAmount $presRfkJr;
    #[RecipientAttribute(
        slug: 'ron_desantis',
        party: PartyType::republican,
        startDate: '2023-05-24',
        endDate: '2024-01-21',
        committeeIds: [
            'C00841130',
            'C00834077',
            'C00834853',
            'C00857011',
            'C00841148',
            'C00815928',
            'C00857425',
            'C00836395',
            'C00841734',
            'C00828400',
        ]
    )]
    public DonorProfileAmount $presRonDeSantis;
    #[RecipientAttribute(
        slug: 'tim_scott',
        party: PartyType::republican,
        startDate: '2023-05-22',
        endDate: '2023-11-12',
        committeeIds: ['C00540302', 'C00840546', 'C00825158', 'C00827519', 'C00750182']
    )]
    public DonorProfileAmount $presTimScott;
    #[RecipientAttribute(
        slug: 'vivek_ramaswamy',
        party: PartyType::republican,
        startDate: '2023-02-21',
        endDate: '2024-01-15',
        committeeIds: ['C00833913', 'C00833749']
    )]
    public DonorProfileAmount $presVivekRamaswamy;
    #[RecipientAttribute(
        slug: 'will_hurd',
        party: PartyType::republican,
        startDate: '2023-06-22',
        endDate: '2023-10-09',
        committeeIds: ['C00843540', 'C00693531']
    )]
    public DonorProfileAmount $presWillHurd;

    protected function getElectionDayStr(): string
    {
        return '2024-11-05';
    }
}
