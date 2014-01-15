<?php

/**
 * Mongo template
 *
 * @package     ouMongoReplicatorPlugin
 * @subpackage  Template
 * @author      Ilan Cohen <ilanco@gmail.com>
 */
class Doctrine_Template_Mongo extends Doctrine_Template
{
    protected $_options = array(
        'connection' => 'mongodb://localhost:27017',
        'database' => '',
        'collection' => '',
        'options' => array(
            'replicaSet' => '',
            'readPreference' => MongoClient::RP_SECONDARY_PREFERRED
        ),
        'key' => 'jobs_hash',
        'fields' => array(),
        'fieldmap' => array(),
        'realtime' => true,
    );

    /**
     * @var Search_Service $_search This is the way to handle ElasticSearch communication
     */
    private $_replicator;

    public function setTableDefinition()
    {
        // Don't setup listener if realtime option is false
        if ($this->_options['realtime']) {
            $this->addListener(new Doctrine_Template_Listener_Mongo($this->_options));
        }
    }

    public function setUp()
    {
        $table = get_class($this->getInvoker());

        try {
            if (class_exists('sfConfig', false)) {
                $mongo = sfConfig::get('app_mongo_replicator', false);

                foreach (array('connection', 'database', 'collection', 'options') as $param) {
                    if (!empty($mongo[$param])) {
                        if (is_array($mongo[$param])) {
                            $this->_options[$param] = array_merge($this->_options[$param], $mongo[$param]);
                        }
                        else {
                            $this->_options[$param] = $mongo[$param];
                        }
                    }
                }
            }
        }
        catch (Exception $e) {
            if (class_exists('sfContext', false) && sfContext::hasInstance()) {
                sfContext::getInstance()->getLogger()->crit('{Doctrine_Template_Mongo::setUp} Error while setting up mongo: '.$e->getMessage());
            }
        }

        $mongoHandler = new Replicator_Handler_Mongo($this->_options);
        $this->_replicator = new Replicator_Service($mongoHandler);
    }

    public function isReplicationAvailableTableProxy()
    {
        return $this->_replicator->isAvailable();
    }

    public function getReplicationConnectionTableProxy()
    {
        return $this->_options['connection'];
    }

    public function getUniqueId()
    {
        return sha1(sprintf('%s_%s', get_class($this->getInvoker()), $this->getInvoker()->getId()));
    }

    public function addToCollection()
    {
        $this->_replicator->add($this->getInvoker());
    }

    public function deleteFromCollection()
    {
        $this->_search->delete($this->getInvoker());
    }

    public function getFieldsAsArray()
    {
        $document = array();
        $invoker = $this->getInvoker();

        $document[$this->_options['key']] = $this->getUniqueId();

        $document['sf_meta_class'] = get_class($invoker);
        $document['sf_meta_id'] = $invoker->getId();

        $fields = $this->_options['fields'];
        $map = $this->_options['fieldmap'];
        foreach ($fields as $field) {
            $fieldName = array_key_exists($field, $map) ? $map[$field] : $field;
            $value = $invoker->get($field);

            $document[$fieldName] = $value;
        }

        return $document;
    }
}

