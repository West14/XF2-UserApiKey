<?php

namespace West\UserApiKey;

use XF\Mvc\Entity\Entity;

class Listener
{
    public static function apiKeyEntityStructure(\XF\Mvc\Entity\Manager $em, \XF\Mvc\Entity\Structure &$structure)
    {
        $structure->relations += [
            'UserApiKey' => [
                'entity' => 'West\UserApiKey:UserApiKey',
                'type' => Entity::TO_ONE,
                'conditions' => 'api_key_id',
                'cascadeDelete' => true
            ]
        ];
    }
}