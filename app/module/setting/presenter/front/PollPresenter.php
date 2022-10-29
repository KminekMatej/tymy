<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
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
    /** @inject */
    public PollManager $pollManager;

    /** @inject */
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
                $poll->getCreatedAt()->format(BaseModel::DATE_CZECH_FORMAT) . ", " . $this->userManager->getById($poll->getCreatedById())->getDisplayName(),
            ]);
        }

        $this->template->polls = $polls;
    }

    public function renderNew(): void
    {
        $this->allowPermission('ASK.VOTE_UPDATE');

        $this->addBreadcrumb($this->translator->translate("poll.new"));
    }

    public function renderPoll(?string $resource = null): void
    {
        $this->allowPermission('ASK.VOTE_UPDATE');

        //RENDERING POLL DETAIL
        $pollId = $this->parseIdFromWebname($resource);
        $this->poll = $this->pollManager->getById($pollId);
        if ($this->poll == null) {
            $this->flashMessage($this->translator->translate("poll.errors.pollNotExists", null, ['id' => $pollId]), "danger");
            $this->redirect(':Setting:Poll:');
        }
        $this->addBreadcrumb($this->poll->getCaption(), $this->link(":Setting:Poll:", $this->poll->getWebName()));
        $this->template->poll = $this->poll;
    }

    public function handlePollDelete(string $resource): void
    {
        $pollId = $this->parseIdFromWebname($resource);
        $this->pollManager->delete($pollId);
        $this->redirect(':Setting:Poll:');
    }

    public function createComponentPollForm(): Form
    {
        $pollId = $this->parseIdFromWebname($this->getRequest()->getParameter("resource"));
        return $this->formFactory->createPollConfigForm(fn(Form $form, stdClass $values) => $this->pollFormSuccess($form, $values), ($pollId ? $this->pollManager->getById($pollId) : null));
    }

    public function pollFormSuccess(Form $form, stdClass $values): void
    {
        $poll = $values->id ?
            $this->pollManager->update((array) $values, $values->id) :
            $this->pollManager->create((array) $values);
        assert($poll instanceof Poll);

        $existingOptionIds = ArrayHelper::entityIds($poll->getOptions());
        $optionsToDel = [];

        foreach ($form->getHttpData() as $name => $value) { //get options from http data instead of $values to read also dynamically adde option rows
            if (preg_match('/option_id_(.+)/m', $name, $matches)) {
                $id = $matches[1];

                if ($id === '0') {   //skip template row
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

                in_array($id, $existingOptionIds) ?
                        $this->optionManager->update($optionData, $poll->getId(), $id) :
                        $this->optionManager->create($optionData, $poll->getId());
            }
        }

        if (!empty($optionsToDel)) {
            $this->optionManager->deleteOptions($poll->getId(), $optionsToDel);
        }

        $this->redirect(':Setting:Poll:', [$poll->getWebName()]);
    }
}
