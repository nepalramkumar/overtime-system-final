<?php

namespace App\Services;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Tab;
use App\Support\EmployeeSorter;

class OvertimeWordService
{
    // तपाईंको संस्थाको जानकारी — यहाँ बदल्नुहोस्
    private $orgNameLine1 = 'नेपाल';
    private $orgNameLine2 = 'चार्टर्ड एकाउन्टेन्ट्स संस्था';
    private $logoPath;

    // आन्तरिक व्यवस्थापन शाखा
    private $verifierName = 'कृष्णराम पौडेल';
    private $verifierPosition = 'अधिकृत';

    public function __construct()
    {
        $this->logoPath = public_path('images/logo.jpg');
    }

    protected function newDocument(): PhpWord
    {
        // XML special characters (&, <, > आदि सुरक्षित रूपमा escape गर्न
        Settings::setOutputEscapingEnabled(true);

        $phpWord = new PhpWord();

        $phpWord->setDefaultFontName('Nirmala UI');
        $phpWord->setDefaultFontSize(10);

        $phpWord->setDefaultParagraphStyle([
            'lineHeight' => 1.0,
            'spacing' => 0,
        ]);

        return $phpWord;
    }

    // ==========================================
    // Letterhead: Logo + संस्थाको नाम + divider line
    // ==========================================
    protected function addLetterhead($section)
    {
        $table = $section->addTable(['cellMargin' => 0]);
        $table->addRow();

        $cell = $table->addCell(9000, [
            'borderBottomSize' => 4,
            'borderBottomColor' => 'C8102E',
        ]);

        if (file_exists($this->logoPath)) {
            $cell->addImage($this->logoPath, [
                'width' => 200,
                'height' => 90
            ]);
        }
    }

    // ==========================================
    // Title Block
    // ==========================================
    protected function addTitleBlock(
        $section,
        $bsDate,
        $approver = null,
        $printedBy = null
    ) {
        $section->addText(
            'आन्तरिक मेमो',
            [
                'bold' => true,
                'underline' => 'single',
                'size' => 11
            ],
            [
                'alignment' => Jc::CENTER
            ]
        );

        $section->addText(
            'अतिरिक्त समय कार्य गरेको प्रमाणित फाराम',
            [
                'bold' => true,
                'size' => 13
            ],
            [
                'alignment' => Jc::CENTER
            ]
        );

        $section->addTextBreak(1);

        $section->addText(
            'मिति: ' . $bsDate,
            [],
            [
                'alignment' => Jc::RIGHT
            ]
        );

        $section->addTextBreak(1);

        $fromDepartment = $printedBy->department ?? '...................';
        $toDepartment = $approver->department ?? '...................';

        $section->addText(
            'बाटः ' . $fromDepartment
        );

        $section->addText(
            'लाईः श्री ' . $toDepartment . ' निर्देशनालय प्रमुख ज्यू,'
        );

        $section->addTextBreak(1);
    }

    // ==========================================
    // Group Introduction
    //
    // अब departmentName र programName दुवै लिन्छ
    // ==========================================
    protected function addIntroGroup(
        $section,
        $departmentName,
        $programName
    ) {
        // Fallback
        $departmentName = $departmentName ?: '';
        $programName = $programName ?: '';

        $section->addText(
            'देहाय बमोजिमका कर्मचारीहरुले ' .
            $departmentName .
            'को कार्यक्रम ' .
            $programName .
            ' का लागि नियमानुसार आवश्यक पूर्व स्वीकृती लिई उल्लेखित विवरण बमोजिम अतिरिक्त समय कार्य गरेकोले सोको प्रमाणितको लागी पेश गरेको छु।',
            [],
            [
                'alignment' => Jc::BOTH
            ]
        );

        $section->addTextBreak(1);
    }

    // ==========================================
    // Individual Introduction
    // ==========================================
    protected function addIntroIndividual($section)
    {
        $section->addText(
            'मैले निम्न बमोजिमको कार्य गर्नकोका लागि नियमानुसार आवश्यक पूर्व स्वीकृती लिई उल्लेखित विवरण बमोजिम अतिरिक्त समय कार्य गरेकोले सोको प्रमाणितको लागी पेश गरेको छु।',
            [],
            [
                'alignment' => Jc::BOTH
            ]
        );

        $section->addTextBreak(1);
    }

