<?php


namespace shyevsa\redis;


/**
 *
 */
class SentinelConnection extends \yii\redis\Connection
{
    /**
     * @var string Master Group Name
     */
    public $master_name;

    /**
     * @var array
     */
    private $_master_addr;

    /**
     * @throws \yii\db\Exception
     */
    public function getMaster()
    {
        if (!$this->_master_addr) {
            $this->_master_addr = $this->executeCommand('sentinel', ['get-master-addr-by-name', $this->master_name]);
        }
        return $this->_master_addr;
    }
}