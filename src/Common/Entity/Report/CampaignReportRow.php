<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

class CampaignReportRow extends AbstractReportRow
{
    public \DateTimeImmutable $date;
    public ReportValue $value;
}
