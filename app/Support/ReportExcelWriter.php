<?php

namespace App\Support;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\BorderName;
use OpenSpout\Common\Entity\Style\BorderStyle;
use OpenSpout\Common\Entity\Style\BorderWidth;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Menulis laporan sebagai XLSX bergaya tabel: header berwarna, border, baris
 * belang, header beku, filter per kolom, dan lebar kolom mengikuti isi.
 *
 * CSV sengaja tidak dipakai untuk ini — CSV hanyalah teks polos dan secara
 * format memang tidak bisa menyimpan lebar kolom, border, atau warna apa pun.
 */
final class ReportExcelWriter
{
    private const MAROON = '861D1D';

    private const ZEBRA = 'F7F1F1';

    private const GRID = 'D8CACA';

    /** Baris tabel dimulai di baris ke-4 (1 judul, 2 keterangan, 3 jarak). */
    private const HEADER_ROW = 4;

    /** Batas lebar kolom (satuan karakter) agar tidak terlalu sempit/melebar. */
    private const MIN_WIDTH = 12.0;

    private const MAX_WIDTH = 46.0;

    /**
     * @param  list<string>       $headers
     * @param  list<list<string>> $rows
     */
    public static function write(string $path, string $title, string $subtitle, array $headers, array $rows): void
    {
        $lastColumn = max(0, count($headers) - 1);
        $columnCount = count($headers);

        $options = new Options;
        foreach (self::autofitWidths($headers, $rows) as $column => $width) {
            $options->setColumnWidth($width, $column);
        }

        // Judul & keterangan dibentangkan selebar tabel.
        $options->mergeCells(0, 1, $lastColumn, 1);
        $options->mergeCells(0, 2, $lastColumn, 2);

        $writer = new Writer($options);
        $writer->openToFile($path);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Laporan');

        // Header tetap terlihat saat di-scroll.
        $sheet->setSheetView(new SheetView(freezeRow: self::HEADER_ROW + 1));

        // Tombol filter/sort di tiap kolom header.
        $sheet->setAutoFilter(new AutoFilter(0, self::HEADER_ROW, $lastColumn, self::HEADER_ROW + count($rows)));

        $writer->addRow(self::spanned($title, $columnCount, new Style(
            fontBold: true,
            fontSize: 16,
            fontColor: self::MAROON,
            cellVerticalAlignment: CellVerticalAlignment::CENTER,
        ), 26.0));

        $writer->addRow(self::spanned($subtitle, $columnCount, new Style(
            fontSize: 10,
            fontColor: '6B6060',
            cellVerticalAlignment: CellVerticalAlignment::CENTER,
        ), 16.0));

        $writer->addRow(Row::fromValues(array_fill(0, $columnCount, '')));

        $writer->addRow(Row::fromValuesWithStyle($headers, new Style(
            fontBold: true,
            fontColor: 'FFFFFF',
            cellAlignment: CellAlignment::CENTER,
            cellVerticalAlignment: CellVerticalAlignment::CENTER,
            border: self::border(self::MAROON),
            backgroundColor: self::MAROON,
        ), 22.0));

        foreach (array_values($rows) as $index => $row) {
            $writer->addRow(Row::fromValuesWithStyle($row, new Style(
                fontSize: 11,
                cellVerticalAlignment: CellVerticalAlignment::CENTER,
                border: self::border(self::GRID),
                backgroundColor: $index % 2 === 1 ? self::ZEBRA : null,
            ), 18.0));
        }

        $writer->close();
    }

    /**
     * Lebar kolom mengikuti isi terpanjang — setara hasil "autofit" di Excel,
     * karena XLSX menyimpan lebar sebagai angka, bukan instruksi "sesuaikan".
     *
     * @return array<int, float> nomor kolom (1-indexed) => lebar
     */
    private static function autofitWidths(array $headers, array $rows): array
    {
        $widths = [];

        foreach (array_values($headers) as $i => $header) {
            $longest = mb_strlen((string) $header);

            foreach ($rows as $row) {
                $longest = max($longest, mb_strlen((string) (array_values($row)[$i] ?? '')));
            }

            // +5 karakter: padding sel + ruang untuk tombol filter di header.
            $widths[$i + 1] = min(self::MAX_WIDTH, max(self::MIN_WIDTH, $longest + 5));
        }

        return $widths;
    }

    private static function border(string $color): Border
    {
        return new Border(
            new BorderPart(BorderName::TOP, $color, BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::RIGHT, $color, BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::BOTTOM, $color, BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::LEFT, $color, BorderWidth::THIN, BorderStyle::SOLID),
        );
    }

    /** Sel yang di-merge tetap butuh sel pendamping kosong di kanannya. */
    private static function spanned(string $value, int $columnCount, Style $style, float $height): Row
    {
        return Row::fromValuesWithStyle(
            array_pad([$value], max(1, $columnCount), ''),
            $style,
            $height
        );
    }
}
