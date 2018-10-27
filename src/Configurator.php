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
    protected $values = [];
    /** @var bool */
    private $autoCreate = true;
    /** @var array */
    private $searchMask, $searchPath, $excludePath, $listCategoryContent = [], $listAllContent = [], $listUsedContent = [];


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
        return ($this->values[$identification]['enable'] ?? false);
    }


    /**
     * Get values.
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->values ?? [];
    }


    /**
     * Get value.
     *
     * @param string $identification
     * @return mixed
     */
    public function getValue(string $identification)
    {
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
                // process default content
                $this->searchDefaultContent($this->searchMask, $this->searchPath, $this->excludePath);
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
     * Search default content.
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
//TODO globalize search files to self class
            // load all default content files
            foreach ($files as $file) {
                $lengthPath = strlen(dirname(__DIR__, 4));
                $partPath = substr($file->getRealPath(), $lengthPath + 1);
                // load neon file
                $fileContent = (array) Neon::decode(file_get_contents($file->getPathname()));
                // prepare empty row
                $this->listCategoryContent[$partPath] = [];
                // decode type logic
                $defaultType = 'translation';
                foreach ($fileContent as $index => $item) {
                    $prepareType = Strings::match($index, '#@[a-z]+@#');
                    // content type
                    $contentType = Strings::trim(implode((array) $prepareType), '@');
                    // content index
                    $contentIndex = Strings::replace($index, ['#@[a-z]+@#' => '']);
                    $value = ['type' => $contentType ?: $defaultType, 'value' => $item];
                    if ($contentType) {
                        // select except translation
                        $this->listCategoryContent[$partPath][$contentIndex] = $value;
                        $this->listAllContent[$contentIndex] = $value;
                    }
                }
            }

            if ($this->values) {
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
     * @return array
     */
    public function getListUsedContent(): array
    {
        return $this->listUsedContent;
    }


    /**
     * Get list category content.
     *
     * @return array
     */
    public function getListCategoryContent(): array
    {
        return $this->listCategoryContent;
    }


    /**
     * Get list all content.
     *
     * @return array
     */
    public function getListAllContent(): array
    {
        return $this->listAllContent;
    }
}
