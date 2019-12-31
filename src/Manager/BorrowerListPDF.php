<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @author: Emmanuel Havet <ehavet@yahoo.fr> (Code 39)
 *
 * User: craig
 * Date: 19/11/2019
 * Time: 11:46
 */

namespace Kookaburra\Library\Manager;

use App\Entity\RollGroup;
use App\Util\ImageHelper;
use App\Util\TranslationsHelper;
use Fpdf\Fpdf;
use Symfony\Component\Form\FormInterface;

/**
 * Class BorrowerListPDF
 * @package Kookaburra\Library\Manager
 */
class BorrowerListPDF extends Fpdf
{
    const DPI = 96;
    const MM_IN_INCH = 25.4;
    const A4_HEIGHT = 297;
    const A4_WIDTH = 210;
    // tweak these values (in pixels)
    const MAX_WIDTH = 800;
    const MAX_HEIGHT = 500;

    /**
     * @var string|null
     */
    private $borrowerType;

    /**
     * @var RollGroup|null
     */
    private $rollGroup;

    /**
     * @var bool
     */
    private $withPhoto;

    /**
     * @var array
     */
    private $people;

    /**
     * @var int
     */
    private $maxWidth;

    /**
     * @var int
     */
    private $maxHeight;

    /**
     * BorrowerListPDF constructor.
     * @param string $orientation
     * @param string $unit
     * @param string $size
     */
    public function __construct(
        $orientation = 'P',
        $unit = 'mm',
        $size = 'A4'
    ) {
        parent::__construct($orientation, $unit, $size);
    }

    /**
     * generate
     * @throws \Exception
     */
    public function generate()
    {
        $this->AliasNbPages();
        $this->AddPage();
        $this->SetFont('Arial','',11);
        $height = $this->isWithPhoto() ? 60 : 20;
        $col = 0;
        $row = 0;
        $this->setMaxWidth(150);
        $this->setMaxHeight(150);
        foreach($this->people as $person) {
            $this->Rect($col * 60 + 15, $row * $height + 24, 60, $height);
            if ($this->isWithPhoto()) {
                $this->centreImage(ImageHelper::getAbsoluteImagePath($person->getImage240()), $col * 60 + 15, $row * $height + 26);
                $this->Code39($col * 60 + 20, $row * $height + 68, $person->uniqueIdentifier(), true, true, 0.6, 10, true, $person->formatName(['informal' => true, 'initial' => true]));
            } else {
                $this->Code39($col * 60 + 20, $row * $height + 29, $person->uniqueIdentifier(), true, true, 0.6, 10, true, $person->formatName(['informal' => true, 'initial' => true]));
            }
            $col++;
            if ($col > 2) {
                $col = 0;
                $row++;
                if ($row > 3 && $this->isWithPhoto()) {
                    $row = 0;
                    $this->AddPage();
                }
                if ($row > 11 && ! $this->isWithPhoto()) {
                    $row = 0;
                    $this->AddPage();
                }
            }

        }
        if ($this->getBorrowerType() === 'Student') {
            $this->Output('D', 'Borrower List - ' . $this->getBorrowerType().' - '.$this->getRollGroup()->getName().'.pdf');
        } else {
            $this->Output('D', 'Borrower List - ' . $this->getBorrowerType().'.pdf');
        }
    }

    /**
     * Header
     */
    function Header()
    {
        // Logo
        $this->Image(__DIR__ . '/../../../../../public/themes/Default/img/logo.png',10,6,30);
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Move to the right
        //$this->Cell();
        // Title
        $title = TranslationsHelper::translate('Borrower List - {borrowerType}', ['{borrowerType}' => TranslationsHelper::translate('borrower.type.'. strtolower($this->getBorrowerType()), [], 'Library')], 'Library') . ' ('.count($this->getPeople()).')';
        if ($this->getBorrowerType() === 'Student')
        {
            $this->Cell(0,5, $title,0,15,'C');
            $this->SetFont('Arial','',12);
            $title = TranslationsHelper::translate('Roll Group', [], 'messages').': '. $this->getRollGroup()->__toString();
            $this->Cell(0,5, $title,'B',0,'C');
            // Line break
            $this->Ln(5);
        } else {
            $this->Cell(0,5, $title,'B',0,'C');
            // Line break
            $this->Ln(20);
        }
    }

