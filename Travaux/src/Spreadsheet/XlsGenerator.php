<?php

namespace AcMarche\Travaux\Spreadsheet;

use AcMarche\Travaux\Entity\Intervention;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class XlsGenerator
{
    private Worksheet $worksheet;

    public function __construct()
    {
    }

    /**
     * @param array|Intervention[] $interventions
     * @return Spreadsheet
     */
    public function forGrh(array $interventions): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $this->worksheet = $spreadsheet->getActiveSheet();

        $this->setTitles();
        $ligne = 2;
        foreach ($interventions as $intervention) {
            $this->addLine($intervention, $ligne);
            $ligne++;
        }

        return $spreadsheet;
    }

    private function addLine(Intervention $intervention, int $ligne): void
    {
        $lettre = 'A';

        $this->worksheet
            ->setCellValue($lettre++.$ligne, $intervention->getId())
            ->setCellValue($lettre++.$ligne, $intervention->getCreatedAt()->format('Y-m-d'))
            ->setCellValue($lettre++.$ligne, $intervention->getIntitule())
            ->setCellValue($lettre++.$ligne, $intervention->getAffectation())
            ->setCellValue($lettre++.$ligne, $intervention->getDomaine())
            ->setCellValue($lettre++.$ligne, $intervention->getBatiment())
            ->setCellValue($lettre++.$ligne, $intervention->getDescriptif());
    }

    private function setTitles(): void
    {
        $lettre = 'A';
        $ligne = 1;
        $this->worksheet
            ->setCellValue($lettre++.$ligne, 'Numéro')
            ->setCellValue($lettre++.$ligne, 'Date')
            ->setCellValue($lettre++.$ligne, 'Intitule')
            ->setCellValue($lettre++.$ligne, 'Affection')
            ->setCellValue($lettre++.$ligne, 'Type')
            ->setCellValue($lettre++.$ligne, 'Bâtiment')
            ->setCellValue($lettre++.$ligne, 'Description');
    }
}
