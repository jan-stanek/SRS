<?php

declare(strict_types=1);

namespace App\Model\Program\Events;

use App\Model\Acl\Role;
use App\Model\Program\Category;
use Doctrine\Common\Collections\Collection;

class CategoryUpdatedEvent
{
    private Category $category;

    /** @var Collection<Role> */
    private Collection $registerableRolesOld;

    /**
     * @param Collection<Role> $registerableRolesOld
     */
    public function __construct(Category $category, Collection $registerableRolesOld)
    {
        $this->category             = $category;
        $this->registerableRolesOld = $registerableRolesOld;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return Collection<Role>
     */
    public function getRegisterableRolesOld(): Collection
    {
        return $this->registerableRolesOld;
    }
}
