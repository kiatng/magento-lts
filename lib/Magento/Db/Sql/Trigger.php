<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Magento_Db
 */

/**
 * Magento_Db_Sql_Trigger
 *
 * @package    Magento_Db
 */
class Magento_Db_Sql_Trigger
{
    public const NAME      = 'name';
    public const TARGET    = 'target';
    public const TIME      = 'time';
    public const EVENT     = 'event';
    public const SCOPE     = 'scope';
    public const BODY      = 'body';

    /**
     * SQL constants
     */
    public const SQL_TIME_BEFORE   = 'BEFORE';
    public const SQL_TIME_AFTER    = 'AFTER';
    public const SQL_EVENT_INSERT  = 'INSERT';
    public const SQL_EVENT_UPDATE  = 'UPDATE';
    public const SQL_EVENT_DELETE  = 'DELETE';
    public const SQL_FOR_EACH_ROW  = 'FOR EACH ROW';

    /**
     * Trigger parts
     *
     * @var array
     */
    protected $_parts = [];

    /**
     * Allowed time types
     *
     * @var array
     */
    protected $_timeTypes = [
        self::SQL_TIME_AFTER,
        self::SQL_TIME_BEFORE,
    ];

    /**
     * Allowed event types
     *
     * @var array
     */
    protected $_eventTypes = [
        self::SQL_EVENT_INSERT,
        self::SQL_EVENT_UPDATE,
        self::SQL_EVENT_DELETE,
    ];

    /**
     * Initial trigger structure, for MySQL scope is always "FOR EACH ROW".
     * Time "AFTER" is default
     *
     * @var array
     */
    protected static $_partsInit = [
        self::TARGET    => null,
        self::TIME      => self::SQL_TIME_AFTER,
        self::EVENT     => null,
        self::SCOPE     => self::SQL_FOR_EACH_ROW,
        self::BODY      => [],
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_parts = self::$_partsInit;
    }

    /**
     * Validate where all trigger parts set?
     *
     * @return Magento_Db_Sql_Trigger
     * @throws Exception
     */
    protected function _validateIsComplete()
    {
        foreach (array_keys(self::$_partsInit) as $part) {
            if (empty($this->_parts[$part])) {
                throw new Exception('Part [' . $part . '] should be set');
            }
        }
        return $this;
    }

    /**
     * Set trigger part
     *
     * @param $part
     * @param $value
     * @return Magento_Db_Sql_Trigger
     * @throws InvalidArgumentException
     */
    protected function _setPart($part, $value)
    {
        if ($value != null) {
            $this->_parts[$part] = $value;
        } else {
            throw new InvalidArgumentException('Part [' . $part . '] can not be empty');
        }
        return $this;
    }

    /**
     * Set trigger part
     *
     * @param $part
     * @return string|array
     * @throws Exception
     */
    protected function _getPart($part)
    {
        if (isset($this->_parts[$part])) {
            return $this->_parts[$part];
        }

        throw new Exception('Part [' . $part . '] does\'t exists');
    }

    /**
     * Set body part to trigger
     *
     * @param $part
     * @param $value
     * @return Magento_Db_Sql_Trigger
     * @throws InvalidArgumentException
     */
    public function setBodyPart($part, $value)
    {
        if ($value != null) {
            $this->_parts[self::BODY][$part] = $value;
        } else {
            throw new InvalidArgumentException('Part [' . $part . '] can not be empty');
        }
        return $this;
    }

    /**
     * Set body part to trigger
     *
     * @param string $part
     * @return string
     * @throws Exception
     */
    public function getBodyPart($part)
    {
        if (isset($this->_parts[self::BODY][$part])) {
            return $this->_parts[self::BODY][$part];
        }

        throw new Exception('Part [' . $part . '] does\'t exists');
    }

    /**
     * Generate trigger name
     *
     * @return string
     */
    protected function _generateTriggerName()
    {
        return strtolower('trg_' . $this->_parts[self::TARGET]
            . '_' . $this->_parts[self::TIME]
            . '_' . $this->_parts[self::EVENT]);
    }

    /**
     * Set trigger time {BEFORE/AFTER}
     * @param $time
     * @return Magento_Db_Sql_Trigger
     * @throws InvalidArgumentException
     */
    public function setTime($time)
    {
        if (in_array($time, $this->getTimeTypes())) {
            $this->_setPart(self::TIME, $time);
        } else {
            throw new InvalidArgumentException('Unsupported time type!');
        }
        return $this;
    }

    /**
     * Set trigger event {INSERT/UPDATE/DELETE}
     *
     * @param $event
     * @return Magento_Db_Sql_Trigger
     * @throws InvalidArgumentException
     */
    public function setEvent($event)
    {
        if (in_array($event, $this->getEventTypes())) {
            $this->_setPart(self::EVENT, $event);
        } else {
            throw new InvalidArgumentException('Unsupported event type!');
        }
        return $this;
    }

    /**
     * Set trigger target, table name
     *
     * @param $target
     * @return Magento_Db_Sql_Trigger
     */
    public function setTarget($target)
    {
        $this->_setPart(self::TARGET, $target);
        return $this;
    }

    /**
     * Set trigger name
     *
     * @param $name
     * @return Magento_Db_Sql_Trigger
     */
    public function setName($name)
    {
        $this->_setPart(self::NAME, $name);
        return $this;
    }

    /**
     * Retrieve trigger name.
     * If trigger name does not exists generate it by template 'trg_{TARGET}_{TIME}_{EVENT}'.
     *
     * @return mixed
     */
    public function getName()
    {
        if (empty($this->_parts[self::NAME])) {
            $this->_parts[self::NAME] = $this->_generateTriggerName();
        }
        return $this->_parts[self::NAME];
    }

    /**
     * Set trigger body
     *
     * @param array|string $body
     * @return Magento_Db_Sql_Trigger
     */
    public function setBody($body)
    {
        if (!is_array($body)) {
            $body = [$body];
        }
        $this->_setPart(self::BODY, $body);
        return $this;
    }

    /**
     * Get body parts of trigger
     *
     * @return array
     */
    public function getBody()
    {
        return $this->_getPart(self::BODY);
    }

    /**
     * Get trigger creating SQL script
     *
     * @return string
     */
    public function assemble()
    {
        $this->_validateIsComplete();
        return 'CREATE TRIGGER '
            . $this->getName() . "\n"
            . $this->_parts[self::TIME] . ' ' . $this->_parts[self::EVENT] . "\n"
            . 'ON ' . $this->_parts[self::TARGET] . ' ' . $this->_parts[self::SCOPE] . "\n"
            . "BEGIN\n"
            . implode("\n", $this->_parts[self::BODY]) . "\n"
            . "END;\n";
    }

    /**
     * Implement magic method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->assemble();
    }

    /**
     * Retrieve list of allowed events
     *
     * @return array
     */
    public function getEventTypes()
    {
        return $this->_eventTypes;
    }

    /**
     * Retrieve list of allowed time types
     *
     * @return array
     */
    public function getTimeTypes()
    {
        return $this->_timeTypes;
    }

    /**
     * Reset trigger parts
     *
     * @return Magento_Db_Sql_Trigger
     */
    public function reset()
    {
        $this->_parts = self::$_partsInit;
        return $this;
    }
}
