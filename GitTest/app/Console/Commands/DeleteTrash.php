<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Storage;

class DeleteTrash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trash:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear files older than 24 hours and delete empty folders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //get path
        $path = Storage::disk(config('web.pdfUpload.disk'))->allFiles('upload');
        for ($i=0;$i<count($path);$i++){
            $fileInfo[$i]['modify'] = Storage::disk(config('web.pdfUpload.disk'))->lastModified($path[$i]);
            $fileInfo[$i]['path'] = $path[$i];
            if ((time() - $fileInfo[$i]['modify']) > 86400){
                Storage::disk(config('web.pdfUpload.disk'))->delete($fileInfo[$i]['path']);
            }
        }
        //get dirs
        $directories = Storage::disk(config('web.pdfUpload.disk'))->allDirectories('upload');
        foreach($directories as $dir) {
            $files = Storage::disk(config('web.pdfUpload.disk'))->allFiles($dir);
            //if this dir has no file,delete it
            if(!count($files)) {
                Storage::disk(config('web.pdfUpload.disk'))->deleteDirectory ($dir);
            }
        }
    }
}
