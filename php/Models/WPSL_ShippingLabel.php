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

        if (!$this->isOptionsValid()) {
            throw new Exception('PDF options contain invalid values.');
        }

        $this->createPDF();
    }

    /**
     * Validates $this->settings
     * @return bool
     */
    private function isSettingsValid() {

        // TODO: Separate to Validator class

        $valid_fonts = [
            'Helvetica',
            'Courier',
            'Arial',
        ];

        $valid_font_styles = [
            '',
            'B',
            'I',
            'U'
        ];

        if (isset($this->settings['pdfWidth'], $this->settings['pdfHeight'])) {

            $width = (int) $this->settings['pdfWidth'];
            $height = (int) $this->settings['pdfHeight'];

            if (!is_int($width) || !is_int($height)) { return false; }

            if ($width < 50 || $width > 500 || $height < 50 || $height > 500) { return false; }

        }

        if (isset($this->settings['pdfFontFamily'])) {

            if (!in_array($this->settings['pdfFontFamily'], $valid_fonts)) { return false; }

        }

        if (isset($this->settings['pdfFontStyle'])) {

            $style = str_split($this->settings['pdfFontStyle']);

            forEach($style as $letter) {

                if (!in_array($letter, $valid_font_styles)) { return false; }

            }
        }

        if (isset($this->settings['pdfFontSize'])) {

            $fontSize = (int) $this->settings['pdfFontSize'];

            if (!is_int($fontSize)) { return false; }

            if ($fontSize < 6 || $fontSize > 35) { return false; }

        }

        return true;
    }

    /**
     * Validates $this->options
     * @return bool
     */
    private function isOptionsValid() {

        // TODO: Validate OPTIONS

        // TODO: Separate to Validator class

        return true;
    }

    /**
     * Fill $this->pdf with data
     */
    public function generatePDF() {

        if (isset($this->options['isPriority']) && $this->options['isPriority']) {
            $this->addPriorityField();
        }

        $this->addToFields();
        $this->addFromFields();

        if (isset($this->options['customFields']) && $this->options['customFields']) {
            $this->addCustomFields();
        }
    }

    public function getPDF() {
        return $this->pdf->Output();
    }

    /**
     * Create $this->pdf
     */
    private function createPDF() {

        $width = (int) $this->settings['pdfWidth'] ?? null;
        $height = ((int) $this->settings['pdfHeight']) ?? null;

        $size = [
            $width ?? 107,
            $height ?? 225
        ];

        $this->pdf = new FPDF('P', 'mm', $size);
        $this->pdf->AddPage();

        $this->setDefaultFont();

    }

    /**
     * Add PRIORITY field to PDF
     */
    private function addPriorityField() {

        $this->setPriorityFont();

        $this->pdf->Cell(0, 10, iconv('utf-8', 'cp1252', 'PRIORITY'),0,1, 'C');
        $this->addSpacer(18);

    }

    private function addToFields() {

        $to = $this->options['receiver'];

        $name = $to['firstName'] . ' ' . $to['lastName'];

        $this->setDefaultFont();

        $this->addSpacer(5);


        /**
         * Add TO fields
         */

        $this->setTitleFont();

        if (isset($to['company']) && $to['company']) {

            $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $to['company']), 0, 1);

            $this->setDefaultFont();

        }

        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $name), 0, 1);

        $this->addSpacer(5);

        $this->setDefaultFont();

        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $to['address']), 0, 1);
        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $to['postCode'] . ' ' . $to['city']), 0, 1);

        if (isset($to['state']) && $to['state']) {

            $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $to['state'] . ', ' . strtoupper($to['country'])), 0, 1);

        } else {

            $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', strtoupper($to['country'])), 0, 1);

        }


        $this->addSpacer(15);
    }

    private function addFromFields() {

        $from = $this->options['sender'];

        /**
         * Add FROM fields
         */

        $this->setTitleFont();

        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $from['company']), 0, 1);

        $this->addSpacer(5);

        $this->setDefaultFont();

        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $from['address']), 0, 1);
        $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $from['postCode'] . ' ' . $from['city']), 0, 1);

        if (isset($from['state']) && $from['state']) {

            $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $from['state'] . ', ' . strtoupper($from['country'])), 0, 1);

        } else {

            $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', strtoupper($from['country'])), 0, 1);

        }

        $this->addSpacer(25);
    }

    private function addCustomFields() {

        $customFields = $this->options['customFields'];

        /**
         * Add optional fields
         */
        forEach($customFields as $field) {

            if (isset($field) && $field) {

                if (isset($field['title']) && $field['title']) {

                    $this->setTitleFont();

                    $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $field['title']), 0, 1);

                }

                if (isset($field['value']) && $field['value']) {

                    $this->setDefaultFont();

                    $this->pdf->Cell(0,5, iconv('utf-8', 'cp1252', $field['value']), 0, 1);

                    $this->addSpacer(5);

                }
            }
        }
    }

    private function setDefaultFont() {

        $fontFamily = $this->settings['pdfFontFamily'] ?? 'Times';
        $fontStyle = $this->settings['pdfFontStyle'] ?? '';
        $fontSize = $this->settings['pdfFontSize'] ?? 12;

        $this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);

    }

    private function setTitleFont() {

        $fontFamily = $this->settings['pdfFontFamily_title'] ?? 'Times';
        $fontStyle = $this->settings['pdfFontStyle_title'] ?? 'B';
        $fontSize = $this->settings['pdfFontSize_title'] ?? 14;

        $this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);

    }

    private function setPriorityFont() {

        $fontFamily = $this->settings['pdfFontFamily_priority'] ?? 'Times';
        $fontStyle = $this->settings['pdfFontStyle_priority'] ?? 'B';
        $fontSize = $this->settings['pdfFontSize_priority'] ?? 22;

        $this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);

    }

    private function addSpacer(int $height) {
        $this->pdf->Cell(0, $height, '', 0,1);
    }
}