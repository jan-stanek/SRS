<?php

declare(strict_types=1);

namespace App\WebModule\Components;

use App\Model\Acl\Repositories\RoleRepository;
use App\Model\Acl\Role;
use App\Model\Cms\Dto\ContentDto;
use App\Model\Settings\Exceptions\SettingsException;
use App\Model\Settings\Settings;
use App\Services\ISettingsService;
use App\WebModule\Forms\ContactForm;
use App\WebModule\Forms\IContactFormFactory;
use Nette\Application\UI\Control;
use Throwable;

/**
 * Komponenta s kontaktním formulářem.
 *
 * @author Jan Staněk <jan.stanek@skaut.cz>
 */
class ContactFormContentControl extends Control
{
    private IContactFormFactory $contactFormFactory;

    private RoleRepository $roleRepository;

    private ISettingsService $settingsService;

    public function __construct(
        IContactFormFactory $contactFormFactory,
        RoleRepository $roleRepository,
        ISettingsService $settingsService
    ) {
        $this->contactFormFactory = $contactFormFactory;
        $this->roleRepository     = $roleRepository;
        $this->settingsService    = $settingsService;
    }

    /**
     * @throws SettingsException
     * @throws Throwable
     */
    public function render(ContentDto $content): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/contact_form_content.latte');

        $template->heading = $content->getHeading();

        $template->backlink = $this->getPresenter()->getHttpRequest()->getUrl()->getPath();

        $user                    = $this->getPresenter()->user;
        $template->guestRole     = $user->isInRole($this->roleRepository->findBySystemName(Role::GUEST)->getName());
        $template->guestsAllowed = $this->settingsService->getBoolValue(Settings::CONTACT_FORM_GUESTS_ALLOWED);

        $template->render();
    }

    public function createComponentContactForm(): ContactForm
    {
        $form = $this->contactFormFactory->create();

        $form->onSave[] = function (): void {
            $this->getPresenter()->flashMessage('web.contact_form_content.send_message_successful', 'success');

            $this->getPresenter()->redirect('this');
        };

        return $form;
    }
}
