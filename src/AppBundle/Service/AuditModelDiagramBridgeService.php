<?php

namespace AppBundle\Service;

use AppBundle\Entity\BladeDamage;
use AppBundle\Enum\BladeDamageEdgeEnum;
use AppBundle\Enum\BladeDamagePositionEnum;
use AppBundle\Pdf\CustomTcpdf;

/**
 * Class AuditModelDiagramBridgeService
 *
 * @category Service
 * @package  AppBundle\Service
 * @author   David Romaní <david@flux.cat>
 */
class AuditModelDiagramBridgeService
{
    const PDF_TOTAL_WIDHT      = 210;
    const DIAGRAM_HEIGHT       = 60;
    const GAP_SQUARE_SIZE      = 5;
    const GAP_SQUARE_HALF_SIZE = 2.5;

    /**
     * @var float
     */
    private $x1;

    /**
     * @var float
     */
    private $x2;

    /**
     * @var float
     */
    private $y1;

    /**
     * @var float
     */
    private $y2;

    /**
     * @var float
     */
    private $xQ1;

    /**
     * @var float
     */
    private $xQ2;

    /**
     * @var float
     */
    private $xQ3;

    /**
     * @var float
     */
    private $xQ4;

    /**
     * @var float
     */
    private $xQ5;

    /**
     * @var float
     */
    private $yQ1;

    /**
     * @var float
     */
    private $yQ2;

    /**
     * @var float
     */
    private $yMiddle;

    /**
     * @var float
     */
    private $yQ3;

    /**
     * @var float
     */
    private $yQ4;

    /**
     * @var float
     */
    private $xScaleGap;

    /**
     * @var float
     */
    private $yScaleGap;

    /**
     * @var array
     */
    private $bladeShape;

    /**
     * 
     * 
     * Methods
     * 
     * 
     */

    /**
     * AuditModelDiagramBridgeService constructor
     */
    public function __construct()
    {
        $this->x1 = CustomTcpdf::PDF_MARGIN_LEFT;
        $this->x2 = self::PDF_TOTAL_WIDHT - CustomTcpdf::PDF_MARGIN_RIGHT;
        $this->xQ1 = $this->x1 + 0.25;
        $this->xQ5 = $this->x2 - 0.25;
        $this->xScaleGap = $this->xQ5 - $this->xQ1;
        $this->xQ2 = $this->xQ1 + ($this->xScaleGap / 4);
        $this->xQ3 = $this->xQ2 + ($this->xScaleGap / 4);
        $this->xQ4 = $this->xQ3 + ($this->xScaleGap / 4);
        $this->bladeShape = array(
            0.570574080305441,
            0.577923063404412,
            0.590106432909933,
            0.608062018782336,
            0.633647645057639,
            0.668180278884672,
            0.712354562586671,
            0.766091601839969,
            0.827681103816886,
            0.891980459573338,
            0.949222288127351,
            0.987703271941501,
            1.000000000000000,
            0.986607024609017,
            0.954102183264805,
            0.911196131979670,
            0.865789976973706,
            0.823567971555419,
            0.787670763591748,
            0.759017362089391,
            0.736965757137396,
            0.720058642359332,
            0.706656262223690,
            0.695345886163577,
            0.685115527749747,
            0.675351354544384,
            0.665744104299468,
            0.656176274538586,
            0.646630532913632,
            0.637130999745297,
            0.627712386068213,
            0.618406897715452,
            0.609240271624142,
            0.600231610520974,
            0.591394401450540,
            0.582737684783291,
            0.574267062547622,
            0.565985499712510,
            0.557893946124258,
            0.549991817314795,
            0.542277366882693,
            0.534747975756082,
            0.527400377504407,
            0.520230834206199,
            0.513235273908621,
            0.506409398120520,
            0.499748765832901,
            0.493248859088797,
            0.486905134005711,
            0.473964410225382,
            0.273964410225382,
        );
    }
    
    /**
     * @param float $y
     *
     * @return $this
     */
    public function setYs($y)
    {
        $this->y1 = $y;
        $this->y2 = $y + self::DIAGRAM_HEIGHT;
        $this->yQ1 = $this->y1  + 5.5;
        $this->yQ2 = $this->yQ1 + 17;
        $this->yQ3 = $this->yQ2 + 16.75;
        $this->yQ4 = $this->yQ3 + 17;
        $this->yMiddle = $this->yQ2 + (($this->yQ3 - $this->yQ2) / 2) + 0.75;
        $this->yScaleGap = $this->yQ2 - $this->yQ1;

        return $this;
    }

