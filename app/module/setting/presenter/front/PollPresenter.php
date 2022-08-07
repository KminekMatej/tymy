<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Core\Helper\ArrayHelper;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Cell;
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

    public function actionDefault(?string $resource = null)
    {
        if ($resource) {
            $this->setView("poll");
        }
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("poll.poll", 2), $this->link(":Setting:Poll:"));
    }

    public function renderDefault()
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
        foreach ($this->pollManager->getList() as $poll) {
            /* @var $poll Poll */
            $this->template->rows[] = [
                Cell::detail($this->link(":Setting:Poll:", [$poll->getWebName()])),
                $poll->getId(),
                $poll->getCaption(),
                $poll->getDescription(),
                $this->translator->translate("poll." . strtolower($poll->getStatus())),
                $poll->getCreatedAt()->format(BaseModel::DATE_CZECH_FORMAT) . ", " . $this->userManager->getById($poll->getCreatedById())->getDisplayName(),
            ];
        }

        $this->template->polls = $this->pollManager->getList();
    }

    public function renderNew()
    {
        $this->allowPermission('ASK.VOTE_UPDATE');

        $this->addBreadcrumb($this->translator->translate("poll.new"));
    }

    public function renderPoll(?string $resource = null)
    {
        $this->allowPermission('ASK.VOTE_UPDATE');

        //RENDERING POLL DETAIL
        $pollId = $this->parseIdFromWebname($resource);
        /* @var $this->poll Poll */
        $this->poll = $this->pollManager->getById($pollId);
        if ($this->poll == null) {
            $this->flashMessage($this->translator->translate("poll.errors.pollNotExists", null, ['id' => $pollId]), "danger");
            $this->redirect(':Setting:Poll:');
        }
        $this->addBreadcrumb($this->poll->getCaption(), $this->link(":Setting:Poll:", $this->poll->getWebName()));
        $this->template->poll = $this->poll;
    }

    public function handlePollDelete(string $resource)
    {
        $pollId = $this->parseIdFromWebname($resource);
        $this->pollManager->delete($pollId);
        $this->redirect(':Setting:Poll:');
    }

    public function createComponentPollForm(): Form
    {
        $pollId = $this->parseIdFromWebname($this->getRequest()->getParameter("resource"));
        return $this->formFactory->createPollConfigForm([$this, "pollFormSuccess"], ($pollId ? $this->pollManager->getById($pollId) : null));
    }

    /**
     * @param Form $form
     * @param stdClass $values
     * @return void
     */
    public function pollFormSuccess(Form $form, $values): void
    {
        /* @var $poll Poll */
        $poll = $values->id ?
            $this->pollManager->update((array) $values, $values->id) :
            $this->pollManager->create((array) $values);

        $existingOptionIds = ArrayHelper::entityIds($poll->getOptions());

        foreach ($form->getHttpData() as $name => $value) { //get options from http data instead of $values to read also dynamically adde option rows
            if (preg_match('/option_caption_(\d+)/m', $name, $matches)) {
                $id = $matches[1];
                if (in_array($id, $existingOptionIds)) { //existing option
                    $this->optionManager->update([
                        "caption" => $value,
                        "type" => $form->getHttpData()["option_type_$id"] ?? null,
                        ], $values->id ?? null, $id);
                } else { //non-existing option, create
                    $this->optionManager->create([
                        "caption" => $value,
                        "type" => $form->getHttpData()["option_type_$id"] ?? null,
                        ], $values->id ?? null);
                }
            }
        }

        $this->redirect(':Setting:Poll:', [$poll->getWebName()]);
    }
}
