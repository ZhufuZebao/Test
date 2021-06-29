<?php
/**
 * Created by PhpStorm.
 * User: P0123779
 * Date: 2019/04/11
 * Time: 14:21
 */

namespace App;


class Friend extends Model
{
    private $id;
    private $name;
    private $url;
    private $mail;
//    protected $fillable = [
//        'id',
//        'name',
//        'mail',
//        'url',
//    ];
//
//    public static function rules()
//    {
//        return [
//            'id'    => ['required', 'int'],
//            'name' => ['required', 'string'],
//            'mail' => ['required', 'string'],
//            'url' => ['required', 'string'],
//        ];
//    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param mixed $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

}