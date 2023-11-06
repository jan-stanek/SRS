<?php

declare(strict_types=1);

namespace App\AdminModule\PaymentsModule\Forms;

use App\AdminModule\Forms\BaseFormFactory;
use App\AdminModule\Presenters\AdminBasePresenter;
use App\Model\Application\Repositories\ApplicationRepository;
use App\Model\Payment\Payment;
use App\Model\Payment\Repositories\PaymentRepository;
use App\Services\ApplicationService;
use Nette;
use Nette\Application\UI\Form;
use Nextras\FormComponents\Controls\DateControl;
use stdClass;
use Throwable;

use function assert;

/**
 * Formulář pro úpravu platby.
 */
class EditPaymentFormFactory
{
    use Nette\SmartObject;

    /**
     * Upravovaná platba.
     */
    private Payment|null $payment = null;

    public function __construct(
        private readonly BaseFormFactory $baseFormFactory,
        private readonly PaymentRepository $paymentRepository,
        private readonly ApplicationRepository $applicationRepository,
        private readonly ApplicationService $applicationService,
    ) {
    }

    /**
     * Vytvoří formulář.
     */
    public function create(int $id): Form
    {
        $this->payment = $this->paymentRepository->findById($id);

        $form = $this->baseFormFactory->create();

        $inputDate = new DateControl('admin.payments.payments.date');
        $form->addComponent($inputDate, 'date');

        $inputAmount = $form->addInteger('amount', 'admin.payments.payments.amount');

        $inputVariableSymbol = $form->addText('variableSymbol', 'admin.payments.payments.variable_symbol');

        $inputPairedApplication = $form->addMultiSelect('pairedApplications', 'admin.payments.payments.paired_applications', $this->applicationRepository->getApplicationsVariableSymbolsOptions())
            ->setHtmlAttribute('class', 'datagrid-multiselect')
            ->setHtmlAttribute('data-live-search', 'true');

        $form->addSubmit('submit', 'admin.common.save');

        $form->addSubmit('cancel', 'admin.common.cancel')
            ->setValidationScope([])
            ->setHtmlAttribute('class', 'btn btn-warning');

        if ($this->payment->getTransactionId() === null) {
            $inputDate
                ->addRule(Form::FILLED, 'admin.payments.payments.date_empty');

            $inputAmount
                ->addRule(Form::FILLED, 'admin.payments.payments.amount_empty')
                ->addRule(Form::MIN, 'admin.payments.payments.amount_low', 1);

            $inputVariableSymbol
                ->addRule(Form::FILLED, 'admin.payments.payments.variable_symbol_empty');
        } else {
            $inputDate->setDisabled();
            $inputAmount->setDisabled();
            $inputVariableSymbol->setDisabled();
        }

        $pairedValidApplications = $this->payment->getPairedValidApplications();

        $inputPairedApplication->setItems(
            $this->applicationRepository->getWaitingForPaymentOrPairedApplicationsVariableSymbolsOptions($pairedValidApplications),
        );

        $form->setDefaults([
            'date' => $this->payment->getDate(),
            'amount' => $this->payment->getAmount(),
            'variableSymbol' => $this->payment->getVariableSymbol(),
            'pairedApplications' => $this->applicationRepository->findApplicationsIds($pairedValidApplications),
        ]);

        $form->onSuccess[] = [$this, 'processForm'];

        return $form;
    }

    /**
     * Zpracuje formulář.
     *
     * @throws Throwable
     */
    public function processForm(Form $form, stdClass $values): void
    {
        if ($form->isSubmitted() != $form['cancel']) {
            $presenter = $form->getPresenter();
            assert($presenter instanceof AdminBasePresenter);

            $pairedApplications = $this->applicationRepository->findApplicationsByIds($values->pairedApplications);

            $this->applicationService->updatePayment($this->payment, $values->date, $values->amount, $values->variableSymbol, $pairedApplications, $presenter->getDbUser());
        }
    }
}
