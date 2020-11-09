<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

if (!class_exists('WPSL_FPDF')) {
    require_once(__DIR__ . '/../../libs/artifacts/fpdf182/fpdf.php');
}

class WPSL_ShippingLabel
{

    private WPSL_FPDF $pdf;
    private array $options, $settings;

    public function __construct($options, $settings)
    {
        $this->options = $options;
        $this->settings = $settings;

        $this->validate();

        $this->createPDF();
    }

    /**
     * Generates PDF with given inputs
     */
    public function generatePDF() {

        if (isset($this->options['isPriority']) && $this->options['isPriority']) {
            $this->addPriorityField();
        }

        $this->addToFields();

        if (isset($this->options['customFields']) && $this->options['customFields']) {
            $this->addCustomFields();
        }

        $this->addFromFields();
    }

    /**
     * Gets PDF file created by $this
     * @return string
     */
    public function getPDF() {
        return $this->pdf->Output();
    }

    /**
     * Creates $this->pdf
     */
    private function createPDF() {

        $width = (int) $this->settings['pdfWidth'] ?? null;
        $height = ((int) $this->settings['pdfHeight']) ?? null;

        $size = [
            $width ?? 107,
            $height ?? 225
        ];

        $this->pdf = new WPSL_FPDF('P', 'mm', $size);
        $this->pdf->AddPage();

        $this->setDefaultFont();

        $this->pdf->SetAutoPageBreak(true, 0);

    }

    /**
     * Adds PRIORITY field to PDF
     */
    private function addPriorityField() {

        $this->setPriorityFont();

        $this->pdf->Cell(0, $this->getScaledSpacing(10, "pdf_fontSize_title"), iconv('utf-8', 'cp1252', 'PRIORITY'),0,1, 'C');
        $this->addSpacer($this->getScaledSpacing(7, "pdf_fontSize_title"));

    }

    /**
     * Adds 'to' fields to PDF
     */
    private function addToFields() {

        $to = $this->options['receiver'];
        $toFieldLabel = $this->options['toFieldLabel'] ?? 'To:';

        $name = $to['firstName'] . ' ' . $to['lastName'];

        $this->setDefaultFont('B', 12);

        $this->pdf->MultiCell(0,$this->getScaledSpacing(4), iconv('utf-8', 'cp1252', $toFieldLabel), 0, 1);

        $this->addSpacer($this->getScaledSpacing(1));

        $this->setTitleFont();

        if (isset($to['company']) && $to['company']) {

            $this->pdf->MultiCell(0,$this->getScaledSpacing(4, "pdf_fontSize_title"), iconv('utf-8', 'cp1252', $to['company']), 0, 1);

            $this->setDefaultFont();

        }

        $this->pdf->MultiCell(0,$this->getScaledSpacing(8), iconv('utf-8', 'cp1252', $name), 0, 1);

        $this->setDefaultFont();

        $this->pdf->MultiCell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', $to['address']), 0, 1);
        $this->pdf->MultiCell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', $to['postCode'] . ' ' . $to['city']), 0, 1);

        if (isset($to['state']) && $to['state']) {

            $this->pdf->MultiCell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', $to['state'] . ', ' . strtoupper($to['country'])), 0, 1);

        } else {

            $this->pdf->MultiCell(0,$this->getScaledSpacing(5), iconv('utf-8', 'cp1252', strtoupper($to['country'])), 0, 1);

        }

    }

    /**
     * Adds 'from' fields to PDF
     */
    private function addFromFields() {

        $from = $this->options['sender'];
        $fromFieldLabel = $this->options['fromFieldLabel'] ?? 'From:';

        $this->addSpacer($this->getScaledSpacing($this->settings['pdfSpaceBeforeFrom']));

        $this->setDefaultFont('B', 10);

        $this->pdf->MultiCell(0,$this->getScaledSpacing(4), iconv('utf-8', 'cp1252', $fromFieldLabel), 0, 1);

        $this->addSpacer($this->getScaledSpacing(1));

        $this->setScaledDefaultFont();

        $this->pdf->MultiCell(0,$this->getScaledSpacing(4), iconv('utf-8', 'cp1252', $from['company']), 0, 1);
        $this->pdf->MultiCell(0,$this->getScaledSpacing(4), iconv('utf-8', 'cp1252', $from['address']), 0, 1);
        $this->pdf->MultiCell(0,$this->getScaledSpacing(4), iconv('utf-8', 'cp1252', $from['postCode'] . ' ' . $from['city']), 0, 1);

        if (isset($from['state']) && $from['state']) {

            $this->pdf->MultiCell(0,$this->getScaledSpacing(4), iconv('utf-8', 'cp1252', $from['state'] . ', ' . strtoupper($from['country'])), 0, 1);

        } else {

            $this->pdf->MultiCell(0,$this->getScaledSpacing(4), iconv('utf-8', 'cp1252', strtoupper($from['country'])), 0, 1);

        }

    }

