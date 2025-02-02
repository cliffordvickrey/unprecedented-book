<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile\Cycle;

use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfileAmount;
use CliffordVickrey\Book2024\Common\Enum\PartyType;

class DonorProfileCycle2020 extends DonorProfileCycle
{
    public int $cycle = 2020;
    #[RecipientAttribute(
        slug: 'amy_klobuchar',
        party: PartyType::democratic,
        startDate: '2019-02-10',
        endDate: '2020-03-02',
        committeeIds: ['C00696419', 'C00738831', 'C00431874']
    )]
    public DonorProfileAmount $presAmyKlobuchar;
    #[RecipientAttribute(
        slug: 'andrew_yang',
        party: PartyType::democratic,
        startDate: '2017-11-06',
        endDate: '2020-02-11',
        committeeIds: ['C00659938', 'C00712497', 'C00719708', 'C00730937', 'C00725994', 'C00721050'])
    ]
    public DonorProfileAmount $presAndrewYang;
    #[RecipientAttribute(
        slug: 'bernie_sanders',
        party: PartyType::democratic,
        startDate: '2019-02-19',
        endDate: '2020-04-08',
        committeeIds: ['C00696948', 'C00686477', 'C00406553', 'C00698233', 'C00581967']
    )]
    public DonorProfileAmount $presBernieSanders;
    #[RecipientAttribute(
        slug: 'beto_orourke',
        party: PartyType::democratic,
        startDate: '2019-03-14',
        endDate: '2019-11-01',
        committeeIds: ['C00699090', 'C00571174', 'C00656439']
    )]
    public DonorProfileAmount $presBetoORourke;
    #[RecipientAttribute(
        slug: 'bill_de_blasio',
        party: PartyType::democratic,
        startDate: '2019-05-16',
        endDate: '2019-09-20',
        committeeIds: ['C00706697', 'C00683664', 'C00712539']
    )]
    public DonorProfileAmount $presBillDeBlasio;
    #[RecipientAttribute(
        slug: 'cory_booker',
        party: PartyType::democratic,
        startDate: '2019-02-01',
        endDate: '2020-01-13',
        committeeIds: ['C00695510', 'C00726208', 'C00693028', 'C00497131']
    )]
    public DonorProfileAmount $presCoryBooker;
    #[RecipientAttribute(
        slug: 'deval_patrick',
        party: PartyType::democratic,
        startDate: '2019-11-14',
        endDate: '2020-02-12',
        committeeIds: ['C00727156', 'C00730317', 'C00743252', 'C00686394']
    )]
    public DonorProfileAmount $presDevalPatrick;
    #[RecipientAttribute(
        slug: 'donald_trump',
        party: PartyType::republican,
        startDate: '2017-01-20',
        committeeIds: [
            'C00618371',
            'C00580100',
            'C00618389',
            'C00637512',
            'C00756882',
            'C00762591',
            'C00544767',
            'C00608489',
            'C00532630',
            'C00650168',
            'C00540898',
            'C00688069',
            'C00692129',
            'C00754168',
            'C00618876',
            'C00739151',
            'C00727230',
            'C00505792',
            'C00692467',
            'C00566174',
            'C00756254',
            'C00697862',
            'C00634717',
            'C00683896',
            'C00625574',
            'C00622159',
            'C00748657',
            'C00528893',
            'C00753921',
            'C00759811',
            'C00753673',
            'C00720227',
            'C00688093',
            'C00733899',
            'C00750224',
            'C00753251',
            'C00580373',
            'C00761569',
            'C00682898',
            'C00626689',
            'C00614370',
            'C00575373',
            'C00596973',
        ]
    )]
    public DonorProfileAmount $presDonaldTrump;
    #[RecipientAttribute(
        slug: 'elizabeth_warren',
        party: PartyType::democratic,
        startDate: '2019-02-09',
        endDate: '2020-03-05',
        committeeIds: ['C00693234', 'C00739110', 'C00714436']
    )]
    public DonorProfileAmount $presElizabethWarren;
    #[RecipientAttribute(
        slug: 'eric_swalwell',
        party: PartyType::democratic,
        startDate: '2019-04-08',
        endDate: '2019-07-08',
        committeeIds: ['C00701698', 'C00566059']
    )]
    public DonorProfileAmount $presEricSwalwell;
    #[RecipientAttribute(
        slug: 'howie_hawkins',
        party: PartyType::thirdParty,
        startDate: '2019-05-28',
        committeeIds: ['C00708024']
    )]
    public DonorProfileAmount $presHowieHawkins;
    #[RecipientAttribute(
        slug: 'jay_r_inslee',
        party: PartyType::democratic,
        startDate: '2019-03-01',
        endDate: '2019-08-21',
        committeeIds: ['C00698050', 'C00697300', 'C00688937']
    )]
    public DonorProfileAmount $presJayInslee;
    #[RecipientAttribute(
        slug: 'joe_biden',
        party: PartyType::democratic,
        startDate: '2019-04-25',
        committeeIds: [
            'C00703975',
            'C00669259',
            'C00495861',
            'C00492140',
            'C00532705',
            'C00701888',
            'C00672394',
            'C00646877',
            'C00636027',
            'C00751420',
            'C00744185',
            'C00564013',
            'C00752691',
            'C00760678',
            'C00748798',
            'C00736637',
            'C00748301',
            'C00760116',
            'C00531624',
            'C00624056',
            'C00755942',
            'C00760488',
            'C00639500',
            'C00756973',
            'C00750497',
            'C00759159',
            'C00742775',
            'C00760728',
            'C00688200',
            'C00753293',
            'C00755132',
            'C00743021',
            'C00748160',
            'C00759878',
            'C00721779',
            'C00743625',
            'C00757963',
            'C00757187',
            'C00752089',
            'C00746099',
            'C00679589',
            'C00742932',
            'C00758482',
            'C00740175',
            'C00757997',
            'C00757542',
            'C00758672',
        ]
    )]
    public DonorProfileAmount $presJoeBiden;
    #[RecipientAttribute(
        slug: 'joseph_a_sestak_jr',
        party: PartyType::democratic,
        startDate: '2019-06-23',
        endDate: '2019-12-01',
        committeeIds: ['C00710574', 'C00455741']
    )]
    public DonorProfileAmount $presJoeSestak;
    #[RecipientAttribute(
        slug: 'joe_walsh',
        party: PartyType::republican,
        startDate: '2019-08-25',
        endDate: '2020-02-07',
        committeeIds: ['C00717033']
    )]
    public DonorProfileAmount $preJoeWalsh;
    #[RecipientAttribute(
        slug: 'john_k_delaney',
        party: PartyType::democratic,
        startDate: '2017-07-28',
        endDate: '2020-01-31',
        committeeIds: ['C00508416', 'C00683136']
    )]
    public DonorProfileAmount $presJohnDelaney;
    #[RecipientAttribute(
        slug: 'john_w_hickenlooper',
        party: PartyType::democratic,
        startDate: '2019-03-04',
        endDate: '2019-08-15',
        committeeIds: ['C00698258', 'C00748756', 'C00687582', 'C00701128']
    )]
    public DonorProfileAmount $johnHickenlooper;
    #[RecipientAttribute(
        slug: 'jo_jorgensen',
        party: PartyType::thirdParty,
        startDate: '2019-11-02',
        committeeIds: ['C00718031']
    )]
    public DonorProfileAmount $presJoJorgensen;
    #[RecipientAttribute(
        slug: 'julian_castro',
        party: PartyType::democratic,
        startDate: '2019-01-12',
        endDate: '2020-01-02',
        committeeIds: ['C00693044', 'C00652552']
    )]
    public DonorProfileAmount $presJulianCastro;
    #[RecipientAttribute(
        slug: 'kamala_harris',
        party: PartyType::democratic,
        startDate: '2019-01-21',
        endDate: '2019-12-03',
        committeeIds: ['C00694455', 'C00713099', 'C00629071']
    )]
    public DonorProfileAmount $presKamalaHarris;
    #[RecipientAttribute(
        slug: 'kirsten_gillibrand',
        party: PartyType::democratic,
        startDate: '2019-03-17',
        endDate: '2019-08-28',
        committeeIds: ['C00694018', 'C00525600', 'C00477067']
    )]
    public DonorProfileAmount $presKirstenGillibrand;
    #[RecipientAttribute(
        slug: 'marianne_williamson',
        party: PartyType::democratic,
        startDate: '2019-01-18',
        endDate: '2020-01-10',
        committeeIds: ['C00696054']
    )]
    public DonorProfileAmount $presMarianneWilliamson;
    #[RecipientAttribute(
        slug: 'mark_sanford',
        party: PartyType::republican,
        startDate: '2019-09-08',
        endDate: '2019-11-12',
        committeeIds: ['C00285254', 'C00579516']
    )]
    public DonorProfileAmount $presMarkSanford;
    #[RecipientAttribute(
        slug: 'michael_bennet',
        party: PartyType::democratic,
        startDate: '2019-05-02',
        endDate: '2020-02-11',
        committeeIds: ['C00705186', 'C00491936']
    )]
    public DonorProfileAmount $presMichaelBennet;
    #[RecipientAttribute(
        slug: 'mike_bloomberg',
        party: PartyType::democratic,
        startDate: '2019-11-24',
        endDate: '2020-03-04',
        committeeIds: ['C00728154']
    )]
    public DonorProfileAmount $presMichaelBloomberg;
    #[RecipientAttribute(
        slug: 'mike_gravel',
        party: PartyType::democratic,
        startDate: '2019-04-02',
        endDate: '2019-08-16',
        committeeIds: ['C00700609']
    )]
    public DonorProfileAmount $presMikeGravel;
    #[RecipientAttribute(
        slug: 'pete_buttigieg',
        party: PartyType::democratic,
        startDate: '2019-04-14',
        endDate: '2020-03-01',
        committeeIds: ['C00697441', 'C00648501']
    )]
    public DonorProfileAmount $presPeteButtigieg;
    #[RecipientAttribute(
        slug: 'seth_moulton',
        party: PartyType::democratic,
        startDate: '2019-04-22',
        endDate: '2019-08-23',
        committeeIds: ['C00704510', 'C00571174', 'C00656439']
    )]
    public DonorProfileAmount $presSethMoulton;
    #[RecipientAttribute(
        slug: 'steve_bullock',
        party: PartyType::democratic,
        startDate: '2019-05-14',
        endDate: '2020-12-02',
        committeeIds: ['C00706416', 'C00493262', 'C00744839', 'C00650754', 'C00760959']
    )]
    public DonorProfileAmount $presSteveBullock;
    #[RecipientAttribute(
        slug: 'timothy_j_ryan',
        party: PartyType::democratic,
        startDate: '2019-04-04',
        endDate: '2019-10-24',
        committeeIds: ['C00701979', 'C00417584']
    )]
    public DonorProfileAmount $presTimRyan;
    #[RecipientAttribute(
        slug: 'tom_steyer',
        party: PartyType::democratic,
        startDate: '2019-07-09',
        endDate: '2020-02-29',
        committeeIds: ['C00711614']
    )]
    public DonorProfileAmount $presTomSteyer;
    #[RecipientAttribute(
        slug: 'tulsi_gabbard',
        party: PartyType::democratic,
        startDate: '2019-01-11',
        endDate: '2020-03-19',
        committeeIds: ['C00693713']
    )]
    public DonorProfileAmount $presTulsiGabbard;
    #[RecipientAttribute(
        slug: 'wayne_messam',
        party: PartyType::democratic,
        startDate: '2019-03-28',
        endDate: '2019-11-20',
        committeeIds: ['C00699280']
    )]
    public DonorProfileAmount $presWayneMessam;
    #[RecipientAttribute(
        slug: 'bill_weld',
        party: PartyType::republican,
        startDate: '2019-04-15',
        endDate: '2020-03-18',
        committeeIds: ['C00700906']
    )]
    public DonorProfileAmount $presWilliamWeld;

    protected function getElectionDayStr(): string
    {
        return '2020-11-03';
    }
}
