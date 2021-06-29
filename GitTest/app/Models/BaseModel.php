<?php
/**
 * @author  Song Zhiqiang
 */

namespace App\Models;

class BaseModel extends \Illuminate\Database\Eloquent\Model
{
    /**
     * 更新: 直前にChangeLogへ記録する
     */
    public function update(array $attributes = [], array $options = [])
    {
        event(new \App\Events\EditedByUser($this));
        return parent::update($attributes, $options);
    }

}