    /**
     * @param BladeDamage $bladeDamage
     *
     * @return float
     */
    public function getGapX(BladeDamage $bladeDamage)
    {
        return $this->xQ1 + (($bladeDamage->getRadius() * $this->xScaleGap) / $bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength());
    }

    /**
     * @param BladeDamage $bladeDamage
     *
     * @return float
     */
    public function getGapXSize(BladeDamage $bladeDamage)
    {
        $result = (($bladeDamage->getSize() / 100) * $this->xScaleGap) / $bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength();
        if ($result < self::GAP_SQUARE_SIZE) {
            $result = self::GAP_SQUARE_SIZE;
        }

        return $result;
    }

    /**
     * @param BladeDamage $bladeDamage
     *
     * @return float
     */
    public function getGapY(BladeDamage $bladeDamage)
    {
        $gap = 0;
        if ($bladeDamage->getEdge() == BladeDamageEdgeEnum::EDGE_IN) {
            // Edge in
            if ($bladeDamage->getPosition() == BladeDamagePositionEnum::VALVE_PRESSURE) {

                // Valve pressure
                $xNormalization = ($bladeDamage->getRadius() * 50) / $bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength();
                $yPoint2 = $this->getBladeShape()[intval(round($xNormalization))];
                $maxY = (($bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength() / 30) - (2/3)) + 2;
                $yPoint = (($bladeDamage->getDistance() / 100) * $yPoint2) / $maxY;

                $gap = $this->yQ2 - (($this->yQ2 - $this->yQ1) * $yPoint);

            } elseif ($bladeDamage->getPosition() == BladeDamagePositionEnum::VALVE_SUCTION) {

                // Valve suction
                $xNormalization = ($bladeDamage->getRadius() * 50) / $bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength();
                $yPoint2 = $this->getBladeShape()[intval(round($xNormalization))];
                $maxY = (($bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength() / 30) - (2/3)) + 2;
                $yPoint = (($bladeDamage->getDistance() / 100) * $yPoint2) / $maxY;

                $gap = $this->yQ3 + (($this->yQ2 - $this->yQ1) * $yPoint);

            }

        } elseif ($bladeDamage->getEdge() == BladeDamageEdgeEnum::EDGE_OUT) {
            // Edge out
            if ($bladeDamage->getPosition() == BladeDamagePositionEnum::VALVE_PRESSURE) {

                // Valve pressure
                $xNormalization = ($bladeDamage->getRadius() * 50) / $bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength();
                $yPoint = $this->getBladeShape()[intval(round($xNormalization))];
                $baseGap = $this->yQ2 - (($this->yQ2 - $this->yQ1) * $yPoint);
                $yPoint2 = $this->getBladeShape()[intval(round($xNormalization))];
                $maxY = (($bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength() / 30) - (2/3)) + 2;
                $yPoint = (($bladeDamage->getDistance() / 100) * $yPoint2) / $maxY;

                $gap = $baseGap + (($this->yQ2 - $this->yQ1) * $yPoint);

            } elseif ($bladeDamage->getPosition() == BladeDamagePositionEnum::VALVE_SUCTION) {

                // Valve suction
                $xNormalization = ($bladeDamage->getRadius() * 50) / $bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength();
                $yPoint = $this->getBladeShape()[intval(round($xNormalization))];
                $baseGap = $this->yQ3 + (($this->yQ2 - $this->yQ1) * $yPoint);
                $yPoint2 = $this->getBladeShape()[intval(round($xNormalization))];
                $maxY = (($bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength() / 30) - (2/3)) + 2;
                $yPoint = (($bladeDamage->getDistance() / 100) * $yPoint2) / $maxY;

                $gap = $baseGap - (($this->yQ2 - $this->yQ1) * $yPoint);

            }

        } elseif ($bladeDamage->getEdge() == BladeDamageEdgeEnum::EDGE_UNDEFINED) {
            // No edge -> check valve position
            if ($bladeDamage->getPosition() == BladeDamagePositionEnum::EDGE_IN) {
                // Edge in
                $gap = $this->getYQ2();

            } elseif ($bladeDamage->getPosition() == BladeDamagePositionEnum::EDGE_OUT) {
                // Edge out
                $xNormalization = ($bladeDamage->getRadius() * 50) / $bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength();
                $yPoint = $this->getBladeShape()[intval(round($xNormalization))];

                $gap = $this->yQ2 - (($this->yQ2 - $this->yQ1) * $yPoint);
            }
        }

        return $gap;
    }

