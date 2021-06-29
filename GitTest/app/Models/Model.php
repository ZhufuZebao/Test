<?php
/**
 * @author  Reiko Mori
 */

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * 更新: 直前にChangeLogへ記録する
     */
    public function update(array $attributes = [], array $options = [])
    {
        event(new \App\Events\EditedByUser($this));

        return parent::update($attributes, $options);
    }

    public function validateImageFile(Request $request, $key = 'file', $isRequired = false)
    {
        $rules = [
            $key => [$isRequired ? 'required' : 'nullable',
                // アップロードされたファイルであること
                'file',
                // 画像ファイルであること
                'image',
                // MIMEタイプを指定
                'mimes:jpeg,gif,png',
                // サイズ制限を指定
                'max:' . (config('web.imageUpload.sizeLimit') * 1024)],
        ];
        return Validator::make($request->all(), $rules);
    }
}
