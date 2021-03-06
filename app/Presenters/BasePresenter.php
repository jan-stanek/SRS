<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Localization\ITranslator;
use stdClass;
use WebLoader\Nette\LoaderFactory;

/**
 * BasePresenter.
 *
 * @author Michal Májský
 * @author Jan Staněk <jan.stanek@skaut.cz>
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @inject */
    public LoaderFactory $webLoader;

    /** @inject */
    public ITranslator $translator;

    /**
     * Zobrazí přeloženou zprávu.
     *
     * @param string   $message
     * @param string   $type
     * @param string[] $parameters
     */
    public function flashMessage($message, $type = 'info', ?string $icon = null, ?int $count = null, array $parameters = []): stdClass
    {
        if ($icon) {
            return parent::flashMessage('<span class="fa fa-' . $icon . '"></span> ' .
                $this->translator->translate($message, $count, $parameters), $type);
        }

        return parent::flashMessage($this->translator->translate($message, $count, $parameters), $type);
    }
}
