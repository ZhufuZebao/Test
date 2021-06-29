<?php

namespace App\Http\Services;

use Exception;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class JapaneseDateTime extends Carbon
{
    private $monthData = [];

    public function __construct($time = null, $time_zone = null)
    {
        parent::__construct($time, $time_zone);
    }

    /*
    * 六曜を取得
    */
    public function getSixWeekdayText() {
        $date = $this->isoFormat('YYYY-MM-DD');
        // 該当月のデータを取得
        $monthData = $this->getMonthData();
        // データある場合
        if (array_key_exists('datelist', $monthData) && array_key_exists($date, $monthData['datelist'])) {
            // 六曜を取得
            return $monthData['datelist'][$date]['rokuyou'];
        }
        return '';
    }

    /*
    * 祝日を取得
    */
    public function getHolidayText() {
        $date = $this->isoFormat('YYYY-MM-DD');
        // 該当月のデータを取得
        $monthData = $this->getMonthData();
        // データある場合
        if (array_key_exists('datelist', $monthData) && array_key_exists($date, $monthData['datelist'])) {
            // 祝日を取得
            return $monthData['datelist'][$date]['holiday'];
        }
        return '';
    }

    /*
    * 月データを取得
    */
    public function getMonthData()
    {
        $month = $this->isoFormat('YYYY-MM');
        // 取得済み場合
        if (!array_key_exists($month, $this->monthData)) {
            // Redisから検索
            $monthDataStr = Redis::get('calendar:month:' . $month);
            // $monthDataStr = Cache::get('calendar:month:' . $month);
            // Redisに存在しない
            if (!$monthDataStr) {
                try {
                    Log::info('month:' . $month. '\'s cache cannot be found.');
                    // WEBから取得
                    $url = 'http://koyomi.zing2.org/api/';
                    $client = new Client();
                    $param = ['form_params' => array(
                        'mode' => "m",
                        'cnt'  => "1",
                        'targetyyyy' => $this->year,
                        'targetmm' => $this->month
                    )];
                    $response = $client->post($url, $param);
                    $monthDataStr = (string)$response->getBody();
                    // Redisに30日間保存
                    Redis::set('calendar:month:' . $month, $monthDataStr, 'ex', 60 * 60 * 24 * 30);
                    // Cache::put('calendar:month:' . $month, $monthDataStr, 60 * 24 * 30);
                } catch (Exception $e) {
                    Log::error($e->getMessage(), $e->getTrace());
                }
            }
            // 取得済み
            $this->monthData[$month] = json_decode($monthDataStr, true);
        }
        // 月データを取得
        return $this->monthData[$month];
    }
    
    /**
     * MagicMethod:__get()
     *
     * @param string $name
     */
    public function __get($name)
    {
        switch ($name) {
            case 'six_weekday_text':
            case 'sixWeekdayText':
                return $this->getSixWeekdayText();
            case 'holiday_text':
            case 'holidayText':
                return $this->getHolidayText();
        }

        return parent::__get($name);
    }
}