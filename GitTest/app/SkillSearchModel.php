<?php
/**
 * Skillを検索する
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

class SkillSearchModel extends Skill
{
    public $fillable = ['id', 'name'];

    public function init($params)
    {
        $this->id   = $params['id']   ?? null;
        $this->name = $params['name'] ?? null;
    }

    public function search()
    {
        $q = self::query();

        if($this->id)
        {
            $q->where('id', $this->id);
        }

        if($this->name)
        {
            $words  = explode(' ', $this->name);
            $params = [];

            foreach($words as $word)
            {
                /*
                 * skill.name にあいまい一致すればOK
                 */
                $q->orWhere('name', 'LIKE', "%{$word}%");
            }
        }

        return $q;
    }

}
