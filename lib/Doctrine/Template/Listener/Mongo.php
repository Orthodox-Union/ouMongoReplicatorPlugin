<?php

/**
 * Update Mongo index when object is created / updated / deleted
 *
 * @package     ouMongoReplicatorPlugin
 * @subpackage  Template
 * @author      Ilan Cohen <ilanco@gmail.com>
 */
class Doctrine_Template_Listener_Mongo extends Doctrine_Record_Listener
{
    protected $_options;

    public function __construct(array $options)
    {
        $this->_options = $options;
    }

    public function postInsert(Doctrine_Event $event)
    {
        $this->updateCollection($event);
    }

    public function postUpdate(Doctrine_Event $event)
    {
        $this->updateCollection($event);
    }

    public function preDelete(Doctrine_Event $event)
    {
        try {
            $invoker = $event->getInvoker();
            $invoker->deleteFromCollection();
        }
        catch (Exception $e) {
            $this->notifyException($e, $invoker);
        }
    }

    protected function updateCollection($event)
    {
        try {
            $invoker = $event->getInvoker();
            $invoker->addToCollection();
        }
        catch (Exception $e) {
            $this->notifyException($e, $invoker);
        }
    }

    private function notifyException(Exception $e, sfDoctrineRecord $record)
    {
        if (sfContext::hasInstance()) {
            $event = sfContext::getInstance()->getEventDispatcher()->notifyUntil(new sfEvent($record, 'mongo.replication_error', array('exception' => $e)));

            if ($event->isProcessed()) {
                return;
            }
            throw $e;
        }
    }
}

