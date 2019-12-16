<?php

namespace Plum\SeleniumIdeManager\Models;

class Command extends BaseModel
{
    protected $table = 'sle_commands';

    protected $fillable = ['suite_id' ,'test_case_id', 'comment', 'command', 'target', 'value', 'targets', 'weight'];

    public function createNewCommand(array $attributes)
    {
        $command = new static();
        $command->suite_id = $attributes['suite_id'];
        $command->test_case_id = $attributes['test_case_id'];
        $command->comment = $attributes['comment'];
        $command->command = $attributes['command'];
        $command->target = $attributes['target'];
        $command->value = $attributes['value'];
        $command->targets = $attributes['targets'];
        $command->weight = $attributes['weight'];
        $command->save();

        return $command;
    }
}