    /**
     * Get X1
     *
     * @return float
     */
    public function getX1()
    {
        return $this->x1;
    }

    /**
     * @param float $x1
     *
     * @return $this
     */
    public function setX1($x1)
    {
        $this->x1 = $x1;

        return $this;
    }

    /**
     * Get X2
     *
     * @return float
     */
    public function getX2()
    {
        return $this->x2;
    }

    /**
     * @param float $x2
     *
     * @return $this
     */
    public function setX2($x2)
    {
        $this->x2 = $x2;

        return $this;
    }

    /**
     * Get Y1
     *
     * @return float
     */
    public function getY1()
    {
        return $this->y1;
    }

    /**
     * @param float $y1
     *
     * @return $this
     */
    public function setY1($y1)
    {
        $this->y1 = $y1;

        return $this;
    }

    /**
     * Get Y2
     *
     * @return float
     */
    public function getY2()
    {
        return $this->y2;
    }

    /**
     * @param float $y2
     *
     * @return $this
     */
    public function setY2($y2)
    {
        $this->y2 = $y2;

        return $this;
    }

    /**
     * Get XQ1
     *
     * @return float
     */
    public function getXQ1()
    {
        return $this->xQ1;
    }

    /**
     * @param float $xQ1
     *
     * @return $this
     */
    public function setXQ1($xQ1)
    {
        $this->xQ1 = $xQ1;

        return $this;
    }

    /**
     * Get XQ2
     *
     * @return float
     */
    public function getXQ2()
    {
        return $this->xQ2;
    }

    /**
     * @param float $xQ2
     *
     * @return $this
     */
    public function setXQ2($xQ2)
    {
        $this->xQ2 = $xQ2;

        return $this;
    }

    /**
     * Get XQ3
     *
     * @return float
     */
    public function getXQ3()
    {
        return $this->xQ3;
    }

    /**
     * @param float $xQ3
     *
     * @return $this
     */
    public function setXQ3($xQ3)
    {
        $this->xQ3 = $xQ3;

        return $this;
    }

    /**
     * Get XQ4
     *
     * @return float
     */
    public function getXQ4()
    {
        return $this->xQ4;
    }

    /**
     * @param float $xQ4
     *
     * @return $this
     */
    public function setXQ4($xQ4)
    {
        $this->xQ4 = $xQ4;

        return $this;
    }

    /**
     * Get XQ5
     *
     * @return float
     */
    public function getXQ5()
    {
        return $this->xQ5;
    }

    /**
     * @param float $xQ5
     *
     * @return $this
     */
    public function setXQ5($xQ5)
    {
        $this->xQ5 = $xQ5;

        return $this;
    }

    /**
     * Get YQ1
     *
     * @return float
     */
    public function getYQ1()
    {
        return $this->yQ1;
    }

    /**
     * @param float $yQ1
     *
     * @return $this
     */
    public function setYQ1($yQ1)
    {
        $this->yQ1 = $yQ1;

        return $this;
    }

    /**
     * Get YQ2
     *
     * @return float
     */
    public function getYQ2()
    {
        return $this->yQ2;
    }

    /**
     * @param float $yQ2
     *
     * @return $this
     */
    public function setYQ2($yQ2)
    {
        $this->yQ2 = $yQ2;

        return $this;
    }

    /**
     * Get YMiddle
     *
     * @return float
     */
    public function getYMiddle()
    {
        return $this->yMiddle;
    }

    /**
     * @param float $yMiddle
     *
     * @return $this
     */
    public function setYMiddle($yMiddle)
    {
        $this->yMiddle = $yMiddle;

        return $this;
    }

    /**
     * Get YQ3
     *
     * @return float
     */
    public function getYQ3()
    {
        return $this->yQ3;
    }

    /**
     * @param float $yQ3
     *
     * @return $this
     */
    public function setYQ3($yQ3)
    {
        $this->yQ3 = $yQ3;

        return $this;
    }

    /**
     * Get YQ4
     *
     * @return float
     */
    public function getYQ4()
    {
        return $this->yQ4;
    }

    /**
     * @param float $yQ4
     *
     * @return $this
     */
    public function setYQ4($yQ4)
    {
        $this->yQ4 = $yQ4;

        return $this;
    }

    /**
     * Return blade shape array
     *
     * @return array
     */
    public function getBladeShape()
    {
        return $this->bladeShape;
    }

