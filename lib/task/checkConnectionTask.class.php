<?php

class checkConnectionTask extends sfBaseTask
{
    protected function configure()
    {
        // // add your own arguments here
        // $this->addArguments(array(
        //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
        // ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
            // add your own options here
        ));

        $this->namespace = 'repl';
        $this->name = 'check-connection';
        $this->briefDescription = 'Checks connection to Mongo server.';
        $this->detailedDescription = <<<EOF
The [repl:check-connection|INFO] task checks connection to Mongo server.
Call it with:

[php symfony repl:check-connection|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $sfContext = sfContext::createInstance($this->configuration);

        $this->logSection('repl:check-connection', 'Starting repl:check-connection');

        if (!Doctrine_Core::getTable('Job')->isReplicationAvailable()) {
            throw new RuntimeException('Replication is unavailable. Make sure you can connect to Mongo.');
        }

        $this->logSection('repl:check-connection', 'Replication is available');

        $this->logSection('repl:check-connection', 'Done');
    }
}