    // ==========================================
    // Records लाई employee + लगातार मिति अनुसार
    // Consolidate गर्ने
    // ==========================================
    protected function consolidateRanges($records)
    {
        // पहिले employee+date अनुसार group
        $byEmployeeDate = [];

        foreach ($records as $rec) {

            $key = $rec->employee_id . '|' . $rec->ot_date;

            if (!isset($byEmployeeDate[$key])) {

                $byEmployeeDate[$key] = [
                    'employee' => $rec->employee,
                    'date' => $rec->ot_date,
                    'hours' => 0,
                    'tiffin' => 0,
                    'label' => $rec->event->event_name
                        ?? ($rec->purpose->name
                        ?? ($rec->remarks ?: 'सामान्य')),
                ];
            }

            $byEmployeeDate[$key]['hours'] += $rec->total_hours;
            $byEmployeeDate[$key]['tiffin'] += $rec->tiffin_amount;
        }

        // Employee अनुसार grouped array
        $byEmployee = [];

        foreach ($byEmployeeDate as $row) {

            $empId = $row['employee']->id ?? 0;

            $byEmployee[$empId]['employee'] = $row['employee'];
            $byEmployee[$empId]['days'][] = $row;
        }

        $consolidated = [];

        foreach ($byEmployee as $group) {

            $days = collect($group['days'])
                ->sortBy('date')
                ->values();

            $rangeStart = null;
            $rangeEnd = null;
            $rangeHours = 0;
            $rangeTiffin = 0;
            $rangeLabel = null;
            $prevDate = null;

            foreach ($days as $day) {

                $currentDate = \Carbon\Carbon::parse($day['date']);

                if ($rangeStart === null) {

                    $rangeStart = $day['date'];
                    $rangeEnd = $day['date'];
                    $rangeHours = $day['hours'];
                    $rangeTiffin = $day['tiffin'];
                    $rangeLabel = $day['label'];

                } elseif (
                    $prevDate &&
                    $currentDate->diffInDays($prevDate) == 1 &&
                    $day['label'] == $rangeLabel
                ) {

                    // लगातार मिति, उही label
                    $rangeEnd = $day['date'];
                    $rangeHours += $day['hours'];
                    $rangeTiffin += $day['tiffin'];

                } else {

                    // Range टुट्यो
                    $consolidated[] = [
                        'employee' => $group['employee'],
                        'from' => $rangeStart,
                        'to' => $rangeEnd,
                        'hours' => $rangeHours,
                        'tiffin' => $rangeTiffin,
                        'label' => $rangeLabel,
                    ];

                    $rangeStart = $day['date'];
                    $rangeEnd = $day['date'];
                    $rangeHours = $day['hours'];
                    $rangeTiffin = $day['tiffin'];
                    $rangeLabel = $day['label'];
                }

                $prevDate = $currentDate;
            }

            if ($rangeStart !== null) {

                $consolidated[] = [
                    'employee' => $group['employee'],
                    'from' => $rangeStart,
                    'to' => $rangeEnd,
                    'hours' => $rangeHours,
                    'tiffin' => $rangeTiffin,
                    'label' => $rangeLabel,
                ];
            }
        }

        return $consolidated;
    }

    // ==========================================
    // Individual Table
    // ==========================================
    protected function addIndividualTable($section, $records)
    {
        // Position-hierarchy sort: level DESC, उस्तै level भए employee_code natural order
        $records = EmployeeSorter::sort(collect($records));

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80
        ];

        $table = $section->addTable($tableStyle);

        $table->addRow();

        $headers = [
            'मिति',
            'देखि - सम्म',
            'घण्टा',
            'मिनेटलाई घण्टामा',
            'खाजा',
            'काम/Purpose'
        ];

        foreach ($headers as $h) {

            $table
                ->addCell(1400)
                ->addText(
                    $h,
                    ['bold' => true],
                    ['alignment' => Jc::CENTER]
                );
        }

        $totalHours = 0;
        $totalTiffin = 0;

        foreach ($records as $rec) {

            $wholeHours = floor($rec->total_hours);

            $minutes = round(
                ($rec->total_hours - $wholeHours) * 60
            );

            $table->addRow();

            $table
                ->addCell(1400)
                ->addText(adToBs($rec->ot_date));

            $table
                ->addCell(1400)
                ->addText(
                    substr($rec->from_time, 0, 5) .
                    ' - ' .
                    substr($rec->to_time, 0, 5)
                );

            $table
                ->addCell(1400)
                ->addText(
                    $wholeHours .
                    ':' .
                    str_pad(
                        $minutes,
                        2,
                        '0',
                        STR_PAD_LEFT
                    ),
                    [],
                    ['alignment' => Jc::CENTER]
                );

            $table
                ->addCell(1400)
                ->addText(
                    number_format($rec->total_hours, 2),
                    [],
                    ['alignment' => Jc::CENTER]
                );

            $table
                ->addCell(1400)
                ->addText(
                    number_format($rec->tiffin_amount, 2),
                    [],
                    ['alignment' => Jc::CENTER]
                );

            $table
                ->addCell(1400)
                ->addText(
                    $rec->purpose->name
                    ?? ($rec->remarks ?: '-')
                );

            $totalHours += $rec->total_hours;
            $totalTiffin += $rec->tiffin_amount;
        }

