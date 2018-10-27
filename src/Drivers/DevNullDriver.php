<?php declare(strict_types=1);

namespace Configurator\Drivers;

use Configurator\Configurator;
use Dibi\Fluent;


/**
 * Class DevNullDriver
 *
 * @author  geniv
 * @package Configurator\Drivers
 */
class DevNullDriver extends Configurator
{

//    /**
//     * DevNullDriver constructor.
//     *
//     * @param ILocale $locale
//     */
//    public function __construct(ILocale $locale)
//    {
//        parent::__construct($locale);
//    }
//    /**
//     * Load translate.
//     */
//    protected function loadTranslate()
//    {
//        // set fake translate for enable searchDefaultTranslate()
//        $this->dictionary['__DevNullDriver__'] = true;
//    }
//    /**
//     * Save translate.
//     *
//     * @param string $identification
//     * @param        $message
//     * @param null   $idLocale
//     * @return string
//     */
//    protected function saveTranslate(string $identification, $message, $idLocale = null): string
//    {
//        $this->dictionary[$identification] = $message;  // save to only variable
//        return $message;
//    }


    /**
     * Get internal id identification.
     *
     * @param array $values
     * @return int
     * @throws \Dibi\Exception
     */
    protected function getInternalIdIdentification(array $values): int
    {
        // TODO: Implement getInternalIdIdentification() method.
    }
//TODO nacteni textu z hledani a pouzi ty!!!


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
    protected function addInternalData(string $type, string $identification, string $content = ''): int
    {
        $this->values[$type][$identification] = ['type' => $type, 'content' => $content, 'enable' => true];
        return 1;
    }


    /**
     * Load internal data.
     *
     * @internal
     */
    protected function loadInternalData()
    {
        // TODO: Implement loadInternalData() method.

        // set fake translate
        $this->values['__DevNullDriver__'] = true;
    }


    /**
     * Get list ident.
     *
     * @return array
     */
    public function getListIdent(): array
    {
        // TODO: Implement getListIdent() method.
        return [];
    }


    /**
     * Get list data.
     *
     * @param int|null $idLocale
     * @return Fluent
     */
    public function getListData(int $idLocale = null): Fluent
    {
        // TODO: Implement getListData() method.
    }


    /**
     * Get list data by type.
     *
     * @param string   $type
     * @param int|null $idLocale
     * @return Fluent
     */
    public function getListDataByType(string $type, int $idLocale = null): Fluent
    {
        // TODO: Implement getListDataByType() method.
    }


    /**
     * Get list data type.
     *
     * @return array
     */
    public function getListDataType(): array
    {
        // TODO: Implement getListDataType() method.
        return [];
    }


    /**
     * Get data by id.
     *
     * @param int      $idIdent
     * @param int|null $idLocale
     * @return array
     */
    public function getDataById(int $idIdent, int $idLocale = null): array
    {
        // TODO: Implement getDataById() method.
        return [];
    }


    /**
     * Edit data.
     *
     * @param int   $id
     * @param array $values
     * @return int
     * @throws \Dibi\Exception
     */
    public function editData(int $id, array $values): int
    {
        // TODO: Implement editData() method.
    }


    /**
     * Delete data.
     *
     * @param int $id
     * @return int
     * @throws \Dibi\Exception
     */
    public function deleteData(int $id): int
    {
        // TODO: Implement deleteData() method.
    }


    /**
     * Clean cache.
     */
    public function cleanCache()
    {
        // TODO: Implement cleanCache() method.
    }


    /**
     * Get data by ident.
     *
     * @param string   $ident
     * @param int|null $idLocale
     * @return array
     */
    public function getDataByIdent(string $ident, int $idLocale = null): array
    {
        // TODO: Implement getDataByIdent() method.
        return [];
    }
}
