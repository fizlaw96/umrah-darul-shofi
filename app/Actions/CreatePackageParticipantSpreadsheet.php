<?php

namespace App\Actions;

use App\Models\Package;
use App\Models\PackageDeparture;
use App\Models\PackageParticipant;
use RuntimeException;
use Throwable;
use ZipArchive;

class CreatePackageParticipantSpreadsheet
{
    public function handle(Package $package): string
    {
        $worksheetPath = tempnam(sys_get_temp_dir(), 'package-participants-sheet-');

        if ($worksheetPath === false) {
            throw new RuntimeException('Unable to create the participant worksheet.');
        }

        $spreadsheetPath = null;

        try {
            $this->writeWorksheet(
                $worksheetPath,
                $package,
                $this->departureDates($package),
                $this->participantColumnWidths($package),
            );

            $spreadsheetPath = tempnam(sys_get_temp_dir(), 'package-participants-');

            if ($spreadsheetPath === false) {
                throw new RuntimeException('Unable to create the participant spreadsheet.');
            }

            $archive = new ZipArchive;

            if ($archive->open($spreadsheetPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Unable to package the participant spreadsheet.');
            }

            $archive->addFromString('[Content_Types].xml', $this->contentTypesXml());
            $archive->addFromString('_rels/.rels', $this->rootRelationshipsXml());
            $archive->addFromString('docProps/app.xml', $this->appPropertiesXml());
            $archive->addFromString('docProps/core.xml', $this->corePropertiesXml());
            $archive->addFromString('xl/workbook.xml', $this->workbookXml());
            $archive->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
            $archive->addFromString('xl/styles.xml', $this->stylesXml());
            $archive->addFile($worksheetPath, 'xl/worksheets/sheet1.xml');

            if (! $archive->close()) {
                throw new RuntimeException('Unable to finalise the participant spreadsheet.');
            }

            return $spreadsheetPath;
        } catch (Throwable $exception) {
            if ($spreadsheetPath !== null && file_exists($spreadsheetPath)) {
                unlink($spreadsheetPath);
            }

            throw $exception;
        } finally {
            unlink($worksheetPath);
        }
    }

    private function departureDates(Package $package): string
    {
        return $package->departures()
            ->orderBy('departure_date')
            ->get()
            ->map(fn (PackageDeparture $departure): string => $departure->departure_date->format('d M Y'))
            ->join(', ') ?: 'Belum ditetapkan';
    }

    /** @return array{A: int, B: int, C: int, D: int} */
    private function participantColumnWidths(Package $package): array
    {
        $widths = [
            'A' => 8,
            'B' => $this->columnWidth('Nama peserta', 18, 50),
            'C' => $this->columnWidth('Telefon', 14, 25),
            'D' => $this->columnWidth('Catatan', 24, 60),
        ];

        $package->participants()
            ->orderBy('full_name')
            ->lazy(200)
            ->each(function (PackageParticipant $participant) use (&$widths): void {
                $widths['B'] = max($widths['B'], $this->columnWidth($participant->full_name, 18, 50));
                $widths['C'] = max($widths['C'], $this->columnWidth($participant->phone, 14, 25));
                $widths['D'] = max($widths['D'], $this->columnWidth($participant->notes, 24, 60));
            });

        return $widths;
    }

    private function columnWidth(?string $value, int $minimum, int $maximum): int
    {
        return min($maximum, max($minimum, mb_strlen((string) $value) + 4));
    }

    /** @param array{A: int, B: int, C: int, D: int} $columnWidths */
    private function writeWorksheet(string $worksheetPath, Package $package, string $departureDates, array $columnWidths): void
    {
        $worksheet = fopen($worksheetPath, 'wb');

        if ($worksheet === false) {
            throw new RuntimeException('Unable to write the participant worksheet.');
        }

        try {
            fwrite($worksheet, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>');
            fwrite($worksheet, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">');
            fwrite($worksheet, '<dimension ref="A1:D5"/>');
            fwrite($worksheet, '<sheetViews><sheetView workbookViewId="0" showGridLines="0"><pane ySplit="5" topLeftCell="A6" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A6" sqref="A6"/></sheetView></sheetViews>');
            fwrite($worksheet, '<sheetFormatPr defaultRowHeight="18"/>');
            fwrite($worksheet, sprintf('<cols><col min="1" max="1" width="%d" bestFit="1" customWidth="1"/><col min="2" max="2" width="%d" bestFit="1" customWidth="1"/><col min="3" max="3" width="%d" bestFit="1" customWidth="1"/><col min="4" max="4" width="%d" bestFit="1" customWidth="1"/></cols>', $columnWidths['A'], $columnWidths['B'], $columnWidths['C'], $columnWidths['D']));
            fwrite($worksheet, '<sheetData>');
            fwrite($worksheet, $this->row(1, ['B' => ['Senarai Peserta', 1]], 20));
            fwrite($worksheet, $this->row(2, ['B' => [$package->title, 2]], 30));
            fwrite($worksheet, $this->row(3, ['B' => ['Tarikh penerbangan', 3], 'C' => [$departureDates, 2]], 30));
            fwrite($worksheet, '<row r="4" ht="8" customHeight="1"/>');
            fwrite($worksheet, $this->row(5, ['A' => ['Bil.', 5], 'B' => ['Nama peserta', 5], 'C' => ['Telefon', 5], 'D' => ['Catatan', 5]], 22));

            $rowNumber = 6;
            $participantNumber = 1;

            $package->participants()
                ->orderBy('full_name')
                ->lazy(200)
                ->each(function (PackageParticipant $participant) use ($worksheet, &$rowNumber, &$participantNumber): void {
                    fwrite($worksheet, $this->row($rowNumber, [
                        'A' => [(string) $participantNumber, 6],
                        'B' => [$participant->full_name, 6],
                        'C' => [$participant->phone ?: '—', 6],
                        'D' => [$participant->notes ?: '—', 6],
                    ]));

                    $rowNumber++;
                    $participantNumber++;
                });

            fwrite($worksheet, '</sheetData>');
            fwrite($worksheet, '<mergeCells count="3"><mergeCell ref="B1:D1"/><mergeCell ref="B2:D2"/><mergeCell ref="C3:D3"/></mergeCells>');
            fwrite($worksheet, sprintf('<autoFilter ref="A5:D%d"/>', max(5, $rowNumber - 1)));
            fwrite($worksheet, '<pageMargins left="0.35" right="0.35" top="0.6" bottom="0.6" header="0.3" footer="0.3"/>');
            fwrite($worksheet, '</worksheet>');
        } finally {
            fclose($worksheet);
        }
    }

    /** @param array<string, array{0: string, 1: int}> $cells */
    private function row(int $rowNumber, array $cells, ?int $height = null): string
    {
        $attributes = $height === null
            ? sprintf('r="%d"', $rowNumber)
            : sprintf('r="%d" ht="%d" customHeight="1"', $rowNumber, $height);
        $cellsXml = '';

        foreach ($cells as $column => [$value, $style]) {
            $cellsXml .= $this->cell($column.$rowNumber, $value, $style);
        }

        return sprintf('<row %s>%s</row>', $attributes, $cellsXml);
    }

    private function cell(string $reference, string $value, int $style): string
    {
        $value = $this->safeSpreadsheetValue($value);
        $space = trim($value) === $value ? '' : ' xml:space="preserve"';

        return sprintf(
            '<c r="%s" s="%d" t="inlineStr"><is><t%s>%s</t></is></c>',
            $reference,
            $style,
            $space,
            htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8'),
        );
    }

    private function safeSpreadsheetValue(string $value): string
    {
        if (preg_match('/^[=+\-@]/', $value) === 1) {
            return "'{$value}";
        }

        return $value;
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';
    }

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>';
    }

    private function appPropertiesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Laravel</Application></Properties>';
    }

    private function corePropertiesXml(): string
    {
        $createdAt = now()->utc()->format('Y-m-d\\TH:i:s\\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:creator>SalamHaji Travel &amp; Tours</dc:creator><cp:lastModifiedBy>SalamHaji Travel &amp; Tours</cp:lastModifiedBy><dcterms:created xsi:type="dcterms:W3CDTF">'.$createdAt.'</dcterms:created><dcterms:modified xsi:type="dcterms:W3CDTF">'.$createdAt.'</dcterms:modified></cp:coreProperties>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><bookViews><workbookView xWindow="0" yWindow="0" windowWidth="24000" windowHeight="12000"/></bookViews><sheets><sheet name="Senarai Peserta" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="5"><font><sz val="11"/><color theme="1"/><name val="Arial"/><family val="2"/></font><font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Arial"/><family val="2"/></font><font><b/><sz val="18"/><color rgb="FF00251E"/><name val="Arial"/><family val="2"/></font><font><b/><sz val="12"/><color rgb="FFFFFFFF"/><name val="Arial"/><family val="2"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Arial"/><family val="2"/></font></fonts><fills count="5"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF006B57"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE9F7ED"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF7F1DF"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFE0E7E3"/></left><right style="thin"><color rgb="FFE0E7E3"/></right><top style="thin"><color rgb="FFE0E7E3"/></top><bottom style="thin"><color rgb="FFE0E7E3"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="7"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf><xf numFmtId="0" fontId="2" fillId="3" borderId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf><xf numFmtId="0" fontId="3" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf><xf numFmtId="0" fontId="0" fillId="4" borderId="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf><xf numFmtId="0" fontId="4" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles><tableStyles count="0" defaultTableStyle="TableStyleMedium2" defaultPivotStyle="PivotStyleLight16"/></styleSheet>';
    }
}
