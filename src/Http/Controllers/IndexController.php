<?php

namespace Plum\SeleniumIdeManager\Http\Controllers;

use Illuminate\Support\Str;
use Plum\SeleniumIdeManager\Models\Command;
use Plum\SeleniumIdeManager\Models\Suite;
use Plum\SeleniumIdeManager\Models\SuiteConfig;
use Plum\SeleniumIdeManager\Models\TestCase;

class IndexController extends BaseController
{
    /**
     * @var Suite
     */
    private $suite;

    /**
     * @var TestCase
     */
    private $testCase;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var SuiteConfig
     */
    private $suiteConfig;

    public function __construct(Suite $suite, TestCase $testCase, Command $command, SuiteConfig $suiteConfig)
    {
        parent::__construct();
        $this->suite = $suite;
        $this->testCase = $testCase;
        $this->command = $command;
        $this->suiteConfig = $suiteConfig;
    }

    public function index()
    {
        $result = $this->suite->getAll();
        if (!empty($result)) {
            $result = collect($result)->map(function ($item) {
                $values = json_decode($item['configs'][0]['variable_value'], true);
                $item['configs_count'] = count($item['configs']);
                $item['configs_rows'] = count($values);
                unset($item['configs']);
                return $item;
            })->all();
        }
        return view('seleniumidemanager::index', ['data' => $result]);
    }

    public function create()
    {
        $id = $this->request->get('id', 0);
        return view('seleniumidemanager::create', ['id' => $id]);
    }

    private function storageSiteFile()
    {
        $file = $this->request->file('file');
        $fileName = 'selenium/' . uniqid() . '.' . $file->getClientOriginalExtension();
        $driver = config('selenium_ide_manager.storage');
        \Storage::disk($driver)->put($fileName, \File::get($file), 'public');
        return \Storage::disk($driver)->url($fileName);
    }

    public function store()
    {
        if (!$this->request->hasFile('file')) {
            return redirect()->route('selenium-ide-manager.suite.index')->with('danger', 'Please choose file .site');
        }

        $oldSuite = [];
        $suiteId = $this->request->get('id', 0);
        if ($suiteId > 0) {
            $oldSuite = $this->suite->getSuiteById($suiteId)->toArray();
        }

        $filePath = $this->storageSiteFile();

        $jsonText = \File::get($this->request->file('file'));
        $json = json_decode($jsonText, true);
        $suite = array_shift($json['suites']);
        $defaultTestId = array_shift($suite['tests']);

        try {
            \DB::beginTransaction();

            $suiteName = $suite['name'];
            $suite = $this->suite->createNewSuite($suiteName, $filePath, $oldSuite);

            if (!empty($json['tests'])) {
                foreach ($json['tests'] as $test) {
                    if (Str::lower($test['name']) == 'config') {
                        $this->makeSuiteConfig($suite->id, $test);
                        continue;
                    }

                    $defaultTestCase = $defaultTestId == $test['id'];
                    $testCaste = $this->testCase->createNewTestCase($suite->id, $test['name'], $defaultTestCase);
                    if (!empty($test['commands'])) {
                        foreach ($test['commands'] as $index => $item) {
                            $this->command->createNewCommand([
                                'suite_id' => $suite->id,
                                'test_case_id' => $testCaste->id,
                                'comment' => $item['comment'],
                                'command' => $item['command'],
                                'target' => $item['target'],
                                'value' => $item['value'],
                                'targets' => json_encode($item['targets']),
                                'weight' => $index
                            ]);
                        }
                    }
                }
            }
            \DB::commit();

            if ($suiteId > 0) {
                $this->deleteSuite($suiteId);
            }

        } catch (\Exception $exception) {
            \DB::rollBack();
            app('log')->error($exception);
        }
        return redirect()->route('selenium-ide-manager.suite.index')->with('success', 'Import suite success');
    }

    private function makeSuiteConfig($suiteId, $test)
    {
        if (empty($test['commands'])) {
            return;
        }
        foreach ($test['commands'] as $item) {
            if ($item['command'] != 'store') {
                continue;
            }
            $this->suiteConfig->createNewSuiteConfig($suiteId, $item['value'], json_encode([$item['target']]));
        }
    }

    private function deleteFileSite($url)
    {
        $driver = config('selenium_ide_manager.storage');
        $storage = \Storage::disk($driver);
        $storageUrl = $storage->getDriver()->getConfig()->get('url');
        $path = Str::replaceFirst($storageUrl, '', $url);
        \Storage::disk($driver)->delete($path);
    }

    private function deleteSuite($id)
    {
        $suite = $this->suite->getSuiteById($id);
        $this->deleteFileSite($suite->ide_file_path);
        $suite->deleteById($id);
    }

    public function destroy($id)
    {
        $this->deleteSuite($id);
        return redirect()->route('selenium-ide-manager.suite.index')->with('success', 'Delete suite success');
    }

    public function changeStatus($id)
    {
        $suite = $this->suite->getSuiteById($id);
        return $this->suite->changeStatus($id, $suite->status);
    }
}
