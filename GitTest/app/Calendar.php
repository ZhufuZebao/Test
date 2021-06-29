<?php
/**
 * カレンダー作成のクラス
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Calendar extends Model
{
    /**
     * カレンダーを生成
     *
     * @return  array   日付の配列
     */
    public function makeCalendar($ref)
    {
        $days = $this->_getCalendar($ref);
        //echo 'year='. $days->YEAR;
        //print_r($days);
        //exit();

        $year = (int)$days->YEAR;
        $mon = (int)$days->MON;
        $day_st = $days->DAY_ST;
        $day_ed = $days->DAY_ED;
        $week = $days->WEEK;

        $daysArray = array();
        $cnt = 0;

        // カレンダーの始まりが月曜でなかった時
        if ($week != 1) {

            $days_b = $this->_getCalendar($ref - 1);

            $m = (int)$days_b->MON;

            // 月曜～カレンダーの始まりまで空文字で埋める
            for ($i = 1, $d = $days_b->DAY_ED - ($week - 2); $i < $week; $i ++, $cnt ++, $d++) {
//                $daysArray[$cnt] = '&nbsp;'; //(int)$days_b->YEAR. '-'. $m . '-'. $d;
                $daysArray[$cnt] = '';
            }
        }

        // 上記の続きから・・・月の初め～月の終りまで日付を埋める
        for ($i = $day_st; $i <= $day_ed; $i ++, $cnt ++) {
            $daysArray[$cnt] = $i;
        }

        // $daysの配列が7日間で割り切れない場合、残りの数日間は次月
        $a = $cnt % 7;
        if ($a > 0) {
            for ($i = $a, $d = 1; $i < 7; $i ++, $cnt ++, $d++) {
//                $daysArray[$cnt] = '&nbsp;'; //$year. '-'. $m. '-'. $d;
                $daysArray[$cnt] = '';
            }
        }
        //print_r($daysArray);

        return $daysArray;
    }

    /**
     * カレンダー生成用の日付を取得
     *
     * @param   int     $mon    参照（... -1=1ヶ月前, 0=今月, 1=1ヶ月後 ...）
     * @return  array   取得したデータ
     */
    private function _getCalendar($mon)
    {
        // 指定の年, 指定の月, 指定の１日, 指定の月末, 指定の１日の曜日
        // 1～7：日～土 （MySQLが返す曜日は0～6ですが+1しています）
        $sql = "
        select  date_format(date_add(current_date, interval ? month), '%Y') as YEAR
              , date_format(date_add(current_date, interval ? month), '%m') as MON
              , 1 as DAY_ST
              , date_format(last_day(date_add(current_date, interval ? month)), '%d') as DAY_ED
              , date_format(date_add(last_day(date_add(current_date, interval ? -1 month)), interval 1 day), '%w') + 1 as WEEK
        ";

        $rows = DB::select($sql, [$mon, $mon, $mon, $mon]);

        return $rows[0];
    }

    public function getWeekCalendar($ref)
    {
        $date = strtotime(date('Y/m/d'). ' '. ($ref * 7). ' day');
//echo 'date='. $date. '<br>';
//echo 'date*='. date('Y/m/d', $date). '<br>';

        $week = array();

        $day = strtotime('-' . date('w', $date) . 'day', $date);
        for ($i=0; $i<7; $i++) {
            $week[]  = date('Y/m/d', strtotime('+' . $i . 'day', $day));
        }

        return $week;
    }

    private function _getWeekCalendar($ref)
    {
        $day = $ref * 7;

        $sql = <<< EOF
select  date_format(date_add(current_date, interval ? day), '%Y') as year
      , date_format(date_add(current_date, interval ? day), '%m') as month
      , date_format(date_add(current_date, interval ? day), '%d') as day
      , date_format(date_add(current_date, interval ? day), '%w') + 1 as week
      , date_format(last_day(date_add(current_date, interval ? day)), '%d') lastday
EOF;

        $rows = DB::select($sql, [$day, $day, $day, $day, $day]);

        return $rows[0];
    }
}