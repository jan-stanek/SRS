<?php

declare(strict_types=1);

namespace App\AdminModule\ConfigurationModule\Forms;

use App\AdminModule\Forms\BaseFormFactory;
use App\Model\Enums\SkautIsEventType;
use App\Model\Settings\Exceptions\SettingsException;
use App\Model\Settings\Settings;
use App\Model\SkautIs\Repositories\SkautIsCourseRepository;
use App\Model\SkautIs\SkautIsCourse;
use App\Model\Structure\Repositories\SubeventRepository;
use App\Services\ISettingsService;
use App\Services\SkautIsEventEducationService;
use App\Services\SkautIsEventGeneralService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Nette;
use Nette\Application\UI\Form;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use stdClass;
use Throwable;

use function assert;
use function count;

/**
 * Formulár pro nastavení propojení se skautIS akcí.
 *
 * @author Michal Májský
 * @author Jan Staněk <jan.stanek@skaut.cz>
 */
class SkautIsEventFormFactory
{
    use Nette\SmartObject;

    private BaseFormFactory $baseFormFactory;

    private ISettingsService $settingsService;

    private SkautIsCourseRepository $skautIsCourseRepository;

    private SkautIsEventGeneralService $skautIsEventGeneralService;

    private SkautIsEventEducationService $skautIsEventEducationService;

    private SubeventRepository $subeventRepository;

    public function __construct(
        BaseFormFactory $baseForm,
        ISettingsService $settingsService,
        SkautIsCourseRepository $skautIsCourseRepository,
        SkautIsEventGeneralService $skautIsEventGeneralService,
        SkautIsEventEducationService $skautIsEventEducationService,
        SubeventRepository $subeventRepository
    ) {
        $this->baseFormFactory              = $baseForm;
        $this->settingsService              = $settingsService;
        $this->skautIsCourseRepository      = $skautIsCourseRepository;
        $this->skautIsEventGeneralService   = $skautIsEventGeneralService;
        $this->skautIsEventEducationService = $skautIsEventEducationService;
        $this->subeventRepository           = $subeventRepository;
    }

    /**
     * Vytvoří formulář.
     *
     * @throws SettingsException
     * @throws Throwable
     */
    public function create(): Form
    {
        $form = $this->baseFormFactory->create();

        $renderer = $form->getRenderer();
        assert($renderer instanceof Bs4FormRenderer);
        $renderer->wrappers['control']['container'] = 'div class="col-7"';
        $renderer->wrappers['label']['container']   = 'div class="col-5 col-form-label"';

        $eventTypeSelect = $form->addSelect(
            'skautisEventType',
            'admin.configuration.skautis_event_type',
            SkautIsEventType::getSkautIsEventTypesOptions()
        );
        $eventTypeSelect->addCondition($form::EQUAL, SkautIsEventType::GENERAL)
            ->toggle('event-general');
        $eventTypeSelect->addCondition($form::EQUAL, SkautIsEventType::EDUCATION)
            ->toggle('event-education');

        $form->addSelect(
            'skautisEventGeneral',
            'admin.configuration.skautis_event',
            $this->skautIsEventGeneralService->getEventsOptions()
        )
            ->setOption('id', 'event-general');

        $form->addSelect(
            'skautisEventEducation',
            'admin.configuration.skautis_event',
            $this->skautIsEventEducationService->getEventsOptions()
        )
            ->setOption('id', 'event-education');

        $form->addSubmit('submit', 'admin.common.save');

        $form->setDefaults([
            'skautisEventType' => $this->settingsService->getValue(Settings::SKAUTIS_EVENT_TYPE),
        ]);

        $form->onSuccess[] = [$this, 'processForm'];

        return $form;
    }

    /**
     * Zpracuje formulář.
     *
     * @throws SettingsException
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Throwable
     */
    public function processForm(Form $form, stdClass $values): void
    {
        $eventId   = null;
        $eventName = null;
        $eventType = $values->skautisEventType;

        switch ($eventType) {
            case SkautIsEventType::GENERAL:
                $eventId   = $values->skautisEventGeneral;
                $eventName = $this->skautIsEventGeneralService->getEventDisplayName($eventId);
                break;

            case SkautIsEventType::EDUCATION:
                $eventId   = $values->skautisEventEducation;
                $eventName = $this->skautIsEventEducationService->getEventDisplayName($eventId);

                $courses = $this->skautIsEventEducationService->getEventCourses($eventId);

                foreach ($courses as $course) {
                    $skautIsCourse = new SkautIsCourse();
                    $skautIsCourse->setSkautIsCourseId($course->ID);
                    $skautIsCourseName = $course->EventEducationType . (! empty($course->DisplayName) ? ' (' . $course->DisplayName . ')' : '');
                    $skautIsCourse->setName($skautIsCourseName);
                    $this->skautIsCourseRepository->save($skautIsCourse);
                }

                if (count($courses) === 1 && ! $this->subeventRepository->explicitSubeventsExists()) {
                    $subevent = $this->subeventRepository->findImplicit();
                    $subevent->setSkautIsCourses($this->skautIsCourseRepository->findAll());
                    $this->subeventRepository->save($subevent);
                }

                break;
        }

        $this->settingsService->setValue(Settings::SKAUTIS_EVENT_TYPE, $eventType);

        if ($eventId !== null) {
            $this->settingsService->setIntValue(Settings::SKAUTIS_EVENT_ID, $eventId);
            $this->settingsService->setValue(Settings::SKAUTIS_EVENT_NAME, $eventName);
        }
    }
}
