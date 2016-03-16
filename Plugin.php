<?php namespace Fes\Birthday;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Fes\Birthday\Components\AgeList' => 'AgeList',
            'Fes\Birthday\Components\RecordList' => 'recordList',
            'Fes\Birthday\Components\RecordDetails' => 'recordDetails'
        ];
    }

    public function registerSettings()
    {
    }
}
