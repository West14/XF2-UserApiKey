<?php

namespace West\UserApiKey\Option;

use XF\Entity\Option;
use XF\Option\AbstractOption;

class AllowedScopes extends AbstractOption
{
    public static function renderOption(\XF\Entity\Option $option, array $htmlParams)
    {
        /** @var \XF\Repository\Api $apiRepo */
        $apiRepo = \XF::repository('XF:Api');
        $scopes = $apiRepo->findApiScopesForList()->fetch();

        return self::getTemplate('admin:option_template_wuakAllowedScopes', $option, $htmlParams, [
            'scopes' => $scopes
        ]);
    }

    public static function verifyOption(&$value, Option $option)
    {
        $value['scopes'] = $value['scopes'] ?? [];

        $value['scopes'] = array_fill_keys($value['scopes'], true);
        if ($value['allow_all_scopes'])
        {
            $value['scopes'] = [];
        }

        return true;
    }
}