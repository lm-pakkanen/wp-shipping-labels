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

        if (!$this->isSettingsValid()) {
            throw new Exception('PDF settings contain invalid values.');
        }

        $this->createPDF();
    }

    private function isSettingsValid() {

        $valid_fonts = [
            'Courier',
            'Helvetica',
            'Arial',
            'Symbol',
            'ZapfDingbats'
        ];

        $valid_font_styles = [
            '',
            'B',
            'I',
            'U'
        ];

        if (isset($this->settings['pdfWidth'], $this->settings['pdfHeight'])) {

            if (!is_int($this->settings['pdfWidth'] || !is_int($this->settings['pdfHeight']))) {
                return false;
            }

        }

        if (isset($this->settings['pdfFontFamily'])) {
            if (!in_array($this->settings['pdfFontFamily'], $valid_fonts)) {
                return false;
            }
        }

        if (isset($this->settings['pdfFontStyle'])) {

            $style = str_split($this->settings['pdfFontStyle']);

            forEach($style as $letter) {

                if (!in_array($letter, $valid_font_styles)) {
                    return false;
                }

            }
        }

        if (isset($this->settings['pdfFontSize'])) {
            if (!is_int($this->settings['pdfFontSize'])) {
                return false;
            }
        }

        return true;
    }

    private function setDefaultFont() {

        $fontFamily = $this->settings['pdfFontFamily'] ?? 'Times';
        $fontStyle = $this->settings['pdfFontStyle'] ?? '';
        $fontSize = $this->settings['pdfFontSize'] ?? 14;

        $this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);

    }

    private function createPDF() {

        $size = [ $this->settings['pdfWidth'] ?? 107, $this->settings['pdfHeight'] ?? 225 ];

        $this->pdf = new FPDF('P', 'mm', $size);
        $this->pdf->AddPage();

        $this->setDefaultFont();

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
        $this->pdf->SetFont('Times', 'B', 22);
        $this->pdf->Cell(0, 10, iconv('utf-8', 'cp1252', 'PRIORITY'),0,1, 'C');
        $this->pdf->Cell(0, 18, '', 0,1);

    }

    private function addToFields() {

        $to = $this->options['receiver'];

        $name = $to['firstName'] . ' ' . $to['lastName'];

        $this->setDefaultFont();

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

        $this->setDefaultFont();

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
                    $this->pdf->SetFont('Times', 'B', 16);
                    $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $field['title']), 0, 1);
                }

                if (isset($field['value'])) {
                    $this->setDefaultFont();
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