<?php

namespace Tymy\Module\Event\Presenter\Api;

use Nette\DI\Attributes\Inject;
use Nette\Utils\DateTime;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tymy\Module\Attendance\Manager\StatusManager;
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
    private int $xlsCol = 1;
    private int $xlsRow = 1;
    private int $lastRow = 1;
    private int $lastCol = 1;
    private array $headingStyle;
    private Worksheet $sheet;
    private DateTime $now;

    #[Inject]
    public StatusManager $statusManager;

    public function renderExport(string $year, string $page)
    {
        if ($this->getRequest()->getMethod() != 'GET') {
            $this->respondNotAllowed();
        }

        $events = $this->eventManager->getYearEvents($this->user->getId(), $year, $page);
        $users = $this->userManager->getByStatus(User::STATUS_PLAYER);
        $this->now = new DateTime();

        $lightGray = "FFEEEEEE";
        $this->headingStyle = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOTTED,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => $lightGray,
                ],
                'endColor' => [
                    'argb' => $lightGray,
                ],
            ],
        ];

        $this->exportSpreadsheet("report-event-$year-$page.xlsx", $users, $events["events"]);
    }

    private function exportSpreadsheet(string $filename, array $users, array $events): void
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(7);

        $this->sheet = new Worksheet(null, "Event report");
        $spreadsheet->addSheet($this->sheet);
        $this->sheet->getPageMargins()->setTop(1)->setRight(0)->setLeft(0)->setBottom(1);

        //add headers and footers
        $this->addHeaderFooter();

        //add table heading
        $this->cell()->setValue("Hráči");
        $this->cell()->getStyle()->getAlignment()->setHorizontal('center');
        $this->cell()->getStyle()->getFont()->setBold(true);
        $this->sheet->mergeCells("{$this->cell()->getCoordinate()}:{$this->cellBelow()->getCoordinate()}");

        $this->addHeadings($events);

        $this->addData($users, $events);

        $this->output($filename, $spreadsheet);
    }

    private function addHeadings(array $events)
    {
        foreach ($events as $event) {
            $this->nextCol();
            assert($event instanceof Event);
            $this->cell()->setValue($event->getCaption() . "\n" . $event->getStartTime()->format(BaseModel::DATE_CZECH_FORMAT));
            $this->cellBelow()->setValue("Plán");

            $this->cell()->getStyle()->applyFromArray($this->headingStyle);
            $this->cellBelow()->getStyle()->applyFromArray($this->headingStyle);

            $cell1 = $this->cell()->getCoordinate();
            $this->nextCol();
            $this->cellBelow()->setValue("Výsledek");
            $this->cell()->getStyle()->applyFromArray($this->headingStyle);
            $this->cellBelow()->getStyle()->applyFromArray($this->headingStyle);
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

    private function addHeaderFooter(): void
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

    private function addData(array $users, array $events)
    {
        $statusList = $this->statusManager->getIdList();

        foreach ($users as $usr) {
            $this->nextRow();

            /* @var $usr User */
            //first col is username
            $this->cell()->setValue($usr->getDisplayName());
            $this->cell()->getStyle()->applyFromArray($this->headingStyle);

            foreach ($events as $event) {
                $this->nextCol();

                $userAttendance = $event->getAttendance()[$usr->getId()] ?? null;
                $preStatus = $postStatus = null;
                if ($userAttendance){
                    /* @var $preStatus \Tymy\Module\Attendance\Model\Status */
                    $preStatus = $statusList[$userAttendance->getPreStatusId()] ?? null;
                    /* @var $postStatus \Tymy\Module\Attendance\Model\Status */
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

    private function nextCol(): void
    {
        $this->xlsCol++;
        if ($this->xlsCol > $this->lastCol) {
            $this->lastCol = $this->xlsCol;
        }
    }

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
