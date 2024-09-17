<?php


namespace shyevsa\redis;


use Yii;
use yii\db\Exception;

/**
 *
 *
 * @property-read array $master
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
     * @inheritdoc
     */
    public $port = 26379;

    /**
     * @inheritdoc
     */
    public $database = null;

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

    /**
     * @inheritdoc
     */
    public function close()
    {
        try {
            parent::close();
        } catch (Exception $e) {
            $connection = $this->connectionString . ', database=' . $this->database;
            Yii::debug([
                'message' => $e->getMessage(),
                'connection' => $connection,
                'note' => 'Ignore Error on Closing'
            ], __METHOD__);
        }
    }
}
