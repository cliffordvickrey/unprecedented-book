<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile;

use CliffordVickrey\Book2024\Common\Enum\PartyType;

class DonorProfileCycle2020 extends DonorProfileCycle
{
    #[RecipientAttribute(PartyType::democrat, ['C00695510', 'C00726208', 'C00693028', 'C00497131'])]
    public bool $presCoryBooker = false;
    #[RecipientAttribute(PartyType::republican, ['C00618371', 'C00580100', 'C00618389', 'C00637512', 'C00756882', 'C00762591', 'C00544767', 'C00608489', 'C00532630', 'C00650168', 'C00540898', 'C00688069', 'C00692129', 'C00754168', 'C00618876', 'C00739151', 'C00727230', 'C00505792', 'C00692467', 'C00566174', 'C00756254', 'C00697862', 'C00634717', 'C00683896', 'C00625574', 'C00622159', 'C00748657', 'C00528893', 'C00753921', 'C00759811', 'C00753673', 'C00720227', 'C00688093', 'C00733899', 'C00750224', 'C00753251', 'C00580373', 'C00761569', 'C00682898', 'C00626689', 'C00614370', 'C00575373', 'C00596973'])]
    public bool $presDonaldTrump = false;
    #[RecipientAttribute(PartyType::democrat, ['C00703975', 'C00669259', 'C00495861', 'C00492140', 'C00532705', 'C00701888', 'C00672394', 'C00646877', 'C00636027', 'C00751420', 'C00744185', 'C00564013', 'C00752691', 'C00760678', 'C00748798', 'C00736637', 'C00748301', 'C00760116', 'C00531624', 'C00624056', 'C00755942', 'C00760488', 'C00639500', 'C00756973', 'C00750497', 'C00759159', 'C00742775', 'C00760728', 'C00688200', 'C00753293', 'C00755132', 'C00743021', 'C00748160', 'C00759878', 'C00721779', 'C00743625', 'C00757963', 'C00757187', 'C00752089', 'C00746099', 'C00679589', 'C00742932', 'C00758482', 'C00740175', 'C00757997', 'C00757542', 'C00758672'])]
    public bool $presJoeBiden = false;
    #[RecipientAttribute(PartyType::democrat, ['C00508416', 'C00683136'])]
    public bool $presJohnDelaney = false;
    #[RecipientAttribute(PartyType::other, ['C00718031'])]
    public bool $presJoJorgensen = false;
    #[RecipientAttribute(PartyType::democrat, ['C00693044', 'C00652552'])]
    public bool $presJulianCastro = false;
    #[RecipientAttribute(PartyType::democrat, ['C00705186', 'C00491936'])]
    public bool $presMichaelBennet  = false;
    #[RecipientAttribute(PartyType::democrat, ['C00728154'])]
    public bool $presMichaelBloomberg = false;
    #[RecipientAttribute(PartyType::democrat, ['C00697441', 'C00648501'])]
    public bool $presPeteButtigieg = false;
    #[RecipientAttribute(PartyType::democrat, ['C00706416', 'C00493262', 'C00744839', 'C00650754', 'C00760959'])]
    public bool $presSteveBullock = false;
}