<?php

declare(strict_types=1);

namespace App\ActionModule\Presenters;

use App\Model\Settings\Exceptions\SettingsException;
use App\Model\Settings\Settings;
use App\Services\BankService;
use App\Services\ISettingsService;
use Nette\Application\Responses\TextResponse;
use Throwable;

/**
 * Presenter obsluhující načítání plateb z API banky.
 *
 * @author Jan Staněk <jan.stanek@skaut.cz>
 */
class BankPresenter extends ActionBasePresenter
{
    /** @inject */
    public BankService $bankService;

    /** @inject */
    public ISettingsService $settingsService;

    /**
     * Zkontroluje splatnost přihlášek.
     *
     * @throws SettingsException
     * @throws Throwable
     */
    public function actionCheck(): void
    {
        $from = $this->settingsService->getDateValue(Settings::BANK_DOWNLOAD_FROM);
        $this->bankService->downloadTransactions($from);

        $response = new TextResponse(null);
        $this->sendResponse($response);
    }
}
