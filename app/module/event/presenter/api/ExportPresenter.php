<?php

namespace Tymy\Module\Event\Presenter\Api;

use Nette\DI\Attributes\Inject;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Core\Response\FileContentResponse;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\User\Model\User;

/**
 * Description of ExportPresenter
 *
 * @author kminekmatej, 5. 1. 2023, 21:27:25
 */
class ExportPresenter extends SecuredPresenter
{
    private const LIGHTGRAY = "FFEEEEEE";
    private const HEADING_STYLE = [
        'font' => [
            'bold' => true,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
        ],
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_DOTTED,
            ],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => self::LIGHTGRAY,
            ],
            'endColor' => [
                'argb' => self::LIGHTGRAY,
            ],
        ],
    ];

    private int $xlsCol = 1;
    private int $xlsRow = 1;
    private int $lastRow = 1;
    private int $lastCol = 1;
    /** @var Status[] */
    private array $statusList;
    private Worksheet $sheet;
    private DateTime $now;

    #[Inject]
    public StatusManager $statusManager;

    #[Inject]
    public EventManager $eventManager;

    #[Inject]
    public EventTypeManager $eventTypeManager;

    public function startup(): void
    {
        parent::startup();
        $this->now = new DateTime();
        $this->statusList = $this->statusManager->getIdList();
    }

    public function actionReport(string $year, string $page)
    {
        if ($this->getRequest()->getMethod() != 'GET') {
            $this->respondNotAllowed();
        }

        $events = $this->eventManager->getYearEvents($this->user->getId(), $year, $page)["events"];
        $users = $this->userManager->getByStatus(User::STATUS_PLAYER);

        $filename = "report-event-$year-$page.xlsx";

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(7);

        $this->sheet = new Worksheet(null, $this->translator->translate("event.attendanceView"));
        $spreadsheet->addSheet($this->sheet);
        $this->sheet->getPageMargins()->setTop(1)->setRight(0)->setLeft(0)->setBottom(1);

        //add universal sheet title
        $this->addTitle();

        //add table heading
        $this->cell()->setValue($this->translator->translate("team.PLAYER", 2));
        $this->cell()->getStyle()->getAlignment()->setHorizontal('center');
        $this->cell()->getStyle()->getFont()->setBold(true);
        $this->sheet->mergeCells("{$this->cell()->getCoordinate()}:{$this->cellBelow()->getCoordinate()}");

        $this->addReportHeadings($events);

        $this->addReportData($users, $events);

        $this->output($filename, $spreadsheet);
    }

    public function actionDefault(string $from, string $until, ?int $type = null)
    {
        if ($this->getRequest()->getMethod() != 'GET') {
            $this->respondNotAllowed();
        }

        $events = $this->eventManager->getEventsInterval($this->user->getId(), new DateTime($from), new DateTime($until), $type);

        $eventType = null;
        if ($type) {
            $eventType = $this->eventTypeManager->getById($type);
            if (!$eventType instanceof EventType) {
                $this->respondBadRequest("Invalid event type id");
            }
        }

        $filename = ($eventType ? Strings::webalize($eventType->getCaption()) : "report") . "-$from-$until.xlsx";

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(9);

        $this->sheet = new Worksheet(null, $this->translator->translate("event.attendanceView"));
        $spreadsheet->addSheet($this->sheet);
        $this->sheet->getPageMargins()->setTop(1)->setRight(0)->setLeft(0)->setBottom(1);

        //add universal sheet title
        $this->addTitle();
        
        //columns: idUd - typ - nazevUd - startDt - user - plan - result
        //add column headings
        $this->addDefaultHeading();
        
        //add event data
        $this->addDefaultData($events);

        $this->output($filename, $spreadsheet);
    }

    /**
     * Add column headings
     * @return void
     */
    private function addDefaultHeading(): void
    {
        $this->cell()->setValue("ID")->getStyle()->applyFromArray(self::HEADING_STYLE);
        $this->nextCol();
        $this->cell()->setValue($this->translator->translate("event.eventType", 1))->getStyle()->applyFromArray(self::HEADING_STYLE);
        $this->nextCol();
        $this->cell()->setValue($this->translator->translate("event.event", 1))->getStyle()->applyFromArray(self::HEADING_STYLE);
        $this->nextCol();
        $this->cell()->setValue($this->translator->translate("event.start"))->getStyle()->applyFromArray(self::HEADING_STYLE);
        $this->nextCol();
        $this->cell()->setValue($this->translator->translate("team.PLAYER", 1))->getStyle()->applyFromArray(self::HEADING_STYLE);
        $this->nextCol();
        $this->cell()->setValue($this->translator->translate("event.plan"))->getStyle()->applyFromArray(self::HEADING_STYLE);
        $this->nextCol();
        $this->cell()->setValue($this->translator->translate("event.result"))->getStyle()->applyFromArray(self::HEADING_STYLE);

        $lastRowCell = $this->sheet->getCell(Coordinate::stringFromColumnIndex($this->xlsCol) . "1");
        $this->sheet->setAutoFilter("A1:" . $lastRowCell->getCoordinate());
        for ($index = 1; $index < $this->lastCol; $index++) {
            $this->sheet->getColumnDimensionByColumn($index)->setAutoSize(true);
        }

        $this->nextRow();
    }

    /**
     * Print attendances of found events to each line
     * @param array $events
     * @return void
     */
    private function addDefaultData(array $events): void
    {
        foreach ($events as $event) {
            foreach ($event->getAttendance() as $userId => $userAttendance) {
                /* @var $preStatus Status */
                $preStatus = $this->statusList[$userAttendance->getPreStatusId()] ?? null;
                /* @var $postStatus Status */
                $postStatus = $this->statusList[$userAttendance->getPostStatusId()] ?? null;

                if (empty($preStatus) && empty($postStatus)) {  //fill only rows where either plan or result is specified
                    continue;
                }

                /* @var $userAttendance Attendance */
                $this->cell()->setValue($event->getId());  //event id
                $this->nextCol();
                $this->cell()->setValue($event->getEventType()->getCaption());  //event type
                $this->nextCol();
                $this->cell()->setValue($event->getCaption());  //event name
                $this->nextCol();
                $this->cell()->setValue($event->getStartTime()->format(BaseModel::DATETIME_CZECH_FORMAT));  //event start time
                $this->nextCol();
                $this->cell()->setValue($userAttendance->getUser()->getCallName());  //user call name
                $this->nextCol();
                if ($preStatus) {
                    $this->cell()->setValue($preStatus->getCaption());  //plan
                    $this->cell()->getStyle()->getFont()->setColor(new Color($preStatus->getColor()));
                }
                $this->nextCol();
                if ($postStatus) {
                    $this->cell()->setValue($postStatus->getCaption());  //result
                    $this->cell()->getStyle()->getFont()->setColor(new Color($postStatus->getColor()));
                }
                $this->nextRow();
            }
        }
    }

    /**
     * Add heading into the report sheet
     * @param array $events
     * @return void
     */
    private function addReportHeadings(array $events): void
    {
        foreach ($events as $event) {
            $this->nextCol();
            assert($event instanceof Event);
            $this->cell()->setValue($event->getCaption() . "\n" . $event->getStartTime()->format(BaseModel::DATE_CZECH_FORMAT));
            $this->cellBelow()->setValue($this->translator->translate("event.plan"));

            $this->cell()->getStyle()->applyFromArray(self::HEADING_STYLE);
            $this->cellBelow()->getStyle()->applyFromArray(self::HEADING_STYLE);

            $cell1 = $this->cell()->getCoordinate();
            $this->nextCol();
            $this->cellBelow()->setValue($this->translator->translate("event.result"));
            $this->cell()->getStyle()->applyFromArray(self::HEADING_STYLE);
            $this->cellBelow()->getStyle()->applyFromArray(self::HEADING_STYLE);
            $cell2 = $this->cell()->getCoordinate();

            $this->sheet->mergeCells("$cell1:$cell2");
        }

        $this->sheet->getRowDimension($this->xlsRow)->setRowHeight(40);

        $lastRowCell = $this->sheet->getCell(Coordinate::stringFromColumnIndex($this->xlsCol) . "2");

        $this->nextRow();

        $this->sheet->setAutoFilter("A2:" . $lastRowCell->getCoordinate());
        for ($index = 1; $index < $this->lastCol; $index++) {
            $this->sheet->getColumnDimensionByColumn($index)->setAutoSize(true);
        }
    }

    private function addTitle(): void
    {
        $headerMiddle = "&B{$this->team->getName()}";

        $footerLeft = "&{$this->tymyUser->getDisplayName()}, {$this->now->format(BaseModel::DATETIME_CZECH_FORMAT)}";
        $footerMiddle = "";
        $footerRight = "&B&P/&N";

        $header = "&LExport report&C{$headerMiddle}";
        $footer = "&L{$footerLeft}&C{$footerMiddle}&R{$footerRight}";

        $this->sheet->getHeaderFooter()->setOddHeader($header);
        $this->sheet->getHeaderFooter()->setEvenHeader($header);
        $this->sheet->getHeaderFooter()->setOddFooter($footer);
        $this->sheet->getHeaderFooter()->setEvenFooter($footer);
    }

    /**
     * Add event attendances data into report export
     * @param array $users
     * @param array $events
     */
    private function addReportData(array $users, array $events)
    {

        foreach ($users as $usr) {
            $this->nextRow();

            /* @var $usr User */
            //first col is username
            $this->cell()->setValue($usr->getDisplayName());
            $this->cell()->getStyle()->applyFromArray(self::HEADING_STYLE);

            foreach ($events as $event) {
                $this->nextCol();

                $userAttendance = $event->getAttendance()[$usr->getId()] ?? null;
                $preStatus = $postStatus = null;
                if ($userAttendance){
                    /* @var $preStatus Status */
                    $preStatus = $this->statusList[$userAttendance->getPreStatusId()] ?? null;
                    /* @var $postStatus Status */
                    $postStatus = $this->statusList[$userAttendance->getPostStatusId()] ?? null;
                }
                $this->cell()->setValue($preStatus ? $preStatus->getCaption() : '');
                if ($preStatus) {
                    $this->cell()->getStyle()->getFont()->setColor(new Color($preStatus->getColor()));
                }
                $this->nextCol();
                $this->cell()->setValue($postStatus ? $postStatus->getCaption() : '');
                if ($postStatus) {
                    $this->cell()->getStyle()->getFont()->setColor(new Color($postStatus->getColor()));
                }
            }
        }
    }

    /**
     * Increment current column coordinates
     * @return void
     */
    private function nextCol(): void
    {
        $this->xlsCol++;
        if ($this->xlsCol > $this->lastCol) {
            $this->lastCol = $this->xlsCol;
        }
    }

    /**
     * Increment current row and reset col to first one
     * @return void
     */
    private function nextRow(): void
    {
        $this->xlsRow++;
        if ($this->xlsRow > $this->lastRow) {
            $this->lastRow = $this->xlsRow;
        }
        $this->xlsCol = 1;
    }

    private function cell(): Cell
    {
        return $this->sheet->getCell(Coordinate::stringFromColumnIndex($this->xlsCol) . $this->xlsRow);
    }

    private function cellBelow(): Cell
    {
        return $this->sheet->getCell(Coordinate::stringFromColumnIndex($this->xlsCol) . ($this->xlsRow + 1));
    }

    private function output(string $filename, Spreadsheet $spreadsheet)
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $fileContent = ob_get_clean();

        $this->sendResponse(new FileContentResponse($fileContent, $filename));
    }
}
