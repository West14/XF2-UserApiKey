<?php

namespace West\UserApiKey;

use XF;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
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
            $table->addColumn('disable_auto_check', 'tinyint')->setDefault(0);
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
            $table->addColumn('html', 'text')->nullable();
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
        $this->alterTable('xf_wuak_user_store', function (Alter $table)
        {
            $table->addColumn('webhook_url', 'varchar', 512)->nullable();
            $table->addColumn('webhook_secret', 'varchar', 64)->nullable();

        });
    }

    public function upgrade1000014Step1()
    {
        $this->alterTable('xf_wuak_store_check_log', function (Alter $table)
        {
            $table->addColumn('html', 'text')->nullable();
        });
    }

    public function upgrade1000015Step1()
    {
        $this->alterTable('xf_wuak_user_store', function (Alter $table)
        {
            $table->addUniqueKey('store_url');
        });
    }

    public function upgrade1000017Step1()
    {
        $this->alterTable('xf_wuak_user_store', function (Alter $table)
        {
            $table
                ->addColumn('disable_auto_check', 'tinyint')
                ->setDefault(0)
                ->after('webhook_secret');
        });
    }

    public function uninstallStep1()
    {
        $keyIdList = $this->app()->finder('West\UserApiKey:UserApiKey')
            ->fetchColumns('api_key_id');

        $keyIdList = array_map(function ($x) {
            return $x['api_key_id'];
        }, $keyIdList);

        $keyIds = implode(',', $keyIdList);
        XF::db()->delete('xf_api_key', "api_key_id IN ($keyIds)");
    }

    public function uninstallStep2()
    {
        $this->dropTable('xf_wuak_user_api_key');
        $this->dropTable('xf_wuak_user_store');
        $this->dropTable('xf_wuak_store_check_log');
    }
}