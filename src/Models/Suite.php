<?php

namespace Plum\SeleniumIdeManager\Models;

class Suite extends BaseModel
{
    protected $table = 'sle_suites';

    protected $fillable = ['name', 'ide_file_path', 'description', 'status', 'hex_color'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function testCases()
    {
        return $this->hasMany(TestCase::class, 'suite_id', 'id');
    }

    public function configs()
    {
        return $this->hasMany(SuiteConfig::class, 'suite_id', 'id');
    }

    public function createNewSuite($name, $path = null, $oldSuite = null)
    {
        $suite = new static();
        $suite->name = $name;
        $suite->ide_file_path = $path ?? 'N/A';
        $suite->status = $oldSuite['suite'] ?? true;
        if (!empty($oldSuite['hex_color'])) {
            $suite->hex_color = $oldSuite['hex_color'];
        }
        $suite->save();

        return $suite;
    }

    public function getSuiteById($id, $relations = [])
    {
        if (!empty($relations)) {
            return $this->with($relations)->findOrFail($id);
        }
        return $this->findOrFail($id);
    }

    public function deleteById($id)
    {
        TestCase::where(['suite_id' => $id])->delete();
        Command::where(['suite_id' => $id])->delete();
        SuiteConfig::where(['suite_id' => $id])->delete();
        return $this->find($id)->delete();
    }

    public function getAll()
    {
        return $this->with(['configs', 'testCases'])->get()->toArray();
    }

    public function changeStatus($id, $status)
    {
        $this->find($id)->update(['status' => !$status]);
    }

    public function getAllSuite()
    {
        return $this->with([
            'configs',
            'testCases' => function ($query) {
                return $query->with([
                    'commands' => function ($query) {
                        return $query->select(['id', 'test_case_id', 'command', 'target', 'value'])->orderBy('weight',
                            'ASC');
                    }
                ]);
            }
        ])->where(['status' => true])->get()->toArray();
    }
}
