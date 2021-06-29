<?php
use App\User;

if (! function_exists('showHtmlChatMessage')) {

    /**
     * チャットメッセージを表示用に編集する
     *
     * @param   string  $msg    編集前のメッセージ
     * @param   string  $dir    添付ファイルがあるディレクトリ
     * @return  string  編集したメッセージ
     */
    function showHtmlChatMessage($msg, $dir)
    {
        $users = new User();
        $qt_mark = array();
        $qt_str  = array();
        $offset = 0;

        $pos = false;
        $pos2 = false;

        // 引用の編集
        while(true) {
            $qt_name = '';
            $qt_time = '';

            $pos = mb_strpos($msg, '[time:', $offset);
            if ($pos === false) {
                break;
            }

            $pos2 = mb_strpos($msg, ']', $pos+1);
            $tmp = mb_substr($msg, $pos+1, $pos2-$pos-1, 'UTF-8');
            $tmp2 = preg_split("/ /", $tmp);
            foreach ($tmp2 as $item) {
                $tmp3 = preg_split("/:/", $item);
                $tmp4[$tmp3[0]] = $tmp3[1];
            }

            if (isset($tmp4['id'])) {
                $qt_name = $users->getUserName($tmp4['id']);
            }
            if (isset($tmp4['time'])) {
                $qt_time = date('Y-m-d H:i:s', $tmp4['time']);
            }
            $qt_mark[] = $tmp;
            $qt_str[] = '<p style="color:silver;">'. $qt_name. ' '. $qt_time. '</p><br />';

            $offset = $pos2 + 1;
        }
//print_r($qt_mark);
//print_r($qt_str);

        // To の編集
        while (true){
            $pos = mb_strpos($msg, '[To:');
            if ($pos === false) {
                break;
            }
            $tmp = '';
            if ($pos > 0) {
                $tmp .= mb_substr($msg, 0, $pos, 'UTF-8');
            }
            $tmp .= '<img src="'. asset('/images/m_icon/to2.png'). '" width="28" /> ';

            $pos2 = mb_strpos($msg, ']', $pos+1);
            $tmp .= mb_substr($msg, $pos2+1, mb_strlen($msg)-$pos2+1, 'UTF-8');

            $msg = $tmp;
        }

        // 返信の編集
        while (true){
            $pos = mb_strpos($msg, '[mid:');
            if ($pos === false) {
                break;
            }
            $tmp = '';
            if ($pos > 0) {
                $tmp .= mb_substr($msg, 0, $pos, 'UTF-8');
            }
            //$tmp .= 'Re:';
            $tmp .= '<img src="'. asset('/images/m_icon/reply.png'). '" width="35" /> ';

            $pos2 = mb_strpos($msg, ']', $pos+1);
            $tmp .= mb_substr($msg, $pos2+1, mb_strlen($msg)-$pos2+1, 'UTF-8');

            $msg = $tmp;
        }

        // アップロードファイル
        while (true){
            $pos  = mb_strpos($msg, '[upload]');
            if ($pos === false) {
                break;
            }
            $pos2 = mb_strpos($msg, '[/upload]', $pos+1);
            $file_name = mb_substr($msg, $pos+8, $pos2-$pos-8);

            $type = preg_split('/\./', $file_name);
//print_r($type);
//exit();
            $idx = count($type) - 1;
            $extension = mb_strtolower($type[$idx], 'UTF-8');
//echo 'extension='. $extension. '<br>';
            $preview_flg = false;
            if ($extension == 'jpg' || $extension == 'jpeg' ||
                $extension == 'png' || $extension == 'gif'
            ) {
                $preview_flg = true;
            }

            $tmp  = mb_substr($msg, 0, $pos, 'UTF-8');

            $tmp .= '<div class="upload_file">'
                        . '<div class="upload_file_title">☆ファイルをアップロードしました。</div>'
                        . '<div class="upload_file_file">';

            if ($preview_flg == true) {
                $tmp .= '<img src="'. asset('/upload/'. $dir. '/'. $file_name). '" width="50" /><br />';
            }

            $tmp .=  '</div>'
                    . $file_name. '<br />'
                . '</div>';

            $tmp .= mb_substr($msg, $pos2+9);
            $msg = $tmp;
        }

        // 引用タグを置換
        $msg = preg_replace('/\[qt\]/', '<blockquote>', $msg);
        $msg = preg_replace('/\[\/qt\]/', '</blockquote>', $msg);

        if (is_array($qt_mark) && !empty($qt_mark) && isset($qt_mark[0]) && $qt_mark[0] != '') {
            foreach ($qt_mark as $key => $mark) {
                $msg = preg_replace('/\['. $mark. '\]/', $qt_str[$key], $msg);
            }
        }

        // 絵文字を画像に置換
        for ($i = 1; $i <= 10; $i++) {
            $b = '/\[icon:'. sprintf('%03d', $i). '\]/';
            $a = '<img src="'. asset('/images/m_icon/k_'. sprintf('%03d', $i). '.gif'). '">';
            $msg = preg_replace($b, $a, $msg);
        }

        // 改行コードを改行タグに置換
        return preg_replace("/\n/", "<br />", $msg);
    }
}
