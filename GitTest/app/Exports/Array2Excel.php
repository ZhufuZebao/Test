<?php 
namespace App\Exports;

use \App\Task;

class Array2Excel implements \Maatwebsite\Excel\Concerns\FromView
{
    public $thead = [];
    public $tbody = [];

    public function __construct($thead, $tbody)
    {
        $this->thead = $thead;
        $this->tbody = $tbody;
    }
    
    public function view(): \Illuminate\Contracts\View\View
    {
        return view('exports.excel', [
            'thead' => $this->thead,
            'tbody' => $this->tbody,
        ]);
    }

}

