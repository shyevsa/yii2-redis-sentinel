<?php


namespace shyevsa\redis;


use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\di\Instance;

class Connection extends \yii\redis\Connection
{
    /**
     * List of Sentinel hostname or `\yii\redis\Connection` array for sentinel connection
     * @var array ['host1','host2', ['hostname'=>'host3', 'port'=>26379, 'password'=>'abcdef']]
     */
    public $sentinels = null;

    /**
     * @var string Master Name
     */
    public $master_name = null;

    /**
     * @var SentinelConnection
     */
    private $_sentinel;

    /**
     * @var array
     */
    public $failed_sentinel = [];

    /**
     * @inheritdoc
     */
    public function open()
    {
        if ($this->socket !== false) {
            return;
        }

        if (!$this->sentinels || !$this->master_name) {
            throw new Exception('`sentinels` and `master_name` must be set');
        }

        Yii::beginProfile("Get Master {$this->master_name}", __METHOD__);
        [$this->hostname, $this->port] = $this->discoverMaster();
        Yii::endProfile("Get Master {$this->master_name}", __METHOD__);
        parent::open();
    }

    /**
     * @inheritdoc
     */
    public function __sleep()
    {
        try {
            if ($this->_sentinel !== null) {
                $this->_sentinel->close();
            }
            $this->_sentinel = null;
            $this->close();
        } catch (Throwable $e) {
            Yii::debug($e->getMessage(), __METHOD__);
        }

        return array_keys(get_object_vars($this));
    }

    /**
     * Search Master from list of Sentinel
     * @return array|bool|string|null
     * @throws Exception
     */
    public function discoverMaster()
    {
        if ($this->_sentinel !== null) {
            try {
                return $this->_sentinel->getMaster();
            } catch (Throwable $e) {
                Yii::error($e->getMessage(), __METHOD__);
            }
        }

        foreach ($this->sentinels as $sentinel) {
            if (is_scalar($sentinel)) {
                $sentinel = [
                    'hostname' => $sentinel
                ];
            }

            if (in_array(hash('sha256', serialize($sentinel)), $this->failed_sentinel, true)) {
                continue;
            }

            try {
                $this->_sentinel = Instance::ensure($sentinel, SentinelConnection::class);
                $this->_sentinel->master_name = $this->master_name;
                $r = $this->_sentinel->getMaster();
                if ($r) {
                    Yii::info("sentinel @ {$this->_sentinel->connectionString} gave master {$r[0]}:{$r[1]}",
                        __METHOD__);
                    return $r;
                }

                Yii::info("No Master Found from @ {$this->_sentinel->connectionString}", __METHOD__);
            } catch (InvalidConfigException | Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $this->failed_sentinel[] = hash('sha256', serialize($sentinel));
            }
        }

        throw new Exception('Could not Find Any Master');
    }

}
