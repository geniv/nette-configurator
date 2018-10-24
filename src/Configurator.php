<?php declare(strict_types=1);

namespace Configurator;

use Exception;
use Nette\Application\UI\Control;
use Locale\ILocale;


/**
 * Class Configurator
 *
 * @author  geniv
 */
abstract class Configurator extends Control implements IConfigurator
{
    /** @var int */
    protected $idDefaultLocale;
    /** @var ILocale */
    protected $locale;
    /** @var array */
    protected $values;
    /** @var bool */
    private $autoCreate = true;


    /**
     * Configurator constructor.
     *
     * @param ILocale $locale
     */
    public function __construct(ILocale $locale)
    {
        parent::__construct();

        $this->locale = $locale;
        $this->idDefaultLocale = $locale->getIdDefault();
    }


    /**
     * Set auto create.
     *
     * @param bool $status
     * @return Configurator
     */
    public function setAutoCreate(bool $status): self
    {
        $this->autoCreate = $status;
        return $this;
    }


    /**
     * Overloading is and get method.
     *
     * @param $name
     * @param $args
     * @return mixed|null
     * @throws \Dibi\Exception
     */
    public function __call($name, $args)
    {
        if (!in_array($name, ['onAnchor'])) {   // exclude method
            if ($this->locale->isReady() && !$this->values) {
//                \Tracy\Debugger::fireLog('Configurator::__call');
                $this->loadInternalData();   // load data
            }

            if (!isset($args[0])) {
                throw new Exception('Identification parameter is not used.');
            }
            $ident = $args[0];  // load identification
            $method = strtolower(substr($name, 6)); // load method name
            $return = (isset($args[1]) ? $args[1] : false); // load result

            // setter - set method (extended for translator)
            if (substr($name, 0, 3) == 'set' && isset($ident) && isset($args[1])) {
                $method = strtolower(substr($name, 3));
                $this->addInternalData($method, $ident, $args[1]); // insert data
                return $args[1];    // return message only by create new translate
            }

            // enable method
            if (substr($name, 0, 8) == 'isEnable') {
                $method = strtolower(substr($name, 8));
                if (isset($this->values[$method][$ident])) {
                    $block = $this->values[$method][$ident];
                    return $block['enable'];    // return enable state
                }
            }

            // getter - get method
            if (substr($name, 0, 3) == 'get' && isset($ident)) {
                $method = strtolower(substr($name, 3));     // modify name
                $return = true;     // set only return
            }

            // create
            if ($this->autoCreate && (!isset($this->values[$method]) || !isset($this->values[$method][$ident]))) {
                $this->addInternalData($method, $ident);    // insert
                $this->loadInternalData();                  // reloading
            }

            // load value
            if (isset($this->values[$method])) {
                $block = $this->values[$method];
                if (isset($block[$ident])) {
                    if ($return) {
                        return ($block[$ident]['enable'] ? $block[$ident]['content'] : null);
                    }
                    echo($block[$ident]['enable'] ? $block[$ident]['content'] : null);
                } else {
                    throw new Exception('Identification "' . $method . ':' . $ident . '" does not eixst.');
                }
            } else {
                throw new Exception('Invalid block. Block: "' . $method . '" does not exists.');
            }
        }
        return null;
    }


    /**
     * Get internal id identification.
     *
     * @param array $values
     * @return int
     * @throws \Dibi\Exception
     */
    abstract protected function getInternalIdIdentification(array $values): int;


    /**
     * Add internal data.
     *
     * @internal
     * @param string $type
     * @param string $identification
     * @param string $content
     * @return int
     * @throws \Dibi\Exception
     */
    abstract protected function addInternalData(string $type, string $identification, string $content = ''): int;


    /**
     * Load internal data.
     *
     * @internal
     */
    abstract protected function loadInternalData();
}
