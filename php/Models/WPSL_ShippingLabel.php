<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

if (!class_exists('FPDF')) {
    require_once(__DIR__ . '/../../libs/fpdf182/fpdf.php');
}

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

        $this->pdf->Cell(0, $this->getScaledSpacing(10, "pdf_fontSize_title"), iconv('utf-8', 'cp1252', 'PRIORITY'),0,1, 'C');
        $this->addSpacer($this->getScaledSpacing(18));

    }

    private function addToFields() {

        $to = $this->options['receiver'];

        $name = $to['firstName'] . ' ' . $to['lastName'];

        $this->setDefaultFont();

        $this->addSpacer($this->getScaledSpacing(5));


        /**
         * Add TO fields
         */

        $this->setTitleFont();

        if (isset($to['company']) && $to['company']) {

            $this->pdf->Cell(0,$this->getScaledSpacing(5, "pdf_fontSize_title"), iconv('utf-8', 'cp1252', $to['company']), 0, 1);

            $this->setDefaultFont();

        }

        $this->pdf->Cell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', $name), 0, 1);

        $this->setDefaultFont();

        $this->addSpacer($this->getScaledSpacing(5));

        $this->pdf->Cell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', $to['address']), 0, 1);
        $this->pdf->Cell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', $to['postCode'] . ' ' . $to['city']), 0, 1);

        if (isset($to['state']) && $to['state']) {

            $this->pdf->Cell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', $to['state'] . ', ' . strtoupper($to['country'])), 0, 1);

        } else {

            $this->pdf->Cell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', strtoupper($to['country'])), 0, 1);

        }


        $this->addSpacer($this->getScaledSpacing(15));
    }

    private function addFromFields() {

        $from = $this->options['sender'];

        /**
         * Add FROM fields
         */

        $this->setTitleFont();

        $this->pdf->Cell(0,$this->getScaledSpacing(5, "pdf_fontSize_title"), iconv('utf-8', 'cp1252', $from['company']), 0, 1);

        $this->setDefaultFont();

        $this->addSpacer($this->getScaledSpacing(5));

        $this->pdf->Cell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', $from['address']), 0, 1);
        $this->pdf->Cell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', $from['postCode'] . ' ' . $from['city']), 0, 1);

        if (isset($from['state']) && $from['state']) {

            $this->pdf->Cell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', $from['state'] . ', ' . strtoupper($from['country'])), 0, 1);

        } else {

            $this->pdf->Cell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', strtoupper($from['country'])), 0, 1);

        }

        $this->addSpacer($this->getScaledSpacing(25));
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

                    $this->pdf->Cell(0,$this->getScaledSpacing(5, "pdf_fontSize_title"), iconv('utf-8', 'cp1252', $field['title']), 0, 1);

                }

                if (isset($field['value']) && $field['value']) {

                    $this->setDefaultFont();

                    $this->pdf->Multicell(0, $this->getScaledSpacing(4), iconv('utf-8', 'cp1252', $field['value']), 0, 1);

                    $this->addSpacer($this->getScaledSpacing(5));

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

    private function getScaledSpacing(int $spacing, string $affected_by = 'pdfFontSize') {

        if ($affected_by === 'pdfFontSize') {

            $default_fontSize = 12;
            $fontSize = $this->settings['pdfFontSize'] ?? $default_fontSize;

        } else if ($affected_by === 'pdfFontSize_title') {

            $default_fontSize = 14;
            $fontSize = $this->settings['pdfFontSize_title'] ?? $default_fontSize;

        } else {
            $fontSize = 1;
            $default_fontSize = 1;
        }

        if ($fontSize > $default_fontSize) {
            $scale = $fontSize / $default_fontSize;
        } else if ($fontSize < $default_fontSize) {
            $scale = $fontSize / $default_fontSize;
        } else {
            $scale = 1;
        }

        return (int) floor($spacing * $scale);
    }

    /**
     * Validates $this->settings
     * @return bool
     */
    private function isSettingsValid() {

        $valid_fonts = [
            'Times',
            'Helvetica',
            'Courier'
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

        $receiver = $this->options['receiver'];

        if (isset($receiver) && !empty($receiver)) {

            $required_fields = [
                $receiver['firstName'],
                $receiver['lastName'],
                $receiver['address'],
                $receiver['postCode'],
                $receiver['city'],
                $receiver['country']
            ];

            forEach($required_fields as $field) {
                if (!isset($field) || empty($field)) { return false; }
            }

        }

        $sender = $this->options['sender'];

        if (isset($sender) && !empty($sender)) {

            $required_fields = [
                $sender['company'],
                $sender['address'],
                $sender['postCode'],
                $sender['city'],
                $sender['country']
            ];

            forEach($required_fields as $field) {
                if (!isset($field) || empty($field)) { return false; }
            }

        }

        return true;
    }
}