<?php

namespace West\UserApiKey;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

    public function installStep1()
    {
        $this->createTable('xf_wuak_user_api_key', function (\XF\Db\Schema\Create $table)
        {
            $table->addColumn('user_id', 'int');
            $table->addColumn('api_key_id', 'int');
            $table->addColumn('expires_at', 'int');
            $table->addPrimaryKey(['user_id', 'api_key_id']);
        });
    }

    public function uninstallStep2()
    {
        $this->dropTable('xf_wuak_user_api_key');
    }
}