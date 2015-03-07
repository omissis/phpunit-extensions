<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Phpunit\Listener;

use Humbug\Phpunit\Filter\FilterInterface;
use Humbug\Phpunit\Filter\TestSuite\AbstractFilter as TestSuiteFilter;

class FilterListener extends \PHPUnit_Framework_BaseTestListener
{

    protected $rootSuiteName;

    protected $currentSuiteName;

    protected $suiteFilters = [];

    protected $suiteLevel = 0;

    public function __construct()
    {
        $args = func_get_args();
        if (empty($args)) {
            throw new \Exception(
                'No Humbug\Filter\FilterInterface objects assigned to FilterListener'
            );
        }
        foreach ($args as $filter) {
            $this->addFilter($filter);
        }
    }

    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->suiteLevel++;
        $this->currentSuiteName = $suite->getName();
        if ($this->suiteLevel == 1) {
            $this->rootSuiteName = $suite->getName();
            $suites = $suite->tests();
            $filtered = $this->filterSuites($this->rootSuiteName, $suites);
            $suite->setTests($filtered);
        }
    }

    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->suiteLevel--;
    }

    protected function filterSuites($parent, array $suites)
    {
        $filtered = $suites;
        foreach ($this->suiteFilters as $filter) {
            $filtered = $filter->filter($parent, $filtered);
        }
        return $filtered;
    }

    protected function addFilter(FilterInterface $filter)
    {
        if ($filter instanceof TestSuiteFilter) {
            $this->suiteFilters[] = $filter;
        }
    }

}