<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Helper\ArrayHelper;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Cell;
use Tymy\Module\Core\Model\Row;
use Tymy\Module\Poll\Manager\OptionManager;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Poll\Model\Poll;
use Tymy\Module\Setting\Presenter\Front\SettingBasePresenter;

class PollPresenter extends SettingBasePresenter
{
    #[\Nette\DI\Attributes\Inject]
    public PollManager $pollManager;

    #[\Nette\DI\Attributes\Inject]
    public OptionManager $optionManager;
    private ?Poll $poll = null;

    public function actionDefault(?string $resource = null): void
    {
        if ($resource) {
            $this->setView("poll");
        }
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("poll.poll", 2), $this->link(":Setting:Poll:"));
    }

    public function renderDefault(): void
    {
        $this->template->cols = [
            null,
            "Id",
            $this->translator->translate("settings.title"),
            $this->translator->translate("settings.description"),
            $this->translator->translate("settings.status"),
            $this->translator->translate("common.created"),
        ];

        $this->template->rows = [];
        $polls = $this->pollManager->getList();
        foreach ($polls as $poll) {
            assert($poll instanceof Poll);
            $this->template->rows[] = new Row([
                Cell::detail($this->link(":Setting:Poll:", [$poll->getWebName()])),
                $poll->getId(),
                $poll->getCaption(),
                $poll->getDescription(),
                $this->translator->translate("poll." . strtolower($poll->getStatus())),
                $poll->getCreatedAt()->format(BaseModel::DATE_CZECH_FORMAT) . ($poll->getCreatedById() ? ", " . $this->userManager->getById($poll->getCreatedById())?->getDisplayName() : ""),
            ]);
        }

        $this->template->polls = $polls;
    }

    public function renderNew(): void
    {
        $this->allowPermission('ASK.VOTE_UPDATE');

        $this->addBreadcrumb($this->translator->translate("poll.new"));
    }

    public function renderPoll(string $resource): void
    {
        $this->allowPermission('ASK.VOTE_UPDATE');

        //RENDERING POLL DETAIL
        $pollId = $this->parseIdFromWebname($resource);
        $this->poll = $this->pollManager->getById($pollId);

        if (!$this->poll instanceof Poll) {
            $this->flashMessage($this->translator->translate("poll.errors.pollNotExists", null, ['id' => $pollId]), "danger");
            $this->redirect(':Setting:Poll:');
        }

        $this->addBreadcrumb($this->poll->getCaption(), $this->link(":Setting:Poll:", $this->poll->getWebName()));
        $this->template->poll = $this->poll;
    }

    public function handlePollDelete(string $resource): void
    {
        $pollId = $this->parseIdFromWebname($resource);
        try {
            $this->pollManager->delete($pollId);
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }

        $this->redirect(':Setting:Poll:');
    }

    public function createComponentPollForm(): Form
    {
        $resource = $this->getRequest()->getParameter("resource");
        $pollId = $resource ? $this->parseIdFromWebname($resource) : null;
        return $this->formFactory->createPollConfigForm(fn(Form $form, stdClass $values) => $this->pollFormSuccess($form, $values), ($pollId ? $this->pollManager->getById($pollId) : null));
    }

    public function pollFormSuccess(Form $form, stdClass $values): void
    {
        try {
            /* @var $poll Poll */
            $poll = $values->id ?
                $this->pollManager->update((array) $values, $values->id) :
                $this->pollManager->create((array) $values);
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
            $this->redirect(':Setting:Poll:');
        }

        assert($poll instanceof Poll);

        $existingOptionIds = ArrayHelper::entityIds($poll->getOptions());
        $optionsToDel = [];

        foreach ($form->getHttpData() as $name => $value) { //get options from http data instead of $values to read also dynamically adde option rows
            if (preg_match('/option_id_(.+)/m', $name, $matches)) {
                $id = $matches[1];

                if ($id === '0' && !empty($existingOptionIds)) {   //add template row if there are no inputs yet (otherwise template row is hidden)
                    continue;
                }
                if ($value == 'null') {
                    $optionsToDel[] = $id;
                    continue;
                }

                $optionData = [
                    "caption" => $form->getHttpData()["option_caption_$id"] ?? null,
                    "type" => $form->getHttpData()["option_type_$id"] ?? null,
                ];

                try {
                    in_array($id, $existingOptionIds) ?
                            $this->optionManager->update($optionData, $poll->getId(), $id) :
                            $this->optionManager->create($optionData, $poll->getId());
                } catch (TymyResponse $tResp) {
                    $this->respondByTymyResponse($tResp);
                }
            }
        }

        if (!empty($optionsToDel)) {
            try {
                $this->optionManager->deleteOptions($poll->getId(), $optionsToDel);
            } catch (TymyResponse $tResp) {
                $this->respondByTymyResponse($tResp);
            }
        }

        $this->redirect(':Setting:Poll:', [$poll->getWebName()]);
    }
}
