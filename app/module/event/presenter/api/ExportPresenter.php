<?php

namespace Tymy\Module\Event\Presenter\Api;

use Nette\DI\Attributes\Inject;
use Nette\Utils\DateTime;
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
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Core\Response\FileContentResponse;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\User\Model\User;

/**
 * Description of ReportPresenter
 *
 * @author kminekmatej, 5. 1. 2023, 21:27:25
 */
class ReportPresenter extends SecuredPresenter
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
    private Worksheet $sheet;
    private DateTime $now;

    #[Inject]
    public StatusManager $statusManager;

    protected function startup(): void
    {
        parent::startup();
        $this->now = new DateTime();
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

        $sheet = new Worksheet(null, $this->translator->translate("event.attendanceView"));
        $spreadsheet->addSheet($sheet);
        $sheet->getPageMargins()->setTop(1)->setRight(0)->setLeft(0)->setBottom(1);

        //add headers and footers
        $this->addReportTitle();

        //add table heading
        $this->cell()->setValue($this->translator->translate("team.player", 2));
        $this->cell()->getStyle()->getAlignment()->setHorizontal('center');
        $this->cell()->getStyle()->getFont()->setBold(true);
        $sheet->mergeCells("{$this->cell()->getCoordinate()}:{$this->cellBelow()->getCoordinate()}");

        $this->addReportHeadings($sheet, $events);

        $this->addReportData($users, $events);

        $this->output($filename, $spreadsheet);
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

            $ths->sheet->mergeCells("$cell1:$cell2");
        }

        $ths->sheet->getRowDimension($this->xlsRow)->setRowHeight(40);

        $lastRowCell = $ths->sheet->getCell(Coordinate::stringFromColumnIndex($this->xlsCol) . "2");

        $this->nextRow();

        $ths->sheet->setAutoFilter("A2:" . $lastRowCell->getCoordinate());
        for ($index = 1; $index < $this->lastCol; $index++) {
            $ths->sheet->getColumnDimensionByColumn($index)->setAutoSize(true);
        }
    }

    private function addReportTitle(): void
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
        $statusList = $this->statusManager->getIdList();

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
                    $preStatus = $statusList[$userAttendance->getPreStatusId()] ?? null;
                    /* @var $postStatus Status */
                    $postStatus = $statusList[$userAttendance->getPostStatusId()] ?? null;
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
