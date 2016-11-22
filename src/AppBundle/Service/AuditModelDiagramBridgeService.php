<?php

namespace AppBundle\Service;

use AppBundle\Entity\Blade;
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
     * @var
     */
    private $maxFunctionYPoint;

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
     * Get MaxFunctionYPoint
     *
     * @return mixed
     */
    public function getMaxFunctionYPoint()
    {
        return $this->maxFunctionYPoint;
    }

    /**
     * Set MaxFunctionYPoint
     *
     * @param mixed $maxFunctionYPoint
     *
     * @return $this
     */
    public function setMaxFunctionYPoint($maxFunctionYPoint)
    {
        $this->maxFunctionYPoint = $maxFunctionYPoint;

        return $this;
    }

    /**
     * @param Blade $blade
     *
     * @return float|int
     */
    public function calculateMaxFunctionYPoint(Blade $blade)
    {
        $maxY = 0;
        $bladeLength = $blade->getLength();
        for ($x = 0; $x <= $bladeLength; $x = $x + 0.5) {
            $val = $this->isolatedDeltaY($x, $blade->getLength());
            if ($maxY < $val) {
                $maxY = $val;
            }
        }
        $this->setMaxFunctionYPoint($maxY);

        return $maxY;
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
     * @param int         $inversed
     *
     * @return float
     */
    public function getGapY(BladeDamage $bladeDamage, $inversed = 1)
    {
        $gap = 0;
        if ($bladeDamage->getEdge() == BladeDamageEdgeEnum::EDGE_IN) {
            // Edge in
            if ($bladeDamage->getPosition() == BladeDamagePositionEnum::VALVE_PRESSURE) {
                // Valve pressure
                $gap = $this->yQ2 - $this->yCalculateFactor($bladeDamage);

            } elseif ($bladeDamage->getPosition() == BladeDamagePositionEnum::VALVE_SUCTION) {
                // Valve suction
                $gap = $this->yQ3 + $this->yCalculateFactor($bladeDamage);
            }

        } elseif ($bladeDamage->getEdge() == BladeDamageEdgeEnum::EDGE_OUT) {
            // Edge out
            if ($bladeDamage->getPosition() == BladeDamagePositionEnum::VALVE_PRESSURE) {
                // Valve pressure
//                $gap = $this->yQ2 - $this->yCalculateFactorEdgeOut($bladeDamage);
                $y = $this->isolatedDeltaY($bladeDamage->getRadius(), $bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength());
                $gap = $this->yQ2 - (($y / $this->getMaxFunctionYPoint() * $this->yScaleGap));

            } elseif ($bladeDamage->getPosition() == BladeDamagePositionEnum::VALVE_SUCTION) {
                // Valve suction
                $gap = $this->yQ3 + $this->yCalculateFactorEdgeOut($bladeDamage);
            }

        } elseif ($bladeDamage->getEdge() == BladeDamageEdgeEnum::EDGE_UNDEFINED) {
            // No edge -> check valve position
            if ($bladeDamage->getPosition() == BladeDamagePositionEnum::EDGE_IN) {
                // Edge in
                $gap = $this->getYQ2();

            } elseif ($bladeDamage->getPosition() == BladeDamagePositionEnum::EDGE_OUT) {
                // Edge out
                $gap = $this->yQ3 + $this->yCalculateFactorEdgeOut($bladeDamage, $inversed);
            }
        }

        return $gap;
    }

    /**
     * @param BladeDamage $bladeDamage
     *
     * @return float
     */
    public function yCalculateFactor(BladeDamage $bladeDamage)
    {
        return (($bladeDamage->getDistance() / 100) * $this->getYScaleGap()) / $this->getMaxFunctionYPoint();
    }

    /**
     * @param BladeDamage $bladeDamage
     * @param int         $inversed
     *
     * @return float
     */
    public function yCalculateFactorEdgeOut(BladeDamage $bladeDamage, $inversed = 1)
    {
        $x = $bladeDamage->getRadius();
        $pow = $this->isolatedDeltaY($x, $bladeDamage->getAuditWindmillBlade()->getWindmillBlade()->getWindmill()->getBladeType()->getLength());
        $valve = ($pow * $this->getYScaleGap()) / $this->getMaxFunctionYPoint();

        return $inversed * ($valve - (((($bladeDamage->getDistance() / 100)) * $this->getYScaleGap()) / $this->getMaxFunctionYPoint()));
    }

    /**
     * @param float $x
     * @param float $bladeLength
     *
     * @return float
     */
    private function isolatedDeltaY($x, $bladeLength)
    {
        $n = ($this->xScaleGap / 20) * 4.5;
        $factor = (200 * ($x / $bladeLength)) / $n;
        $base = pow(M_E, (pow((0.7 * pow($factor, 1.5) - 0.4), 2) * -1)) * $n * 0.07;
        $bladeBase = (atan((pow($factor, 3) - pow($factor, 2) * 0.5)) / ($factor + 3)) * $n * 0.4;

        return ($bladeBase / 1.5) + $base;
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
     * Get XScaleGap
     *
     * @return float
     */
    public function getXScaleGap()
    {
        return $this->xScaleGap;
    }

    /**
     * Set XScaleGap
     *
     * @param float $xScaleGap
     *
     * @return $this
     */
    public function setXScaleGap($xScaleGap)
    {
        $this->xScaleGap = $xScaleGap;

        return $this;
    }

    /**
     * Get YScaleGap
     *
     * @return float
     */
    public function getYScaleGap()
    {
        return $this->yScaleGap;
    }

    /**
     * Set YScaleGap
     *
     * @param float $yScaleGap
     *
     * @return $this
     */
    public function setYScaleGap($yScaleGap)
    {
        $this->yScaleGap = $yScaleGap;

        return $this;
    }

    /**
     * Draw a blade damage center point reference
     *
     * @param \TCPDF $pdf
     * @param BladeDamage $bladeDamage
     */
    public function drawCenterPoint(\TCPDF $pdf, BladeDamage $bladeDamage)
    {
//        $this->Line($x, $y, $x + 0.5, $y, array('width' => 0.5, 'color' => $this->hex2rgb($hexColor)));
        $pdf->Line($this->getGapX($bladeDamage), $this->getGapY($bladeDamage), $this->getGapX($bladeDamage) + 0.5, $this->getGapY($bladeDamage), array('width' => 0.5, 'dash' => false, 'color' => $this->hex2rgb($bladeDamage->getDamageCategory()->getColour())));
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
