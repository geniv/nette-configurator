<?php declare(strict_types=1);

namespace Configurator\Bridges\Tracy;

use Configurator\IConfigurator;
use Latte\Engine;
use Nette\Localization\ITranslator;
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
    /** @var ITranslator */
    private $translator;


    /**
     * Panel constructor.
     *
     * @param IConfigurator    $configurator
     * @param ITranslator|null $translator
     */
    public function __construct(IConfigurator $configurator, ITranslator $translator = null)
    {
        $this->configurator = $configurator;
        $this->translator = $translator;
    }


    /**
     * Renders HTML code for custom tab.
     *
     * @return string
     */
    public function getTab(): string
    {
        return '<span title="Configurator">' .
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" width="16" height="16"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"><path stroke-width="2.4" d="M28.842 17.621v-5.2l-2.887-.355a11.26 11.26 0 0 0-1.112-2.689l1.789-2.29-3.677-3.677-2.29 1.789a11.26 11.26 0 0 0-2.689-1.112L17.621 1.2h-5.2l-.355 2.887a11.26 11.26 0 0 0-2.689 1.112L7.087 3.41 3.41 7.087l1.789 2.29a11.26 11.26 0 0 0-1.112 2.689l-2.887.355v5.2l2.887.355a11.26 11.26 0 0 0 1.112 2.689l-1.789 2.29 3.677 3.677 2.29-1.789a11.26 11.26 0 0 0 2.689 1.112l.355 2.887h5.2l.355-2.887a11.26 11.26 0 0 0 2.689-1.112l2.29 1.789 3.677-3.677-1.789-2.29a11.26 11.26 0 0 0 1.112-2.689l2.887-.355z"/><circle stroke-width="1.76" cx="15.021" cy="15.021" r="3.926"/></g></svg>' .
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
            'listUsedTranslate'   => ($this->translator ? $this->translator->getListUsedTranslate() : []),  // list translator index
            'listAllContent'      => $this->configurator->getListAllContent(),      // list all content
            'listCategoryContent' => $this->configurator->getListCategoryContent(), // list category content
        ];
        $latte = new Engine;
        return $latte->renderToString(__DIR__ . '/PanelTemplate.latte', $params);
    }
}
