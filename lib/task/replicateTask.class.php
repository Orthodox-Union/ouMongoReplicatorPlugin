<?php

class replicateTask extends sfBaseTask
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
        $this->name = 'replicate';
        $this->briefDescription = 'Replicates data to Mongo server.';
        $this->detailedDescription = <<<EOF
The [repl:replicate|INFO] task replicates data to Mongo server.
Call it with:

[php symfony repl:replicate|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $sfContext = sfContext::createInstance($this->configuration);

        $this->logSection('repl:replicate', 'Starting repl:replicate');
        $this->logSection('repl:replicate', 'Connecting to ' .  Doctrine_Core::getTable('Job')->getReplicationConnection());

        if (!Doctrine_Core::getTable('Job')->isReplicationAvailable()) {
            throw new RuntimeException('Replication is unavailable. Make sure you can connect to Mongo.');
        }

        $jobs = Doctrine_Core::getTable('Job')->createApprovedJobsQuery()->execute();
        foreach ($jobs as $job) {
            $job->addToCollection();
            $this->logSection('repl:replicate', $job->getId());
        }

        $this->logSection('repl:replicate', 'Done');
    }
}

