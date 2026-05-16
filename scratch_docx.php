<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

$phpWord = new PhpWord();
$section = $phpWord->addSection();
$table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
$table->addRow();
$table->addCell(2000, ['bgColor' => 'EEF2FF'])->addText("Bg EEF2FF");
$table->addCell(2000, ['bgColor' => 'FCE4EC'])->addText("Bg FCE4EC");
$table->addCell(2000, ['bgColor' => '85B581'])->addText("Bg 85B581");

IOFactory::createWriter($phpWord, 'Word2007')->save('test_colors.docx');
echo "test_colors.docx generated.\n";
