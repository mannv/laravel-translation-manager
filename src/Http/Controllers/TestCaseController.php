<?php

namespace Plum\SeleniumIdeManager\Http\Controllers;

use Illuminate\Http\Response;
use Plum\SeleniumIdeManager\Models\Suite;
use Plum\SeleniumIdeManager\Models\TestCase;

class TestCaseController extends BaseController
{
    /**
     * @var TestCase
     */
    private $testCase;

    /**
     * @var Suite
     */
    private $suite;

    public function __construct(Suite $suite, TestCase $testCase)
    {
        parent::__construct();
        $this->testCase = $testCase;
        $this->suite = $suite;
    }

    public function update($id)
    {
        $suiteId = $this->request->get('suite_id');
        $this->testCase->updateFirstTestCase($suiteId, $id);
        $suite = $this->suite->getSuiteById($suiteId, ['testCases'])->toArray();
        $html = \View::make('seleniumidemanager::test_case', ['testCases' => $suite['test_cases'], 'suite' => $suite]);
        return $html;
    }

    public function show($id)
    {
        $testCase = $this->testCase->getById($id);
        return view('seleniumidemanager::test_case_show', ['data' => $testCase]);
    }
}
