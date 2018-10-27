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
        $this->values[$identification] = ['type' => $type, 'content' => $content, 'enable' => true];
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
     * Edit data.
     *
     * @param int   $id
     * @param array $values
     * @return int
     */
    public function editData(int $id, array $values): int
    {
        return 0;
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
        return 0;
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
