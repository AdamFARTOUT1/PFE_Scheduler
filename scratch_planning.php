<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

$phpWord = new PhpWord();
$section = $phpWord->addSection(['orientation' => 'landscape']);

$phpWord->addTableStyle('PlanTable', ['borderSize' => 4, 'borderColor' => 'AAAAAA', 'cellMargin' => 50]);
$table = $section->addTable('PlanTable');

$table->addRow(360);
$table->addCell(1000, ['bgColor' => 'FFFFFF'])->addText("ID 1");
$table->addCell(2000, ['bgColor' => '85B581'])->addText("Prof A");
$table->addCell(2000, ['bgColor' => 'CDD19A'])->addText("Prof B");
$table->addCell(2000, ['bgColor' => 'FDB2B0'])->addText("Prof C");
$table->addCell(2000, ['bgColor' => 'F6B9F4'])->addText("Prof D");
$table->addCell(1000, ['bgColor' => 'FFCDD2'])->addText("Date");

IOFactory::createWriter($phpWord, 'Word2007')->save('test_planning.docx');
echo "test_planning.docx generated.\n";