    /**
     * Adds optional fields to PDF
     */
    private function addCustomFields() {

        $this->addSpacer($this->getScaledSpacing(4));

        $customFields = $this->options['customFields'];

        forEach($customFields as $field) {

            if (isset($field) && $field) {

                if (isset($field['title']) && $field['title']) {

                    $this->setScaledTitleFont();

                    $this->pdf->MultiCell(0,$this->getScaledSpacing(5, "pdf_fontSize_title"), iconv('utf-8', 'cp1252', $field['title']), 0, 1);

                }

                if (isset($field['value']) && $field['value']) {

                    $this->setScaledDefaultFont();

                    $this->pdf->Multicell(0, $this->getScaledSpacing(5), iconv('utf-8', 'cp1252', $field['value']), 0, 1);

                }
            }
        }
    }

    /**
     * Sets default PDF field font
     * @param string|null $style
     * @param int|null $fontSize
     */
    private function setDefaultFont(string $style = null, int $fontSize = null) {

        $fontFamily = $this->settings['pdfFontFamily'] ?? 'Times';

        $fontStyle = isset($style) ? $style : $this->settings['pdfFontStyle'] ?? null;
        $fontSize = isset($fontSize) ? $fontSize : $this->settings['pdfFontSize'] ?? 12;

        $this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);

    }

    private function setScaledDefaultFont(string $style = null, int $fontSize = null) {

        $fontFamily = $this->settings['pdfFontFamily'] ?? 'Times';

        $fontStyle = isset($style) ? $style : $this->settings['pdfFontStyle'] ?? null;
        $fontSize = isset($fontSize) ? $fontSize : $this->settings['pdfFontSize'] ?? 12;

        $fontSize = $this->getScaledFontSize($fontSize, 0.75);

        $this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);

    }

    /**
     * Sets PDF font to title field font
     */
    private function setTitleFont() {

        $fontFamily = $this->settings['pdfFontFamily_title'] ?? 'Times';
        $fontStyle = $this->settings['pdfFontStyle_title'] ?? 'B';
        $fontSize = $this->settings['pdfFontSize_title'] ?? 14;

        $this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);

    }

    private function setScaledTitleFont() {

        $fontFamily = $this->settings['pdfFontFamily_title'] ?? 'Times';
        $fontStyle = $this->settings['pdfFontStyle_title'] ?? 'B';
        $fontSize = $this->settings['pdfFontSize_title'] ?? 14;

        $fontSize = $this->getScaledFontSize($fontSize,0.75);

        $this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);

    }

    /**
     * Sets PDF font to PRIORITY field font
     */
    private function setPriorityFont() {

        $fontFamily = $this->settings['pdfFontFamily_priority'] ?? 'Times';
        $fontStyle = $this->settings['pdfFontStyle_priority'] ?? 'B';
        $fontSize = $this->settings['pdfFontSize_priority'] ?? 22;

        $this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);

    }

    /**
     * Adds spacer cell
     * @param int $height
     */
    private function addSpacer(int $height) {
        $this->pdf->MultiCell(0, $height, '', 0,1);
    }

    /**
     * Scales input cell height or spacer height
     * @param int $spacing
     * @param string $affected_by
     * @return int
     */
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

        if ($scale > 1) {
            return (int) floor($spacing * $scale);
        } else {
            return (int) ceil($spacing * $scale);
        }
    }

    private function getScaledFontSize(int $fontSize, float $scale) {

        if ($scale > 1) {
            return (int) floor($scale * $fontSize);
        } else {
            return (int) ceil($scale * $fontSize);
        }

    }

    /**
     * Validates values used by class
     * @throws Exception
     */
    private function validate() {

        if (!$this->isSettingsValid()) {
            throw new Exception('PDF settings contain invalid values.');
        }

        if (!$this->isOptionsValid()) {
            throw new Exception('PDF options contain invalid values.');
        }

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

        if (isset($this->settings['pdfSpaceAfterTo'])) {
            $space = (int) $this->settings['pdfSpaceAfterTo'];

            if (!is_int($space)) { return false; }

            if ($space < 0) { return false; }
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