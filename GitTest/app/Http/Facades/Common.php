<?php
/**
 * @author: Song Zhiqiang
 */

namespace App\Http\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static upload(\Illuminate\Http\Request $request, string $type)
 * @method static getFile(string $type, string $file_name, boolean $isDownload = false)
 * @method static getFileSize(string $type, string $file_name)
 * @method static zipCloud($request)
 * @method static getJapaneseMonthWithOthers($monthDate)
 * @method static getJapaneseThreeMonthWithOthers($monthDate)
 * @method static getJapaneseWeek($date)
 * @method static escapeDBSelectKeyword($str)
 */
class Common extends Facade
{
    /**
     * Facade名前を取得
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Common';
    }
}
