<?php
namespace Codeception\Test\Feature;

use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\StrictCoverage;
use PHPUnit\Runner\Version as PHPUnitVersion;

trait CodeCoverage
{
    /**
     * @return \PHPUnit\Framework\TestResult
     */
    abstract public function getTestResultObject();

    public function codeCoverageStart()
    {
        $testResult = $this->getTestResultObject();
        if (version_compare(PHPUnitVersion::series(), '10.0', '<')) {
            $codeCoverage = $testResult->getCodeCoverage();
        } else {
            $codeCoverage = $testResult->codeCoverage();
        }
        if (!$codeCoverage) {
            return;
        }
        $codeCoverage->start(Descriptor::getTestSignature($this));
    }

    public function codeCoverageEnd($status, $time)
    {
        $testResult = $this->getTestResultObject();
        if (version_compare(PHPUnitVersion::series(), '10.0', '<')) {
            $codeCoverage = $testResult->getCodeCoverage();
        } else {
            $codeCoverage = $testResult->codeCoverage();
        }
        if (!$codeCoverage) {
            return;
        }

        if ($this instanceof StrictCoverage) {
            $linesToBeCovered = $this->getLinesToBeCovered();
            $linesToBeUsed = $this->getLinesToBeUsed();
        } else {
            $linesToBeCovered = [];
            $linesToBeUsed = [];
        }

        try {
            $codeCoverage->stop(true, $linesToBeCovered, $linesToBeUsed);
        } catch (\PHP_CodeCoverage_Exception $cce) {
            if ($status === \Codeception\Test\Test::STATUS_OK) {
                $this->getTestResultObject()->addError($this, $cce, $time);
            }
        }
    }
}
