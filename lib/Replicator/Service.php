<?php

class Replicator_Service
{
    private $_handler;

    public function __construct($handler)
    {
        $this->_handler = $handler;
    }

    public function isAvailable()
    {
        return $this->_handler->isAvailable();
    }

    public function add(sfDoctrineRecord $record)
    {
        $this->_handler->add($record->getFieldsAsArray());
    }

    public function delete(sfDoctrineRecord $record)
    {
        $this->_handler->delete($record->getUniqueHash());
    }
}

