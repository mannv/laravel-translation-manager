<?php

namespace Plum\SeleniumIdeManager\Http\Controllers;

use Illuminate\Http\Response;
use Plum\SeleniumIdeManager\Models\Suite;
use Plum\SeleniumIdeManager\Models\SuiteConfig;
use Plum\SeleniumIdeManager\Models\TestCase;

class SuiteConfigController extends BaseController
{
    /**
     * @var SuiteConfig
     */
    private $suiteConfig;

    /**
     * @var Suite
     */
    private $suite;

    public function __construct(SuiteConfig $suiteConfig, Suite $suite)
    {
        parent::__construct();
        $this->suiteConfig = $suiteConfig;
        $this->suite = $suite;
    }

    public function edit($id)
    {
        $suite = $this->suite->getSuiteById($id);

        $configs = $this->suiteConfig->getBySuiteId($id);
        $configs = collect($configs)->map(function ($item) {
            $item['variable_value'] = json_decode($item['variable_value'], true);
            return $item;
        })->all();
        return view('seleniumidemanager::config.edit',
            [
                'suite_name' => $suite->name,
                'id' => $id,
                'data' => $configs,
                'total_rows' => count($configs[0]['variable_value'])
            ]
        );
    }

    public function update($id)
    {
        $params = request()->get('variable_name');
        if (empty($params)) {
            return redirect()->back()->with('danger', 'Data not found');
        }

        foreach ($params as $id => $values) {
            $this->suiteConfig->updateConfig($id, $values);
        }

        return redirect()->back()->with('success', 'Update config success');
    }
}
