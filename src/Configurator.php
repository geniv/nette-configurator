<?php declare(strict_types=1);

namespace Configurator;

use Exception;
use Locale\ILocale;
use Nette\Application\UI\Control;
use SearchContent;


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
    protected $values = [];
    /** @var bool */
    private $autoCreate = true;
    /** @var array */
    private $listAllContent = [], $listUsedContent = [];
    /** @var SearchContent */
    private $searchContent;


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
     * Is enable.
     *
     * @param string $identification
     * @return bool
     */
    public function isEnable(string $identification): bool
    {
        $this->loadInternalData();  // load data
        return (bool) ($this->values[$identification]['enable'] ?? false);
    }


    /**
     * Get values.
     *
     * @internal
     * @return array
     */
    public function getValues(): array
    {
        $this->loadInternalData();  // load data
        return $this->values ?? [];
    }


    /**
     * Get values by type.
     *
     * @internal
     * @param string $type
     * @return array
     */
    public function getValuesByType(string $type): array
    {
        $this->loadInternalData();  // load data
        return array_filter($this->values, function ($item) use ($type) {
            return ($item['type'] == $type);
        });
    }


    /**
     * Get value.
     *
     * @param string $identification
     * @return mixed
     */
    public function getValue(string $identification)
    {
        $this->loadInternalData();  // load data
        return ($this->values[$identification] ?? null);
    }


    /**
     * Overloading is and get method.
     *
     * @param $name
     * @param $args
     * @return mixed|null
     * @throws \Dibi\Exception
     * @throws Exception
     */
    public function __call($name, $args)
    {
        if (!in_array($name, ['onAnchor'])) {   // exclude method
            if ($this->locale->isReady() && !$this->values) {
//                \Tracy\Debugger::fireLog('Configurator::__call');
                $this->loadInternalData();  // load data
            }

            if (!isset($args[0])) {
                throw new Exception('Identification parameter is not used.');
            }
            $ident = $args[0];  // load identification

            // setter - set method (extended for translator)
            if (substr($name, 0, 3) == 'set' && isset($ident) && isset($args[1])) {
                $type = strtolower(substr($name, 3));
                $this->saveInternalData($type, $ident, $args[1]); // insert data
                return $args[1];    // return message only by create new translate
            }

            // create
            if ($this->autoCreate && (!isset($this->values[$ident]))) {
                $type = strtolower(substr($name, 6)); // load type name
                $this->saveInternalData($type, $ident);      // insert
                $this->loadInternalData();                  // reloading
            }

            // load value
            if (isset($this->values[$ident])) {
                $value = $this->values[$ident];
                $this->listUsedContent[$ident] = $value;    // add to use
                if ($args[1] ?? false) {
                    // return for renderXXX
                    return ($value['enable'] ? $value['content'] : null);
                }
                echo($value['enable'] ? $value['content'] : null);
            } else {
                throw new Exception('Identification "' . $ident . '" does not exist.');
            }
        }
        return null;
    }


    /**
     * Save internal data.
     *
     * @internal
     * @param string $type
     * @param string $identification
     * @param string $content
     * @return int
     * @throws \Dibi\Exception
     */
    abstract protected function saveInternalData(string $type, string $identification, string $content = ''): int;


    /**
     * Load internal data.
     *
     * @internal
     */
    abstract protected function loadInternalData();


    /*
     *
     * -- SYSTEM --
     *
     */


    /**
     * Get default content.
     *
     * @param string $type
     * @param string $identification
     * @return string
     */
    protected function getDefaultContent(string $type, string $identification): string
    {
        return '## ' . $type . ' - ' . $identification . ' ##';
    }


    /**
     * Set path search.
     *
     * @internal
     * @param array $searchMask
     * @param array $searchPath
     * @param array $excludePath
     */
    public function setSearchPath(array $searchMask = [], array $searchPath = [], array $excludePath = [])
    {
        $this->searchContent = new SearchContent($searchMask, $searchPath, $excludePath);
    }


    /**
     * Get search content.
     *
     * @return SearchContent
     */
    public function getSearchContent(): SearchContent
    {
        return $this->searchContent;
    }


    /**
     * Search default content.
     *
     * @internal
     * @throws \Dibi\Exception
     */
    protected function searchDefaultContent()
    {
        // call in: loadInternalData()
        if ($this->searchContent) {
            $this->listAllContent = $this->searchContent->getList();

            if ($this->values && $this->listAllContent) {
                // list all content
                foreach ($this->listAllContent as $index => $item) {
                    // call only if values does not exist or values is default ## value
                    if (!isset($this->values[$index]) || $this->values[$index]['content'] == $this->getDefaultContent($item['type'], $index)) {
                        $this->saveInternalData($item['type'], $index, $item['value']); // insert data
                    }
                }
            }
        }
    }


    /**
     * Get list used content.
     *
     * @internal
     * @return array
     */
    public function getListUsedContent(): array
    {
        return $this->listUsedContent;
    }


    /**
     * Get list category content.
     *
     * @internal
     * @return array
     */
    public function getListCategoryContent(): array
    {
        return $this->searchContent->getListCategory();
    }


    /**
     * Get list all content.
     *
     * @internal
     * @return array
     */
    public function getListAllContent(): array
    {
        return $this->listAllContent;
    }
}
