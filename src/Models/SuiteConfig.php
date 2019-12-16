<?php

namespace Plum\SeleniumIdeManager\Models;

class SuiteConfig extends BaseModel
{
    protected $table = 'sle_suite_config';

    protected $fillable = ['suite_id', 'variable_name', 'variable_value'];

    public function createNewSuiteConfig($suiteId, $name, $value)
    {
        $config = new static();
        $config->suite_id = $suiteId;
        $config->variable_name = $name;
        $config->variable_value = $value;
        $config->save();

        return $config;
    }

    public function getBySuiteId($id)
    {
        return self::where(['suite_id' => $id])->orderBy('id', 'ASC')->get()->toArray();
    }

    public function updateConfig($id, $values)
    {
        return $this->findOrFail($id)->update(['variable_value' => json_encode($values)]);
    }
}
