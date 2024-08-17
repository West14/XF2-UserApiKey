<?php

namespace West\UserApiKey\Repository;

use XF\Mvc\Entity\Repository;

class UserStore extends Repository
{
    public function findUserStoresForList()
    {
        return $this->finder('West\UserApiKey:UserStore')
            ->with('User');
    }
}