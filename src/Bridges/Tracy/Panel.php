<?php declare(strict_types=1);

namespace Configurator\Bridges\Tracy;

use Configurator\IConfigurator;
use Latte\Engine;
use Nette\SmartObject;
use Tracy\IBarPanel;


/**
 * Class Panel
 *
 * @author  geniv
 * @package Configurator\Bridges\Tracy
 */
class Panel implements IBarPanel
{
    use SmartObject;

    /** @var IConfigurator */
    private $configurator;


    /**
     * Panel constructor.
     *
     * @param IConfigurator $configurator
     */
    public function __construct(IConfigurator $configurator)
    {
        $this->configurator = $configurator;
    }


    /**
     * Renders HTML code for custom tab.
     *
     * @return string
     */
    public function getTab(): string
    {
        //TODO SVG icon!
        return '<span title="Configurator">' .
            '<svg height="16" viewBox="0 0 48 48" width="16" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h48v48h-48z" fill="none"/><path d="M25.74 30.15l-5.08-5.02.06-.06c3.48-3.88 5.96-8.34 7.42-13.06h5.86v-4.01h-14v-4h-4v4h-14v3.98h22.34c-1.35 3.86-3.46 7.52-6.34 10.72-1.86-2.07-3.4-4.32-4.62-6.7h-4c1.46 3.26 3.46 6.34 5.96 9.12l-10.17 10.05 2.83 2.83 10-10 6.22 6.22 1.52-4.07zm11.26-10.15h-4l-9 24h4l2.25-6h9.5l2.25 6h4l-9-24zm-5.25 14l3.25-8.67 3.25 8.67h-6.5z"/></svg>' .
            'Configurator' .
            '</span>';
    }


    /**
     * Renders HTML code for custom panel.
     *
     * @return string
     */
    public function getPanel(): string
    {
        $params = [
            'class'               => get_class($this->configurator),
            'listUsedContent'     => $this->configurator->getListUsedContent(),     // list used content index
            'listAllContent'      => $this->configurator->getListAllContent(),      // list all content
            'listCategoryContent' => $this->configurator->getListCategoryContent(), // list category content
            'flattenValues'       => $this->configurator->getFlattenValues(),       // list values
        ];
        $latte = new Engine;
        return $latte->renderToString(__DIR__ . '/PanelTemplate.latte', $params);
    }
}
