<?php
namespace callmez\config;

use Yii;
use yii\base\Component;
use yii\base\ArrayAccessTrait;
use yii\base\InvalidParamException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * BaseConfig is the base class for config component
 * @package app\components
 */
abstract class BaseConfig extends Component implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use ArrayAccessTrait;
    /**
     * @var array the data rows. Each array element represents one row of data (column name => column value).
     */
    protected $data;
    /**
     * @var array old data rows.
     */
    protected $oldData;

    /**
     * Loads the data.
     */
    public function init()
    {
        $this->load();
        parent::init();
    }

    /**
     * Loads the config data.
     */
    public function load()
    {
        $this->oldData = $this->data = $this->getData();
    }

    /**
     * Get data from `$this->data` with key that using "dot" notation
     *
     * For exapmle:
     * ~~~
     * Yii::$app->config->get('foo.bar', 'test');
     * ~~~
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return $this->deepGet($key, $default);
    }

    /**
     * Set data to a given value with key that using "dot" notation.
     *
     * For exapmle:
     * ~~~
     * Yii::$app->config->set('foo.bar', 'test');
     * ~~~
     *
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                $this->deepSet($innerKey, $innerValue);
            }
        } else {
            $this->deepSet($key, $value);
        }
    }

    /**
     * delete data with key that using "dot" notation.
     *
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        return $this->deepDelete($key);
    }

    /**
     * Get data from `$this->data` with key that using "dot" notation
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    protected function deepGet($key, $default = null)
    {
        if (is_null($key)) {
            return $this->data;
        } elseif (isset($this->data[$key])) {
            return $this->data[$key];
        }
        $array = $this->data;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }

    /**
     * Set data to a given value with key that using "dot" notation.
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function deepSet($key, $value)
    {
        if (is_null($key)) {
            if (!is_array($value)) {
                throw new InvalidParamException('The root "data" value must be array.');
            }
            return $this->data = $value;
        }
        $keys = explode('.', $key);
        $array = & $this->data;
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = & $array[$key];
        }
        $array[array_shift($keys)] = $value;
    }

    /**
     * delete data with key that using "dot" notation.
     *
     * @param $key
     * @return bool
     */
    protected function deepDelete($key)
    {
        $keys = explode('.', $key);
        switch (count($keys)) {
            case 1:
                if (isset($this->data[$keys[0]])) {
                    unset($this->data[$keys[0]]);
                }
                break;
            case 2:
                if (isset($this->data[$keys[0]][$keys[1]])) {
                    unset($this->data[$keys[0]][$keys[1]]);
                }
                break;
            case 3:
                if (isset($this->data[$keys[0]][$keys[1]][$keys[2]])) {
                    unset($this->data[$keys[0]][$keys[1]][$keys[2]]);
                }
                break;
            case 4:
                if (isset($this->data[$keys[0]][$keys[1]][$keys[2]][$keys[3]])) {
                    unset($this->data[$keys[0]][$keys[1]][$keys[2]][$keys[3]]);
                }
                break;
            case 5:
                if (isset($this->data[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]])) {
                    unset($this->data[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]]);
                }
                break;
            case 6:
                if (isset($this->data[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]][$keys[5]])) {
                    unset($this->data[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]][$keys[5]]);
                }
                break;
            default:
                $expression = '$this->data[\'' . implode('\'][\'', $keys) . '\']';
                eval("if (isset({$expression})) { unset({$expression}); }");
        }
        return true;
    }

    /**
     * 获取数据
     */
    abstract public function getData();

    /**
     * 保存改动的和清除删除的数据
     */
    abstract public function saveData();
}