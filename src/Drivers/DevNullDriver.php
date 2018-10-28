<?php declare(strict_types=1);

namespace Configurator\Drivers;

use Configurator\Configurator;
use Dibi\IDataSource;


/**
 * Class DevNullDriver
 *
 * @author  geniv
 * @package Configurator\Drivers
 */
class DevNullDriver extends Configurator
{

    /**
     * Add internal data.
     *
     * @internal
     * @param string $type
     * @param string $identification
     * @param string $content
     * @return int
     */
    protected function saveInternalData(string $type, string $identification, string $content = ''): int
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
        // set fake translate
        $this->values['__DevNullDriver__'] = true;

        // process default content
        $this->searchDefaultContent();
    }


    /**
     * Get list data.
     *
     * @param int|null $idLocale
     * @return IDataSource
     */
    public function getListData(int $idLocale = null): IDataSource
    {
        //
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
     */
    public function deleteData(int $id): int
    {
        return 0;
    }


    /**
     * Clean cache.
     */
    public function cleanCache()
    {
    }
}
