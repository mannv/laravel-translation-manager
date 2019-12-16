<?php

namespace Plum\SeleniumIdeManager\Models;

class TestCase extends BaseModel
{
    protected $table = 'sle_test_cases';

    protected $fillable = ['suite_id', 'name', 'first_test_case'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commands()
    {
        return $this->hasMany(Command::class, 'test_case_id', 'id')->orderBy('weight', 'ASC');
    }

    public function suite()
    {
        return $this->hasOne(Suite::class, 'id', 'suite_id');
    }

    public function createNewTestCase($suiteId, $name, $defaultTestCase = false)
    {
        $testCase = new static();
        $testCase->name = $name;
        $testCase->suite_id = $suiteId;
        $testCase->first_test_case = $defaultTestCase;
        $testCase->save();

        return $testCase;
    }

    public function updateFirstTestCase($suiteId, $testCaseId)
    {
        $this->where(['suite_id' => $suiteId])->update(['first_test_case' => false]);
        return $this->findOrFail($testCaseId)->update(['first_test_case' => true]);
    }

    public function getById($id)
    {
        return self::with(['suite', 'commands'])->findOrFail($id);
    }
}
