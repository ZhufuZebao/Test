<?php
/**
 * Qualification を検索する
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

class QualificationSearchModel extends Qualification
{
    protected $table = 'qualifications';
    public $fillable = ['id', 'name'];

    public function init($params)
    {
        $this->id       = $params['id']       ?? null;
        $this->skill_id = $params['skill_id'] ?? null;
        $this->name     = $params['name']     ?? null;
    }

    public function search()
    {
        $q = self::query();

        if($this->id)
        {
            $q->where('id', $this->id);
        }

        if($this->skill_id)
        {
            $q->where('skill_id', $this->skill_id);
        }

        if($this->name)
        {
            $words  = explode(' ', $this->name);
            $params = [];

            foreach($words as $word)
            {
                /*
                 * qualifications.name にあいまい一致すればOK
                 */
                $q->orWhere('name', 'LIKE', "%{$word}%");
            }
        }

        return $q;
    }

}
