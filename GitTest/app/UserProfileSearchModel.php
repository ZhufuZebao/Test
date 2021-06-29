<?php
/**
 * UserProfile を検索する
 */
namespace App;

class UserProfileSearchModel extends UserProfile
{
    public $fillable = ['keyword','name'];

    public $keyword;
    public $name;

    /**
     * @var string 'ASC' or 'DESC'
     */
    public $order = 'ASC';

    /**
     *  Eloquent\Builder
     */
    public $query;

    /**
     * SQL `order by`
     * title  : order by title ASC
     * -title : order by title DESC
     */
    public $sort;

    /**
     * 自身の初期化
     * @ret void
     */
    public function init($params)
    {
        $this->query   = self::query();

        $this->keyword = $params['keyword'] ?? null;
        $this->name    = $params['name']    ?? null;
        $this->sort    = $params['sort']    ?? null;

        if(preg_match('/^\-/', $this->sort)) // '-title' or '-name'
        {
            $this->sort  = substr($this->sort, 1);
            $this->order = 'DESC';
        }
    }

    /**
     * (keyword OR 検索) AND (name OR 検索) order by (title|content|users.name) (ASC|DESC)
     * @ret Eloquent\Builder
     */
    public function search()
    {
        if($kword = $this->keyword)
        {
            $this->query
                 ->where(function($q) use($kword){
                     $q->where('title',  'LIKE', "%{$kword}%");
                     $q->orWhere('content','LIKE', "%{$kword}%");
                 });
        }

        if($name = $this->name)
        {
            $this->query
                 ->whereHas('user', function($q) use($name){
                     $q->where('name',       'LIKE', "%{$name}%");
                     $q->orWhere('first_name', 'LIKE', "%{$name}%");
                     $q->orWhere('last_name',  'LIKE', "%{$name}%");
                 });
        }

        if(in_array($this->sort, ['title','content']))
        {
            $this->query->orderBy($this->sort, $this->order);
        }
        elseif('name' == $this->sort)
        {
            $this->query
                 ->join('users', 'users.id', '=', 'user_profiles.id')
                 ->orderBy('users.name', $this->order);
        }

        return $this->query;
    }

}
