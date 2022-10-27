<?php

declare(strict_types=1);

namespace App\Model\User\Queries;

class PatrolByTroopAndNotConfirmedQuery
{
    public function __construct(private int $troopId)
    {
    }

    public function getTroopId(): int
    {
        return $this->troopId;
    }
}
