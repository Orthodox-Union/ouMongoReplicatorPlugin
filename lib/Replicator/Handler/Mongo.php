<?php

class Replicator_Handler_Mongo
{
    protected $_service;
    protected $_database;

    public function __construct($options)
    {
        try {
            $this->_service = new MongoClient(
                $options['connection'],
                $options['options']
            );

            $this->_database = $this->_service->selectDB($options['database']);
            $this->_collection = $this->_database->$options['collection'];
        }
        catch (Exception $e) {
        }
    }

    public function isAvailable()
    {
        return ($this->_service === NULL) ? false : $this->_service->connected;
    }

    public function add(array $document)
    {
        $jobs_hash = $document['jobs_hash'];
        unset($document['jobs_hash']);

        $this->_collection->update(
            array('jobs_hash' => $jobs_hash),
            array(
                '$set' => $document
            ),
            array('upsert' => true)
        );
    }

    public function delete($hash)
    {
        $this->_collection->remove(
            array('jobs_hash' => $hash)
        );
    }
}

