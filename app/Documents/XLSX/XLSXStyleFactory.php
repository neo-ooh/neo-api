<?php

namespace Neo\Documents\XLSX;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class XLSXStyleFactory {
    public const COLORS = [
        "dark-blue" => "FF202035",
        "light-blue" => "FF0099F8",
        "shopping"  => "FF0099F8",
        "otg"       => "FFFF6300",
        "fitness"   => "FFF8002B",
    ];

    public static function networkSectionHeader(string $network) {
        return [
            'font'      => [
                'bold'  => true,
                'color' => [
                    'argb' => "FFFFFFFF"
                ],
                'size'  => "24",
                "name"  => "Calibri"
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                "wrapText" => true
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => static::COLORS[$network],
                ],
            ],
        ];
    }

    public static function tableHeader() {
        return [
            'font'      => [
                'bold'  => true,
                'color' => [
                    'argb' => "FFFFFFFF"
                ],
                'size'  => "12",
                "name"  => "Calibri"
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                "wrapText" => true
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => static::COLORS["dark-blue"],
                ],
            ],
        ];
    }

    public static function locationHeader() {
        return [
            'font'      => [
                'color' => [
                    'argb' => "FFFFFFFF"
                ],
                'size'  => "14",
                "name"  => "Calibri"
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                "wrapText" => true
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => static::COLORS["dark-blue"],
                ],
            ],
        ];
    }

    public static function tableBody() {
        return [
            'font'      => [
                'color' => [
                    'argb' => "FF000000"
                ],
                'size'  => "12",
                "name"  => "Calibri"
            ],
            "numberFormat" => [
                "formatCode" => '#,##0_-'
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                "wrapText" => true
            ],
            "borders"   => [
                "allBorders" => [
                    "borderStyle" => Border::BORDER_THIN,
                ]
            ]
        ];
    }

    public static function tableFooter(string $network) {
        return [
            'font'      => [
                'color' => [
                    'argb' => "FFFFFFFF"
                ],
                'size'  => "12",
                "name"  => "Calibri"
            ],
            "numberFormat" => [
                "formatCode" => '#,##0_-'
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                "wrapText" => true
            ],
            "borders"   => [
                "allBorders" => [
                    "borderStyle" => Border::BORDER_THIN,
                ]
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => static::COLORS[$network],
                ],
            ],
        ];
    }

    public static function networkFooter() {
        return [
            'font'      => [
                'color' => [
                    'argb' => "FFFFFFFF"
                ],
                'size'  => "12",
                "name"  => "Calibri"
            ],
            "numberFormat" => [
                "formatCode" => '#,##0_-'
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                "wrapText" => true
            ],
            "borders"   => [
                "allBorders" => [
                    "borderStyle" => Border::BORDER_THIN,
                ]
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => static::COLORS["dark-blue"],
                ],
            ],
        ];
    }

    public static function totals() {
        return [
            'font'      => [
                'bold'  => true,
                'color' => [
                    'argb' => "FFFFFFFF"
                ],
                'size'  => "14",
                "name"  => "Calibri"
            ],
            "numberFormat" => [
                "formatCode" => '#,##0_-'
            ],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_CENTER,
                "wrapText" => true
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => static::COLORS["dark-blue"],
                ],
            ],
        ];
    }

    public static function specsHeader($network = "dark-blue") {
        return [
            'font'      => [
                'bold'  => true,
                'color' => [
                    'argb' => static::COLORS[$network]
                ],
                'size'  => "14",
                "name"  => "Calibri"
            ],
            "numberFormat" => [
                "formatCode" => '#,##0_-'
            ],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_CENTER,
                "wrapText" => true
            ],
        ];
    }

    public static function flightRow() {
        return [
            'font'      => [
                'bold'  => true,
                'color' => [
                    'argb' => "FFFFFFFF"
                ],
                'size'  => "12",
                "name"  => "Calibri"
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                "wrapText" => true
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => static::COLORS["light-blue"],
                ],
            ],
        ];
    }
}
