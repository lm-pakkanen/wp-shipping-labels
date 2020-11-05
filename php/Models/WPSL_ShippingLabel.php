<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

require_once(__DIR__ . '/../../libs/fpdf182/fpdf.php');

/**
 * Shipping label class
 * Handles creation of shipping label PDF
 */
class WPSL_ShippingLabel
{

    private FPDF $pdf;
    private array $options, $settings;

    public function __construct($options, $settings)
    {
        $this->options = $options;
        $this->settings = $settings;

        $this->createPDF();
    }

    private function createPDF() {

        $size = [ $this->settings['pdfWidth'] ?? 107, $this->settings['pdfHeight'] ?? 225 ];

        $fontFamily = $this->settings['pdfFontFamily'] ?? 'Times';
        $fontStyle = $this->settings['pdfFontStyle'] ?? '';
        $fontSize = $this->settings['pdfFontSize'] ?? 10;

        $this->pdf = new FPDF('P', 'mm', $size);
        $this->pdf->AddPage();
        $this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);

    }

    public function generatePDF() {

        if (isset($this->options['isPriority']) && $this->options['isPriority']) {
            $this->addPriorityField();
        }

        $this->addToFields();
        $this->addFromFields();

        if (isset($this->options['customFields'])) {
            $this->addCustomFields();
        }
    }

    private function addPriorityField() {

        /**
         * Add PRIORITY field to PDF
         */
        $this->pdf->SetFont('Times', 'B', 14);
        $this->pdf->Cell(0, 0, iconv('utf-8', 'cp1252', 'PRIORITY'),0,1, 'C');
        $this->pdf->Cell(0, 12, '', 0,1);

    }

    private function addToFields() {

        $to = $this->options['receiver'];

        $name = $to['firstName'] . ' ' . $to['lastName'];

        $this->pdf->SetFont('Times', '', 10);

        /**
         * Add TO fields
         */
        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $to['company']), 0, 1);
        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $name), 0, 1);
        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $to['address']), 0, 1);
        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $to['postCode'] . ' ' . $to['city']), 0, 1);

        if (isset($to['state']) && $to['state']) {
            $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $to['state']), 0, 1);
        }

        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $to['country']), 0, 1);


        /**
         * Spacer
         */
        $this->pdf->Cell(0, 15, '', 0,1);

    }

    private function addFromFields() {

        $from = $this->options['sender'];

        /**
         * Add FROM fields
         */
        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $from['company']), 0, 1);
        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $from['address']), 0, 1);
        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $from['postCode'] . ', ' . $from['city']), 0, 1);

        if (isset($from['state']) && $from['state']) {
            $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $from['state']), 0, 1);
        }

        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $from['country']), 0, 1);


        /**
         * Spacer
         */
        $this->pdf->Cell(0, 25, '', 0,1);
    }

    private function addCustomFields() {

        $customFields = $this->options['customFields'];

        /**
         * Add optional fields
         */
        forEach($customFields as $field) {

            if (isset($field)) {

                if (isset($field['title'])) {
                    $this->pdf->SetFont('Times', 'B', 12);
                    $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $field['title']), 0, 1);
                    $this->pdf->SetFont('Times', '', 10);
                }

                if (isset($field['value'])) {
                    $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $field['value']), 0, 1);
                    $this->pdf->Cell(0,5,'', 0,1);
                }

            }
        }

    }

    public function getPDF() {
        return $this->pdf->Output();
    }
}