<?php declare(strict_types=1);

namespace Configurator;

use Exception;
use Locale\ILocale;
use Nette\Application\UI\Control;
use Nette\Neon\Neon;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use SplFileInfo;


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
    protected $values, $flattenValues;
    /** @var bool */
    private $autoCreate = true;
    /** @var array */
    private $searchMask, $searchPath, $excludePath, $listCategoryDefaultContent, $listAllDefaultContent;


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
     * @throws Exception
     */
    public function __call($name, $args)
    {
        if (!in_array($name, ['onAnchor'])) {   // exclude method
            if ($this->locale->isReady() && !$this->values) {
//                \Tracy\Debugger::fireLog('Configurator::__call');
                // load data
                $this->loadInternalData();
                // process default content
                $this->searchDefaultContent($this->searchMask, $this->searchPath, $this->excludePath);
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
     * @param array $searchMask
     * @param array $searchPath
     * @param array $excludePath
     */
    public function setSearchPath(array $searchMask = [], array $searchPath = [], array $excludePath = [])
    {
        $this->searchMask = $searchMask;
        $this->searchPath = $searchPath;
        $this->excludePath = $excludePath;
    }


    /**
     * Search default translate.
     *
     * @param array $searchMask
     * @param array $searchPath
     * @param array $excludePath
     * @throws \Dibi\Exception
     */
    private function searchDefaultContent(array $searchMask, array $searchPath = [], array $excludePath = [])
    {
        if ($searchMask && $searchPath) {
            $files = [];
            foreach ($searchPath as $path) {
                // insert dirs
                if (is_dir($path)) {
                    $fil = [];
                    foreach (Finder::findFiles($searchMask)->exclude($excludePath)->from($path) as $file) {
                        $fil[] = $file;
                    }
                    natsort($fil);  // natural sorting path
                    $files = array_merge($files, $fil);  // merge sort array
                }
                // insert file
                if (is_file($path)) {
                    $files[] = new SplFileInfo($path);
                }
            }

            // load all default translation files
            foreach ($files as $file) {
                $lengthPath = strlen(dirname(__DIR__, 4));
                $partPath = substr($file->getRealPath(), $lengthPath + 1);
                // load neon file
                $fileContent = (array) Neon::decode(file_get_contents($file->getPathname()));
                // prepare empty row
                $this->listCategoryDefaultContent[$partPath] = [];
                // decode type logic
                $defaultType = 'translation';
                foreach ($fileContent as $index => $item) {
                    $prepareType = Strings::match($index, '#@[a-z]+@#');
                    // content type
                    $contentType = Strings::trim(implode((array) $prepareType), '@');
                    // content index
                    $contentIndex = Strings::replace($index, ['#@[a-z]+@#' => '']);
                    $value = ['type' => $contentType ?: $defaultType, 'value' => $item];
                    $this->listCategoryDefaultContent[$partPath][$contentIndex] = $value;
                    $this->listAllDefaultContent[$contentIndex] = $value;
                }
            }

            if ($this->flattenValues) {
                foreach ($this->listAllDefaultContent as $index => $item) {
                    if ($item['type'] != 'translation') {
                        if (isset($this->flattenValues[$index]) && $this->flattenValues[$index]['content'] == $this->getDefaultContent($item['type'], $index)) {
                            // call only not translation, exist index, value is default
                            $this->addInternalData($item['type'], $index, $item['value']); // insert data
                        }
                    }
                }
            }
        }
    }


    /**
     * Get list category default content.
     *
     * @return array
     */
    public function getListCategoryDefaultContent(): array
    {
        return $this->listCategoryDefaultContent;
    }


    /**
     * Get list all default content.
     *
     * @return array
     */
    public function getListAllDefaultContent(): array
    {
        return $this->listAllDefaultContent;
    }


    /**
     * Get flatten values.
     *
     * @return array
     */
    public function getFlattenValues(): array
    {
        return $this->flattenValues;
    }
}
