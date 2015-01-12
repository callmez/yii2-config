<?php
namespace callmez\config\adapters;

use Yii;
use yii\db\Query;
use yii\di\Instance;
use yii\db\Connection;
use yii\base\InvalidConfigException;
use callmez\config\BaseConfig;

/**
 * DbConfig represents an config that stores config information in database.
 * @package app\components
 */
class DbConfig extends BaseConfig
{

    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     */
    public $db = 'db';
    /**
     * 数据表存储类
     * @var string
     */
    public $configTable = '{{%config}}';

    public function init()
    {
        $this->db = Instance::ensure($this->db, Connection::className());
        parent::init();
    }

    /**
     * Returns the config data from config table.
     *
     * @return array|\yii\db\ActiveRecord[]
     * @throws \yii\base\InvalidConfigException
     */
    public function getData()
    {
        $query = (new Query)->from($this->configTable);
        $data = [];
        foreach ($query->all($this->db) as $row) {
            $data[$row['name']] = unserialize($row['value']);
        }
        return $data;
    }

    /**
     * Save updated data to config table.
     */
    public function saveData()
    {
        if ($this->data == $this->oldData) {
            return ;
        }

        $newData = $updatedData = $deletedKeys = [];
        foreach ($this->data as $name => $value) {
            if (!array_key_exists($name, $this->oldData)) {
                $newData[$name] = [$name, serialize($value)];
            } elseif ($value != $this->oldData[$name]) {
                $updatedData[$name] = [$name, serialize($value)];
            }
        }
        foreach ($this->oldData as $name => $value) {
            if (!array_key_exists($name, $this->data)) {
                $deletedKeys[] = $name;
            }
        }

        if ($this->db->driverName === 'mysql') { // user replace into if mysql database
            if (($replaceData = array_merge($updatedData, $newData)) !== []) {
                $sql = $this->db->queryBuilder->batchInsert($this->configTable, ['name', 'value'], $replaceData);
                $sql = 'REPLACE INTO' . substr($sql, 11); // Replace 'INSERT INTO' to 'REPLACE INTO'
                $this->db->createCommand($sql)->execute();
            }
        } else {
            if (!empty($newData)) {
                $this->db->createCommand()
                    ->batchInsert($this->configTable, ['name', 'value'], $newData)
                    ->execute();
            }
            if (!empty($updatedData)) {
                foreach($updatedData as $name => $value) {
                    $this->db->createCommand()
                        ->update($this->configTable, ['value' => $value[1]], ['name' => $name])
                        ->execute();
                }
            }
        }
        if (!empty($deletedKeys)) { // delete deleted data
            $this->db->createCommand()
                ->delete($this->configTable, ['name' => $deletedKeys])
                ->execute();
        }

        $this->oldData = $this->data;
    }


    /**
     * Auto save data.
     */
    public function __destruct()
    {
        $this->saveData();
    }
}