        $table->addRow();

        $table
            ->addCell(1400)
            ->addText(
                'जम्मा',
                ['bold' => true]
            );

        $table->addCell(1400);

        $table
            ->addCell(
                2800,
                ['gridSpan' => 2]
            )
            ->addText(
                number_format($totalHours, 2),
                ['bold' => true],
                ['alignment' => Jc::CENTER]
            );

        $table
            ->addCell(1400)
            ->addText(
                number_format($totalTiffin, 2),
                ['bold' => true],
                ['alignment' => Jc::CENTER]
            );

        $table->addCell(1400);

        $section->addTextBreak(1);
    }

    // ==========================================
    // Main Table
    // ==========================================
    protected function addMainTable(
        $section,
        $records,
        $title
    ) {
        // Position-hierarchy sort: level DESC, उस्तै level भए employee_code natural order
        $records = EmployeeSorter::sort(collect($records));

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80
        ];

        $table = $section->addTable($tableStyle);

        $table->addRow();

        $headers = [
            'क्र.सं.',
            'नाम',
            'मिति (देखि - सम्म)',
            'पद',
            'जम्मा घण्टा',
            'खाजा खर्च',
            'कैफियत'
        ];

        $widths = [
            700,
            1800,
            2400,
            1400,
            1200,
            1200,
            1500
        ];

        foreach ($headers as $i => $h) {

            $table
                ->addCell($widths[$i])
                ->addText(
                    $h,
                    ['bold' => true],
                    ['alignment' => Jc::CENTER]
                );
        }

        // Employee अनुसार group गर्ने
        $employeeGroups = [];

        foreach ($records as $rec) {

            $empId = $rec->employee_id;

            if (!isset($employeeGroups[$empId])) {

                $employeeGroups[$empId] = [
                    'employee' => $rec->employee,
                    'dates' => [],
                    'hours' => 0,
                    'tiffin' => 0,
                ];
            }

            $employeeGroups[$empId]['dates'][] = $rec->ot_date;

            $employeeGroups[$empId]['hours'] +=
                $rec->total_hours;

            $employeeGroups[$empId]['tiffin'] +=
                $rec->tiffin_amount;
        }

        $sn = 1;

        foreach ($employeeGroups as $group) {

            $dates = collect($group['dates'])
                ->sort()
                ->values();

            $dateRange = adToBs($dates->first());

            if ($dates->count() > 1) {

                $dateRange .=
                    ' देखि ' .
                    adToBs($dates->last()) .
                    ' सम्म';
            }

            $table->addRow();

            $table
                ->addCell($widths[0])
                ->addText(
                    (string) $sn++,
                    [],
                    ['alignment' => Jc::CENTER]
                );

            $table
                ->addCell($widths[1])
                ->addText(
                    $group['employee']->name ?? 'N/A'
                );

            $table
                ->addCell($widths[2])
                ->addText($dateRange);

            $table
                ->addCell($widths[3])
                ->addText(
                    $group['employee']->position->name
                    ?? 'N/A'
                );

            $table
                ->addCell($widths[4])
                ->addText(
                    hoursToHm($group['hours']),
                    [],
                    ['alignment' => Jc::CENTER]
                );

            $table
                ->addCell($widths[5])
                ->addText(
                    number_format(
                        $group['tiffin'],
                        2
                    ),
                    [],
                    ['alignment' => Jc::CENTER]
                );

            $table
                ->addCell($widths[6])
                ->addText($title);
        }

        $section->addTextBreak(1);
    }

    // ==========================================
    // Signature Block
    // ==========================================
    protected function addSignatureBlock(
        $section,
        $printedBy = null,
        $approver = null,
        $recommender = null
    ) {
        $tabStyle = [
            'tabs' => [
                new Tab('left', 3000),
                new Tab('left', 2500)
            ],
            'spaceBefore' => 0,
            'spaceAfter' => 200
        ];

        // पेश गर्नेः
        $section->addText(
            'पेश गर्नेः',
            ['bold' => true],
            ['spaceAfter' => 0]
        );

        $section->addText(
            'नाम: ' .
            ($printedBy->name ?? '..........................') .
            "\t" .
            'पद: ' .
            (
                $printedBy->position->name
                ?? $printedBy->designation
                ?? '..........................'
            ) .
            "\t" .
            'दस्तखत: ..........................',
            [],
            $tabStyle
        );

        // सिफारिस गर्ने
        $section->addText(
            'सिफारिस गर्ने',
            ['bold' => true],
            ['spaceAfter' => 0]
        );

        $section->addText(
            'नाम: ' .
            ($recommender->name ?? '..........................') .
            "\t" .
            'पद: ' .
            (
                $recommender->position->name
                ?? '..........................'
            ) .
            "\t" .
            'दस्तखत र मिति: ..........................',
            [],
            $tabStyle
        );

        $section->addText(
            str_repeat('-', 110)
        );

        $section->addText(
            'आन्तरिक व्यवस्थापन शाखा',
            ['bold' => true],
            [
                'alignment' => Jc::CENTER,
                'spaceAfter' => 0
            ]
        );

        $section->addText(
            'माथि उल्लेखित कर्मचारीहरुले उल्लेख गरे बमोजिम समय अतिरिक्त काम गरेको दैनिक हाजिरीका अभिलेखमा छ।',
            [],
            ['spaceAfter' => 100]
        );

        $section->addText(
            'नाम: ' .
            $this->verifierName .
            "\t" .
            'पद: ' .
            $this->verifierPosition .
            "\t" .
            'हस्ताक्षर: ..........................         मिति: ..........................',
            [],
            $tabStyle
        );

        $section->addText(
            str_repeat('-', 110)
        );

        $section->addText(
            'निजहरुले माथि उल्लेख गर बमोजिमको अतिरिक्त समय काम गरेको व्यहोरा प्रमाणित गर्दछु।',
            [],
            ['spaceAfter' => 200]
        );

        $departmentName = $approver->department ?? '';

        $section->addText(
            $departmentName . ' निर्देशनालय प्रमुख',
            ['bold' => true],
            [
                'alignment' => Jc::CENTER,
                'spaceAfter' => 200
            ]
        );

        $section->addText(
            'नाम: ' .
            ($approver->name ?? '..........................') .
            "\t" .
            'हस्ताक्षर: ..........................' .
            "\t" .
            '   मिति: ..........................',
            [],
            $tabStyle
        );
    }

    // ==========================================
    // Detail Page
    // ==========================================
    protected function addDetailPage(
        $section,
        $records
    ) {
        // Position-hierarchy sort: level DESC, उस्तै level भए employee_code natural order
        $records = EmployeeSorter::sort(collect($records));

        $section->addPageBreak();

        $section->addText(
            'अतिरिक्त समय कार्यको विस्तृत विवरण',
            [
                'bold' => true,
                'size' => 12
            ],
            [
                'alignment' => Jc::CENTER
            ]
        );

        $section->addTextBreak(1);

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80
        ];

        $table = $section->addTable($tableStyle);

        $table->addRow();

        $headers = [
            'नाम',
            'मिति',
            'देखि - सम्म',
            'घण्टा',
            'मिनेटलाई घण्टामा',
            'खाजा'
        ];

        foreach ($headers as $h) {

            $table
                ->addCell(1500)
                ->addText(
                    $h,
                    ['bold' => true],
                    ['alignment' => Jc::CENTER]
                );
        }

        // Employee अनुसार group
        $groupedByEmployee = [];

        foreach ($records as $rec) {

            $empId = $rec->employee_id;

            if (!isset($groupedByEmployee[$empId])) {

                $groupedByEmployee[$empId] = [
                    'employee' => $rec->employee,
                    'records' => [],
                ];
            }

            $groupedByEmployee[$empId]['records'][] = $rec;
        }

        foreach ($groupedByEmployee as $group) {

            $subtotalHours = 0;
            $subtotalTiffin = 0;

            foreach ($group['records'] as $rec) {

                $table->addRow();

                $table
                    ->addCell(1500)
                    ->addText(
                        $rec->employee->name ?? 'N/A'
                    );

                $table
                    ->addCell(1500)
                    ->addText(
                        adToBs($rec->ot_date)
                    );

                $table
                    ->addCell(1500)
                    ->addText(
                        substr($rec->from_time, 0, 5) .
                        ' - ' .
                        substr($rec->to_time, 0, 5)
                    );

                $table
                    ->addCell(1500)
                    ->addText(
                        hoursToHm($rec->total_hours),
                        [],
                        ['alignment' => Jc::CENTER]
                    );

                $table
                    ->addCell(1500)
                    ->addText(
                        number_format(
                            $rec->total_hours,
                            2
                        ),
                        [],
                        ['alignment' => Jc::CENTER]
                    );

                $table
                    ->addCell(1500)
                    ->addText(
                        number_format(
                            $rec->tiffin_amount,
                            2
                        ),
                        [],
                        ['alignment' => Jc::CENTER]
                    );

                $subtotalHours +=
                    $rec->total_hours;

                $subtotalTiffin +=
                    $rec->tiffin_amount;
            }

            $table->addRow();

            $table
                ->addCell(
                    4500,
                    ['gridSpan' => 3]
                )
                ->addText(
                    ($group['employee']->name ?? 'N/A') .
                    ' - जम्मा',
                    ['bold' => true]
                );

            $table
                ->addCell(1500)
                ->addText(
                    hoursToHm($subtotalHours),
                    ['bold' => true],
                    ['alignment' => Jc::CENTER]
                );

            $table
                ->addCell(1500)
                ->addText(
                    number_format(
                        $subtotalHours,
                        2
                    ),
                    ['bold' => true],
                    ['alignment' => Jc::CENTER]
                );

            $table
                ->addCell(1500)
                ->addText(
                    number_format(
                        $subtotalTiffin,
                        2
                    ),
                    ['bold' => true],
                    ['alignment' => Jc::CENTER]
                );
        }
    }

    // ==========================================
    // Individual Format
    // ==========================================
    public function generateIndividual(
        $records,
        $employee,
        $event = null,
        $printedBy = null
    ) {
        $phpWord = $this->newDocument();

        $section = $phpWord->addSection([
            'marginTop' => 15
        ]);

        $bsToday = adToBs(date('Y-m-d'));

        $approver = $event->approver ?? null;
        $recommender = $event->recommender ?? null;

        $this->addLetterhead($section);

        $this->addTitleBlock(
            $section,
            $bsToday,
            $approver,
            $printedBy
        );

        $this->addIntroIndividual($section);

        $this->addIndividualTable(
            $section,
            $records
        );

        $this->addSignatureBlock(
            $section,
            $printedBy,
            $approver,
            $recommender
        );

        if (count($records) > 1) {
            $this->addDetailPage(
                $section,
                $records
            );
        }

        $filename =
            'OT_Slip_' .
            str_replace(' ', '_', $employee->name) .
            '_' .
            date('Ymd') .
            '.docx';

        return $this->saveToDownload(
            $phpWord,
            $filename
        );
    }

    // ==========================================
    // Group Format
    // ==========================================
    public function generateGroup(
        $records,
        $title,
        $event = null,
        $printedBy = null
    ) {
        $phpWord = $this->newDocument();

        $section = $phpWord->addSection([
            'marginTop' => 15
        ]);

        $bsToday = adToBs(date('Y-m-d'));

        $approver = $event->approver ?? null;
        $recommender = $event->recommender ?? null;

        $this->addLetterhead($section);

        $this->addTitleBlock(
            $section,
            $bsToday,
            $approver,
            $printedBy
        );

        // ==========================================
        // Department Name
        //
        // Case 1:
        // Event उपलब्ध छ भने $event->department
        //
        // Case 2:
        // Event null छ भने पहिलो employee को department
        //
        // अन्तिम fallback = खाली
        // ==========================================
        $departmentName = '';

        if ($event && !empty($event->department)) {

            $departmentName = $event->department;

        } elseif (
            $records &&
            isset($records[0]->employee) &&
            !empty($records[0]->employee->department)
        ) {

            $departmentName =
                $records[0]->employee->department;
        }

        // नयाँ signature:
        // addIntroGroup($section, $departmentName, $programName)
        $this->addIntroGroup(
            $section,
            $departmentName,
            $title
        );

        $this->addMainTable(
            $section,
            $records,
            $title
        );

        $this->addSignatureBlock(
            $section,
            $printedBy,
            $approver,
            $recommender
        );

        $this->addDetailPage(
            $section,
            $records
        );

        $filename =
            'OT_Group_' .
            str_replace(' ', '_', $title) .
            '_' .
            date('Ymd') .
            '.docx';

        return $this->saveToDownload(
            $phpWord,
            $filename
        );
    }

    // ==========================================
    // Save Word File
    // ==========================================
    protected function saveToDownload(
        PhpWord $phpWord,
        string $filename
    ) {
        $tempFile =
            tempnam(
                sys_get_temp_dir(),
                'ot_'
            ) . '.docx';

        $writer = IOFactory::createWriter(
            $phpWord,
            'Word2007'
        );

        $writer->save($tempFile);

        return response()
            ->download(
                $tempFile,
                $filename
            )
            ->deleteFileAfterSend(true);
    }
}