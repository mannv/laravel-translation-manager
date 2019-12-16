<?php

namespace Plum\SeleniumIdeManager\Http\Controllers;

use Illuminate\Support\Str;
use Plum\SeleniumIdeManager\Models\Suite;

class ExportController extends BaseController
{
    /**
     * @var Suite
     */
    private $suite;

    public function __construct(Suite $suite)
    {
        parent::__construct();
        $this->suite = $suite;
    }

    public function index()
    {
        $result = $this->suite->getAllSuite();
        if (empty($result)) {
            return redirect()->route('selenium-ide-manager.suite.index')->with('danger', 'Data not found');
        }

        $data = [];
        foreach ($result as $suite) {
            $testCases = $this->getTestCase($suite['test_cases'], $firstTestCase);
            $data[] = [
                'name' => $suite['name'],
                'configs' => $this->getSuiteConfig($suite['configs']),
                'first_test_case' => $firstTestCase,
                'test_case' => $testCases
            ];
        }

        $filePath = storage_path('app/test_case.json');
        \File::put($filePath, json_encode($data));

        return response()->download($filePath)->deleteFileAfterSend();
    }

    private function getTestCase($testCase, &$firstTestCase)
    {
        $listTestCase = [];
        foreach ($testCase as $item) {
            if ($item['first_test_case'] == 1) {
                $firstTestCase = $item['name'];
            }

            $listTestCase[$item['name']] = collect($item['commands'])->map(function ($item) {
                unset($item['id']);
                unset($item['test_case_id']);

                if ($item['command'] == 'open') {
                    $item['target'] = $this->filterDomain($item['target']);
                }

                return $item;
            })->all();
        }
        return $listTestCase;
    }

    private function filterDomain($url)
    {
        $replaceDomain = config('app.domain.frontend');
        $arr = parse_url($replaceDomain);
        $replaceDomain = $arr['host'];


        $domains = ['local.vitop-career.com', 'test.vitop-career.com', 'vitop.vn'];
        foreach ($domains as $domain) {
            if (Str::contains($url, $domain)) {
                $url = Str::replaceFirst($domain, $replaceDomain, $url);
            }
        }
        $url = Str::replaceFirst('http://', 'https://', $url);
        return $url;
    }

    private function getSuiteConfig($configs)
    {
        if (empty($configs)) {
            return [];
        }

        $values = json_decode($configs[0]['variable_value'], true);
        $totalRows = count($values);

        $response = [];

        for ($i = 0; $i < $totalRows; $i++) {
            $list = [];
            foreach ($configs as $item) {
                $values = json_decode($item['variable_value'], true);
                $list[] = [
                    'name' => $item['variable_name'],
                    'value' => $values[$i],
                ];
            }
            $response[] = $list;
        }
        return $response;
    }
}