    /**
     * Footer
     */
    function Footer()
    {
        // Position at 1.7 cm from bottom
        $this->SetY(-17);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

    /**
     * @return string|null
     */
    public function getBorrowerType(): ?string
    {
        return $this->borrowerType;
    }

    /**
     * @return RollGroup|null
     */
    public function getRollGroup(): ?RollGroup
    {
        return $this->rollGroup;
    }

    /**
     * @return bool
     */
    public function isWithPhoto(): bool
    {
        return $this->withPhoto;
    }

    /**
     * @return array
     */
    public function getPeople(): array
    {
        return $this->people ?: [];
    }

    /**
     * setForm
     * @param FormInterface $form
     * @return BorrowerListPDF
     */
    public function setForm(FormInterface $form): BorrowerListPDF
    {
        $this->borrowerType = $form->get('borrowerType')->getData();
        $this->rollGroup = $form->get('rollGroup')->getData();
        $this->withPhoto = $form->get('withPhoto')->getData() === 'Y';
        return $this;
    }

    /**
     * People.
     *
     * @param array $people
     * @return BorrowerListPDF
     */
    public function setPeople(array $people): BorrowerListPDF
    {
        $this->people = $people;
        return $this;
    }

    /**
     * pixelsToMM
     * @param $val
     * @return float|int
     */
    function pixelsToMM($val) {
        return $val * self::MM_IN_INCH / self::DPI;
    }

    /**
     * resizeToFit
     * @param $imgFilename
     * @return array
     */
    function resizeToFit($imgFilename) {
        list($width, $height) = getimagesize($imgFilename);
        $widthScale = $this->getMaxWidth() / $width;
        $heightScale = $this->getMaxHeight() / $height;
        $scale = min($widthScale, $heightScale);
        return [
            round($this->pixelsToMM($scale * $width)),
            round($this->pixelsToMM($scale * $height))
        ];
    }

    /**
     * centreImage
     * @param $img
     */
    function centreImage($img, $x, $y) {
        list($width, $height) = $this->resizeToFit($img);
        // you will probably want to swap the width/height
        // around depending on the page's orientation
        $x = $x + (60 - $width) / 2;

        $this->Image(
            $img, $x,
            $y,
            $width,
            $height
        );
    }

    /**
     * @return int
     */
    public function getMaxWidth(): int
    {
        return $this->maxWidth ?: self::MAX_WIDTH;
    }

    /**
     * setMaxWidth
     *
     * in pixels
     * @param int|null $maxWidth
     * @return BorrowerListPDF
     */
    public function setMaxWidth(?int $maxWidth): BorrowerListPDF
    {
        $this->maxWidth = $maxWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxHeight(): int
    {
        return $this->maxHeight ?: self::MAX_HEIGHT;
    }

    /**
     * setMaxHeight
     *
     * in pixels
     * @param int|null $maxHeight
     * @return BorrowerListPDF
     */
    public function setMaxHeight(?int $maxHeight): BorrowerListPDF
    {
        $this->maxHeight = $maxHeight;
        return $this;
    }

    /**
     * Code39
     *
     * This script supports both standard and extended Code 39 barcodes. The extended mode gives access to the full ASCII range (from 0 to 127). The script also gives the possibility to add a checksum.
     *
     * Code39(float x, float y, string code [, boolean ext [, boolean cks [, float w [, float h [, boolean wide]]]]])
     *
     * @param float $x: abscissa
     * @param float $y: ordinate
     * @param string $code: barcode value
     * @param bool $ext: indicates if extended mode must be used (true by default)
     * @param bool $cks: indicates if a checksum must be appended (false by default)
     * @param float $w: width of a narrow bar (0.4 by default)
     * @param int $h: height of bars (20 by default)
     * @param bool wide: indicates if ratio between wide and narrow bars is high; if yes, ratio is 3, if no, it's 2 (true by default)
     * $param string
     * @throws \Exception
     */
    function Code39(float $x, float $y, string $code, bool $ext = true, bool $cks = false, float $w = 0.4, int $h = 20, bool $wide = true, string $label = '') {

        //Display code
        $this->SetFont('Arial', '', 10);

        if($ext) {
            //Extended encoding
            $code = $this->encode_code39_ext($code);
        }
        else {
            //Convert to upper case
            $code = strtoupper($code);
            //Check validity
            if(!preg_match('|^[0-9A-Z. $/+%-]*$|', $code))
                $this->Error('Invalid barcode value: '.$code);
        }

        //Compute checksum
        if ($cks)
            $code .= $this->checksum_code39($code);

        //Add start and stop characters
        $code = '*'.$code.'*';

        //Conversion tables
        $narrow_encoding = array (
            '0' => '101001101101', '1' => '110100101011', '2' => '101100101011',
            '3' => '110110010101', '4' => '101001101011', '5' => '110100110101',
            '6' => '101100110101', '7' => '101001011011', '8' => '110100101101',
            '9' => '101100101101', 'A' => '110101001011', 'B' => '101101001011',
            'C' => '110110100101', 'D' => '101011001011', 'E' => '110101100101',
            'F' => '101101100101', 'G' => '101010011011', 'H' => '110101001101',
            'I' => '101101001101', 'J' => '101011001101', 'K' => '110101010011',
            'L' => '101101010011', 'M' => '110110101001', 'N' => '101011010011',
            'O' => '110101101001', 'P' => '101101101001', 'Q' => '101010110011',
            'R' => '110101011001', 'S' => '101101011001', 'T' => '101011011001',
            'U' => '110010101011', 'V' => '100110101011', 'W' => '110011010101',
            'X' => '100101101011', 'Y' => '110010110101', 'Z' => '100110110101',
            '-' => '100101011011', '.' => '110010101101', ' ' => '100110101101',
            '*' => '100101101101', '$' => '100100100101', '/' => '100100101001',
            '+' => '100101001001', '%' => '101001001001' );

        $wide_encoding = array (
            '0' => '101000111011101', '1' => '111010001010111', '2' => '101110001010111',
            '3' => '111011100010101', '4' => '101000111010111', '5' => '111010001110101',
            '6' => '101110001110101', '7' => '101000101110111', '8' => '111010001011101',
            '9' => '101110001011101', 'A' => '111010100010111', 'B' => '101110100010111',
            'C' => '111011101000101', 'D' => '101011100010111', 'E' => '111010111000101',
            'F' => '101110111000101', 'G' => '101010001110111', 'H' => '111010100011101',
            'I' => '101110100011101', 'J' => '101011100011101', 'K' => '111010101000111',
            'L' => '101110101000111', 'M' => '111011101010001', 'N' => '101011101000111',
            'O' => '111010111010001', 'P' => '101110111010001', 'Q' => '101010111000111',
            'R' => '111010101110001', 'S' => '101110101110001', 'T' => '101011101110001',
            'U' => '111000101010111', 'V' => '100011101010111', 'W' => '111000111010101',
            'X' => '100010111010111', 'Y' => '111000101110101', 'Z' => '100011101110101',
            '-' => '100010101110111', '.' => '111000101011101', ' ' => '100011101011101',
            '*' => '100010111011101', '$' => '100010001000101', '/' => '100010001010001',
            '+' => '100010100010001', '%' => '101000100010001');

        $encoding = $wide ? $wide_encoding : $narrow_encoding;

        //Inter-character spacing
        $gap = ($w > 0.29) ? '00' : '0';

        //Convert to bars
        $encode = '';
        for ($i = 0; $i < strlen($code); $i++)
            $encode .= $encoding[$code[$i]].$gap;

        while ($w * strlen($encode) > 55)
            $w -= 0.01;

        //Draw bars
        $width = $this->GetStringWidth($label) + 4;
        $a = (60 - $w * strlen($encode)) / 2;
        $this->draw_code39($encode, $x + $a - 5, $y, $w, $h);

        $this->setXY($x - 5,$y + $h - 3);
        $this->Cell((60 - $width ) / 2);
        $this->SetFillColor(255);
        $this->Cell($width, 4, $label, 0, 0, 'C',1);
        $this->SetFillColor(0);
    }

    /**
     * checksum_code39
     * @param $code
     * @return mixed
     */
    function checksum_code39($code) {

        //Compute the modulo 43 checksum

        $chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
            'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
            'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%');
        $sum = 0;
        for ($i=0 ; $i<strlen($code); $i++) {
            $a = array_keys($chars, $code[$i]);
            $sum += $a[0];
        }
        $r = $sum % 43;
        return $chars[$r];
    }

    /**
     * encode_code39_ext
     * @param $code
     * @return string
     * @throws \Exception
     */
    function encode_code39_ext($code) {

        //Encode characters in extended mode

        $encode = array(
            chr(0) => '%U', chr(1) => '$A', chr(2) => '$B', chr(3) => '$C',
            chr(4) => '$D', chr(5) => '$E', chr(6) => '$F', chr(7) => '$G',
            chr(8) => '$H', chr(9) => '$I', chr(10) => '$J', chr(11) => '£K',
            chr(12) => '$L', chr(13) => '$M', chr(14) => '$N', chr(15) => '$O',
            chr(16) => '$P', chr(17) => '$Q', chr(18) => '$R', chr(19) => '$S',
            chr(20) => '$T', chr(21) => '$U', chr(22) => '$V', chr(23) => '$W',
            chr(24) => '$X', chr(25) => '$Y', chr(26) => '$Z', chr(27) => '%A',
            chr(28) => '%B', chr(29) => '%C', chr(30) => '%D', chr(31) => '%E',
            chr(32) => ' ', chr(33) => '/A', chr(34) => '/B', chr(35) => '/C',
            chr(36) => '/D', chr(37) => '/E', chr(38) => '/F', chr(39) => '/G',
            chr(40) => '/H', chr(41) => '/I', chr(42) => '/J', chr(43) => '/K',
            chr(44) => '/L', chr(45) => '-', chr(46) => '.', chr(47) => '/O',
            chr(48) => '0', chr(49) => '1', chr(50) => '2', chr(51) => '3',
            chr(52) => '4', chr(53) => '5', chr(54) => '6', chr(55) => '7',
            chr(56) => '8', chr(57) => '9', chr(58) => '/Z', chr(59) => '%F',
            chr(60) => '%G', chr(61) => '%H', chr(62) => '%I', chr(63) => '%J',
            chr(64) => '%V', chr(65) => 'A', chr(66) => 'B', chr(67) => 'C',
            chr(68) => 'D', chr(69) => 'E', chr(70) => 'F', chr(71) => 'G',
            chr(72) => 'H', chr(73) => 'I', chr(74) => 'J', chr(75) => 'K',
            chr(76) => 'L', chr(77) => 'M', chr(78) => 'N', chr(79) => 'O',
            chr(80) => 'P', chr(81) => 'Q', chr(82) => 'R', chr(83) => 'S',
            chr(84) => 'T', chr(85) => 'U', chr(86) => 'V', chr(87) => 'W',
            chr(88) => 'X', chr(89) => 'Y', chr(90) => 'Z', chr(91) => '%K',
            chr(92) => '%L', chr(93) => '%M', chr(94) => '%N', chr(95) => '%O',
            chr(96) => '%W', chr(97) => '+A', chr(98) => '+B', chr(99) => '+C',
            chr(100) => '+D', chr(101) => '+E', chr(102) => '+F', chr(103) => '+G',
            chr(104) => '+H', chr(105) => '+I', chr(106) => '+J', chr(107) => '+K',
            chr(108) => '+L', chr(109) => '+M', chr(110) => '+N', chr(111) => '+O',
            chr(112) => '+P', chr(113) => '+Q', chr(114) => '+R', chr(115) => '+S',
            chr(116) => '+T', chr(117) => '+U', chr(118) => '+V', chr(119) => '+W',
            chr(120) => '+X', chr(121) => '+Y', chr(122) => '+Z', chr(123) => '%P',
            chr(124) => '%Q', chr(125) => '%R', chr(126) => '%S', chr(127) => '%T');

        $code_ext = '';
        for ($i = 0 ; $i<strlen($code); $i++) {
            if (ord($code[$i]) > 127)
                $this->Error('Invalid character: '.$code[$i]);
            $code_ext .= $encode[$code[$i]];
        }
        return $code_ext;
    }

    /**
     * draw_code39
     * @param $code
     * @param $x
     * @param $y
     * @param $w
     * @param $h
     */
    function draw_code39($code, $x, $y, $w, $h) {

        //Draw bars

        for($i=0; $i<strlen($code); $i++) {
            if($code[$i] == '1')
                $this->Rect($x+$i*$w, $y, $w, $h, 'F');
        }
    }}