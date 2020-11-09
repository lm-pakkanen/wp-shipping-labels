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

    /**
     * WPSL_ShippingLabel constructor.
     * @param $options
     * @param $settings
     * @throws Exception
     */
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
     * @throws Exception
     */
    private function createPDF() {

        $width = (int) $this->settings['width'] ?? null;
        $height = ((int) $this->settings['height']) ?? null;

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
     * @throws Exception
     */
    private function addPriorityField() {

        $this->setPriorityFont();

        $this->pdf->Cell(0, 10, iconv('utf-8', 'cp1252', 'PRIORITY'),0,1, 'C');
        $this->addSpacer(7);

    }

    /**
     * Adds 'to' fields to PDF
     * @throws Exception
     */
    private function addToFields() {

        $to = $this->options['receiver'];
        $toFieldLabel = $this->options['toFieldLabel'] ?? 'To:';

        $name = $to['firstName'] . ' ' . $to['lastName'];

        $this->setFont(null,  'B', 12);

        $this->pdf->MultiCell(0,$this->getScaledSpacing(5, 'receiver_title'), iconv('utf-8', 'cp1252', $toFieldLabel), 0, 1);

        $this->addSpacer($this->getScaledSpacing(1, 'receiver_title'));

        $this->setFont('receiver_title', 'B');

        if (isset($to['company']) && $to['company']) {

            $this->pdf->MultiCell(0,$this->getScaledSpacing(8, "receiver_title"), iconv('utf-8', 'cp1252', $to['company']), 0, 1);

            $this->setFont('receiver_content');

            $this->pdf->MultiCell(0,$this->getScaledSpacing(6), iconv('utf-8', 'cp1252', $name), 0, 1);

        } else {

            $this->pdf->MultiCell(0,$this->getScaledSpacing(8), iconv('utf-8', 'cp1252', $name), 0, 1);
            $this->addSpacer($this->getScaledSpacing(1, 'receiver_title'));

        }


        $this->setFont('receiver_content');

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
     * @throws Exception
     */
    private function addFromFields() {

        $from = $this->options['sender'];
        $fromFieldLabel = $this->options['fromFieldLabel'] ?? 'From:';

        $this->addSpacer($this->settings['spaceBeforeFrom']);

        $this->setFont(null, 'B', 10);

        $this->pdf->MultiCell(0,$this->getScaledSpacing(5, 'sender_content'), iconv('utf-8', 'cp1252', $fromFieldLabel), 0, 1);

        $this->addSpacer($this->getScaledSpacing(1, 'sender_content'));

        $this->setFont('sender_content');

        $this->pdf->MultiCell(0,$this->getScaledSpacing(5, 'sender_content'), iconv('utf-8', 'cp1252', $from['company']), 0, 1);
        $this->pdf->MultiCell(0,$this->getScaledSpacing(5, 'sender_content'), iconv('utf-8', 'cp1252', $from['address']), 0, 1);
        $this->pdf->MultiCell(0,$this->getScaledSpacing(5, 'sender_content'), iconv('utf-8', 'cp1252', $from['postCode'] . ' ' . $from['city']), 0, 1);

        if (isset($from['state']) && $from['state']) {

            $this->pdf->MultiCell(0,$this->getScaledSpacing(5, 'sender_content'), iconv('utf-8', 'cp1252', $from['state'] . ', ' . strtoupper($from['country'])), 0, 1);

        } else {

            $this->pdf->MultiCell(0,$this->getScaledSpacing(5, 'sender_content'), iconv('utf-8', 'cp1252', strtoupper($from['country'])), 0, 1);

        }

    }

    /**
     * Adds optional fields to PDF
     * @throws Exception
     */
    private function addCustomFields() {

        $this->addSpacer($this->getScaledSpacing(4));

        $customFields = $this->options['customFields'];

        forEach($customFields as $field) {

            if (isset($field) && $field) {

                if (isset($field['title']) && $field['title']) {

                    $this->setDefaultFont('sender_title');

                    $this->pdf->MultiCell(0,$this->getScaledSpacing(5, "sender_title"), iconv('utf-8', 'cp1252', $field['title']), 0, 1);

                }

                if (isset($field['value']) && $field['value']) {

                    $this->setFont('sender_content');

                    $this->pdf->Multicell(0, $this->getScaledSpacing(5, "sender_content"), iconv('utf-8', 'cp1252', $field['value']), 0, 1);

                }
            }
        }
    }

    /**
     * @param string|null $settingName
     * @param string $fontStyle
     * @param int|null $fontSize
     * @throws Exception
     */
    private function setFont(string $settingName = null, string $fontStyle = '', int $fontSize = null) {

        $fontFamily = $this->settings['fontFamily'] ?? 'Times';

        $internalFontSize = null;

        $valid_font_styles = [
            '',
            'B',
            'I',
            'U'
        ];

        if (isset($settingName) && !empty($settingName)) {

            $internalFontSize = $this->settings[$settingName . '_fontSize'] ?? null;

            if (!$internalFontSize) {
                throw new Exception('Fontsize not found for: ' . htmlspecialchars($settingName));
            } else if ((int) $internalFontSize < 6 || (int) $internalFontSize > 35) {
                throw new Exception('Fontsize is invalid for: ' . htmlspecialchars($settingName));
            }

        } else if (isset($fontSize) && !empty($fontSize)) {

            $internalFontSize = $fontSize;

            if ((int) $internalFontSize < 6 || (int) $internalFontSize > 35) {
                throw new Exception('Fontsize invalid: ' . htmlspecialchars($internalFontSize));
            }

        } else {
            throw new Exception('Fontsize was not provided.');
        }

        if (isset($fontStyle) && !empty($fontStyle)) {

            $styles = str_split($fontStyle);

            forEach($styles as $style) {

                if (!in_array($style, $valid_font_styles)) {
                    throw new Exception('Invalid font style: ' . htmlspecialchars($style));
                }

            }
        }

        $this->pdf->SetFont($fontFamily, $fontStyle, $internalFontSize);
    }

    /**
     * Sets default PDF field font
     * Uses receiver_content as default size
     * @throws Exception
     */
    private function setDefaultFont() {
        $this->SetFont('receiver_content');
    }

    /**
     * Sets PDF font to PRIORITY field font
     * @throws Exception
     */
    private function setPriorityFont() {
        $this->SetFont(null, 'B', 25);
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
     * @throws Exception
     */
    private function getScaledSpacing(int $spacing, string $affected_by = 'receiver_content') {

        $defaultFontSize = null;

        switch ($affected_by) {

            case 'receiver_title':
                $defaultFontSize = 22;
                break;

            case 'receiver_content';
            case 'sender_title':
                $defaultFontSize = 16;
                break;

            case 'sender_content':
                $defaultFontSize = 12;
                break;


        }

        $fontSize = $this->settings[$affected_by . '_fontSize'] ?? null;

        if (!$fontSize) {
            throw new Exception('Fontsize not found for: ' . $affected_by);
        }

        if ($fontSize !== $defaultFontSize) {
            $scale = $fontSize / $defaultFontSize;
        } else {
            $scale = 1;
        }

        if ($scale >= 1) {
            return (int) floor($spacing * $scale);
        } else {
            return (int) ceil($spacing * $scale);
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

        if (isset($this->settings['pdfWidth'], $this->settings['pdfHeight'])) {

            $width = (int) $this->settings['pdfWidth'];
            $height = (int) $this->settings['pdfHeight'];

            if (!is_int($width) || !is_int($height)) { return false; }

            if ($width < 50 || $width > 500 || $height < 50 || $height > 500) { return false; }

        }

        if (isset($this->settings['pdfFontFamily'])) {

            if (!in_array($this->settings['pdfFontFamily'], $valid_fonts)) { return false; }

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