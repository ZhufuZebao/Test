<?php


namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConvertController
{
    //store the uploaded pdf file and make key
    public function storePdfFile(Request $request)
    {
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();
        if (!in_array(strtolower($ext), ['pdf'])) {
            //file format error
            return 'Wrong format';
        }
        $realPath = $file->getRealPath();
        $str = file_get_contents($realPath);
        $fileName = md5(time()) . "." . $ext;
        $pagePathDir = 'test/';
        $filePath = '/public/uploads/' . $pagePathDir . $fileName;
        Storage::disk('uploads')->put($pagePathDir . $fileName, $str);
        //convert the first page of this file
        $arr = $this->convertSinglePage($fileName, 1);
        $result['firstPagePath'] = '/uploads/' . $arr['firstPageImage'];
        $result['pageCount'] = $arr['pageCount'];
        return $result;
    }

    //check if this page's image has been created
    private function checkPageExist($key, $pageNum)
    {

    }

    //convert one pdf file to images(1 page -> 1 image)
    public function pdf2img()
    {
        //add max process time limit
        set_time_limit(0);
        $pathToPdf = public_path('uploads/') . 'test/test.pdf';
        $pathToWhereImageShouldBeStored = public_path('uploads/') . 'test/';

        $pdf = new Pdf($pathToPdf);
        //get page
        $pageCount = $pdf->getNumberOfPages();
        //store each page as png file
        for ($i = 1; $i <= $pageCount; $i++) {
            $fileName = 'test' . $i . '.png';
            $filePath = $pathToWhereImageShouldBeStored . $fileName;
            $pdf->setPage($i)->saveImage($filePath);
        }
    }

    //convert one page of a pdf file to one image
    public function getOnePage(Request $request)
    {
        $pageNum = $request->input('pageNum');
        $fileName = $request->input('fileName');

        set_time_limit(0);
        $pathToPdf = public_path('uploads/') . 'test/' . $fileName;
        $pathToWhereImageShouldBeStored = public_path('uploads/') . 'test/';

        $pdf = new Pdf($pathToPdf);
        $fileName = 'test' . $pageNum . '.png';
        $filePath = $pathToWhereImageShouldBeStored . $fileName;
        $pdf->setPage($pageNum)->saveImage($filePath);

        return $filePath;
    }

    //convert single page
    private function convertSinglePage($fileName, $pageNum)
    {
        set_time_limit(0);
        $pathToPdf = public_path('uploads/') . 'test/' . $fileName;
        $pathToWhereImageShouldBeStored = public_path('uploads/') . 'test/';

        $pdf = new Pdf($pathToPdf);
        //get page
        $pageCount = $pdf->getNumberOfPages();
        $imageName = 'test' . $pageNum . '.png';
        $filePath = $pathToWhereImageShouldBeStored . $imageName;
        $pdf->setPage($pageNum)->saveImage($filePath);
        $arr['pageCount'] = $pageCount;
        $arr['firstPageImage'] = $imageName;
        return $arr;
    }
}