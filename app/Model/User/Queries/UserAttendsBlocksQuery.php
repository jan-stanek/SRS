<?php

declare(strict_types=1);

namespace App\Model\User\Queries;

use App\Model\User\User;

class UserAttendsBlocksQuery
{
    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
