<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile;

use CliffordVickrey\Book2024\Common\Enum\PartyType;

class DonorProfileCycle2024 extends DonorProfileCycle
{
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00798173', 'C00837104'])]
    public bool $presAsaHutchinson = false;
    #[RecipientAttribute(party: PartyType::other, committeeIds: ['C00837625'])]
    public bool $presChaseOliver = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00841593', 'C00571778', 'C00842237'])]
    public bool $presChrisChristie = false;
    #[RecipientAttribute(party: PartyType::other, committeeIds: ['C00843508'])]
    public bool $presCornelWest = false;
    #[RecipientAttribute(party: PartyType::democrat, committeeIds: ['C00854778', 'C00858779', 'C00857383'])]
    public bool $presDeanPhillips = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00828541', 'C00867937', 'C00873893', 'C00770941', 'C00580100', 'C00618371', 'C00855114', 'C00825851', 'C00879510', 'C00878801', 'C00762591', 'C00867036', 'C00883520', 'C00881987', 'C00891291', 'C00580100', 'C00881805', 'C00888172', 'C00540898', 'C00889931', 'C00494021', 'C00634261', 'C00771477', 'C00887703', 'C00637512', 'C00544767', 'C00750224', 'C00887729', 'C00692467', 'C00880815', 'C00886515', 'C00886317', 'C00883561', 'C00885855', 'C00608489', 'C00887745', 'C00881631', 'C00875963', 'C00885665', 'C00621755', 'C00790477'])]
    public bool $presDonaldTrump = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00842344', 'C00842302'])]
    public bool $presDougBurgum = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00842971', 'C00801803'])]
    public bool $presFrancisSuarez = false;
    #[RecipientAttribute(party: PartyType::other, committeeIds: ['C00856112'])]
    public bool $presJillStein = false;
    #[RecipientAttribute(party: PartyType::democrat, committeeIds: ['C00703975', 'C00744946', 'C00838912', 'C00857177', 'C00845776', 'C00778381'])]
    public bool $presJoeBiden = false;
    #[RecipientAttribute(party: PartyType::democrat, committeeIds: ['C00703975', 'C00744946', 'C00838912', 'C00669259', 'C00492140', 'C00631549', 'C00725820', 'C00753558', 'C00495861', 'C00885947', 'C00752048', 'C00547349', 'C00827253', 'C00701888', 'C00883827', 'C00875815', 'C00819631', 'C00844282', 'C00874115', 'C00882381', 'C00887182', 'C00718353', 'C00885137', 'C00812495', 'C00886093', 'C00695528', 'C00887968', 'C00879973', 'C00882233', 'C00853713', 'C00825737', 'C00890392', 'C00887414', 'C00891259', 'C00874750', 'C00531624', 'C00842575', 'C00800995', 'C00884239', 'C00703082', 'C00736637', 'C00739557', 'C00891325', 'C00882019', 'C00884817', 'C00877027', 'C00887992', 'C00752089', 'C00827030', 'C00889279', 'C00877886', 'C00891937', 'C00876854', 'C00891044', 'C00875443', 'C00528448', 'C00891028', 'C00887208', 'C00891382', 'C00889907', 'C00881573'])]
    public bool $presKamalaHarris = false;
    #[RecipientAttribute(party: PartyType::democrat, committeeIds: ['C00834424', 'C00867770'])]
    public bool $presMarianneWilliamson = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00842039', 'C00839464', 'C00640664'])]
    public bool $presMikePence = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00833392', 'C00828061', 'C00765982', 'C00858381'])]
    public bool $presNikkiHaley = false;
    #[RecipientAttribute(party: PartyType::other, committeeIds: ['C00836916', 'C00821439', 'C00838805', 'C00840660', 'C00843938', 'C00851451', 'C00858670', 'C00870410'])]
    public bool $presRfkJr = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00834077', 'C00834853', 'C00857011', 'C00841148', 'C00815928', 'C00857425', 'C00836395', 'C00841734', 'C00828400'])]
    public bool $presRonDeSantis = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00540302', 'C00840546', 'C00825158', 'C00827519', 'C00750182'])]
    public bool $presTimScott = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00833913', 'C00833749'])]
    public bool $presVivekRamaswamy = false;
    #[RecipientAttribute(party: PartyType::republican, committeeIds: ['C00843540', 'C00693531'])]
    public bool $presWillHurd = false;
}
