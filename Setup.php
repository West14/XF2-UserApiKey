<?php

namespace West\UserApiKey;

use XF;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

    public function installStep1()
    {
        $this->createTable('xf_wuak_user_api_key', function (Create $table)
        {
            $table->addColumn('user_id', 'int');
            $table->addColumn('api_key_id', 'int');
            $table->addColumn('expires_at', 'int');
            $table->addColumn('seen', 'tinyint')->setDefault(0);
            $table->addPrimaryKey(['user_id', 'api_key_id']);
        });

        $this->createTable('xf_wuak_user_store', function (Create $table)
        {
            $table->addColumn('user_id', 'int');
            $table->addColumn('store_url', 'varchar', 128);
            $table->addColumn('webhook_url', 'varchar', 512)->nullable();
            $table->addColumn('webhook_secret', 'varchar', 64)->nullable();
            $table->addColumn('status', 'enum')->values(['valid', 'missing_link', 'validating', 'error']);
            $table->addColumn('error_code', 'varchar', 64)->nullable();
            $table->addColumn('error_retry_count', 'int')->setDefault(3);
            $table->addColumn('active', 'tinyint')->setDefault(1);
            $table->addColumn('checked_at', 'int')->setDefault(0);
            $table->addPrimaryKey('user_id');
        });

        $this->createTable('xf_wuak_store_check_log', function (Create $table)
        {
            $table->addColumn('log_id', 'int')->autoIncrement();
            $table->addColumn('user_id', 'int');
            $table->addColumn('store_url', 'varchar', 128);
            $table->addColumn('status', 'enum')->values(['valid', 'missing_link', 'validating', 'error']);
            $table->addColumn('error_code', 'varchar', 64)->nullable();
            $table->addColumn('log_date', 'int')->setDefault(0);
        });
    }

    public function upgrade1000011Step1()
    {
        $this->createTable('xf_wuak_store_check_log', function (Create $table)
        {
            $table->addColumn('log_id', 'int')->autoIncrement();
            $table->addColumn('user_id', 'int');
            $table->addColumn('store_url', 'varchar', 128);
            $table->addColumn('status', 'enum')->values(['valid', 'missing_link', 'validating', 'error']);
            $table->addColumn('error_code', 'varchar', 64)->nullable();
            $table->addColumn('log_date', 'int')->setDefault(0);
        });
    }

    public function upgrade1000012Step1()
    {
        $this->alterTable('xf_wuak_user_store', function (XF\Db\Schema\Alter $table)
        {
            $table->addColumn('webhook_url', 'varchar', 512)->nullable();
            $table->addColumn('webhook_secret', 'varchar', 64)->nullable();

        });
    }

    public function uninstallStep1()
    {
        $keyIds = $this->app()->finder('West\UserApiKey:UserApiKey')
            ->fetchColumns('api_key_id');

        $keyIds = array_map(function ($x) {
            return $x['api_key_id'];
        }, $keyIds);

        XF::db()->delete('xf_api_key', 'api_key_id IN (?)', implode(',', $keyIds));
    }

    public function uninstallStep2()
    {
        $this->dropTable('xf_wuak_user_api_key');
        $this->dropTable('xf_wuak_user_store');
        $this->dropTable('xf_wuak_store_check_log');
    }
}