    /**
     * Draw a blade damage center point reference
     *
     * @param CustomTcpdf $pdf
     * @param BladeDamage $bladeDamage
     */
    public function drawCenterPoint(CustomTcpdf $pdf, BladeDamage $bladeDamage)
    {
        $x = $this->getGapX($bladeDamage);
        $y = $this->getGapY($bladeDamage);
        $pdf->Line($x, $y, $x + 0.5, $y, array('width' => 0.5, 'dash' => 0, 'color' => $this->hex2rgb($bladeDamage->getDamageCategory()->getColour())));

        if ($bladeDamage->getEdge() == BladeDamageEdgeEnum::EDGE_UNDEFINED) {
            if ($bladeDamage->getPosition() == BladeDamagePositionEnum::EDGE_IN) {
                // Edge in
                $pdf->Line($x, $y + $this->yQ3 - $this->yQ2, $x + 0.5, $y + $this->yQ3 - $this->yQ2, array('width' => 0.5, 'dash' => false, 'color' => $this->hex2rgb($bladeDamage->getDamageCategory()->getColour())));

            } elseif ($bladeDamage->getPosition() == BladeDamagePositionEnum::EDGE_OUT) {
                // Edge out
                $deltaY = $this->yQ2 - $y;
                $pdf->Line($x, $this->yQ3 + $deltaY, $x + 0.5, $this->yQ3 + $deltaY, array('width' => 0.5, 'dash' => false, 'color' => $this->hex2rgb($bladeDamage->getDamageCategory()->getColour())));
            }
        }
    }

    /**
     * Draw a blade damage rectangle reference
     *
     * @param CustomTcpdf $pdf
     * @param BladeDamage $bladeDamage
     * @param integer     $damageNumber
     */
    public function drawCenterDamage(CustomTcpdf $pdf, BladeDamage $bladeDamage, $damageNumber)
    {
        $pdf->setBackgroundHexColor($bladeDamage->getDamageCategory()->getColour());
        $x = $this->getGapX($bladeDamage);
        $y = $this->getGapY($bladeDamage);
        $w = $this->getGapXSize($bladeDamage);
        $pdf->Rect($x - self::GAP_SQUARE_HALF_SIZE, $y - self::GAP_SQUARE_HALF_SIZE, $w, self::GAP_SQUARE_SIZE, 'DF', array('all' => array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))));
        $pdf->MultiCell($w + 2, self::GAP_SQUARE_SIZE, $damageNumber, 0, 'C', false, 0, $x - self::GAP_SQUARE_HALF_SIZE - 1, $y - self::GAP_SQUARE_HALF_SIZE - 0.25, true);

        if ($bladeDamage->getEdge() == BladeDamageEdgeEnum::EDGE_UNDEFINED) {
            if ($bladeDamage->getPosition() == BladeDamagePositionEnum::EDGE_IN) {
                // Edge in
                $pdf->Rect($x - self::GAP_SQUARE_HALF_SIZE, $y - self::GAP_SQUARE_HALF_SIZE + $this->yQ3 - $this->yQ2, $w, self::GAP_SQUARE_SIZE, 'DF', array('all' => array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))));
                $pdf->MultiCell($w + 2, self::GAP_SQUARE_SIZE, $damageNumber, 0, 'C', false, 0, $x - self::GAP_SQUARE_HALF_SIZE - 1, $y - self::GAP_SQUARE_HALF_SIZE + $this->yQ3 - $this->yQ2 - 0.25, true);

            } elseif ($bladeDamage->getPosition() == BladeDamagePositionEnum::EDGE_OUT) {
                // Edge out
                $deltaY = $this->yQ2 - $y;
                $pdf->Rect($x - self::GAP_SQUARE_HALF_SIZE, $this->yQ3 + $deltaY - self::GAP_SQUARE_HALF_SIZE, $w, self::GAP_SQUARE_SIZE, 'DF', array('all' => array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))));
                $pdf->MultiCell($w + 2, self::GAP_SQUARE_SIZE, $damageNumber, 0, 'C', false, 0, $x - self::GAP_SQUARE_HALF_SIZE - 1, $this->yQ3 + $deltaY - self::GAP_SQUARE_HALF_SIZE, true);
            }
        }
    }

    /**
     * @param string $hex
     *
     * @return array
     */
    public function hex2rgb($hex)
    {
        $hex = str_replace('#', '', $hex);

        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }

        return array($r, $g, $b);
    }
}
