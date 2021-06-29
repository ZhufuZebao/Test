<?php
/**
 * Created by PhpStorm.
 * User: P0123779
 * Date: 2019/04/12
 * Time: 16:23
 */

namespace App;


class InvitationMsg extends Model
{
    private $id;
    private $sendId;
    private $receiveId;
    private $sendTime;
    private $receiveTime;
    private $status;
    private $mail;
    private $remark;

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
    public function getSendId()
    {
        return $this->sendId;
    }

    /**
     * @param mixed $sendId
     */
    public function setSendId($sendId)
    {
        $this->sendId = $sendId;
    }

    /**
     * @return mixed
     */
    public function getReceiveId()
    {
        return $this->receiveId;
    }

    /**
     * @param mixed $receiveId
     */
    public function setReceiveId($receiveId)
    {
        $this->receiveId = $receiveId;
    }

    /**
     * @return mixed
     */
    public function getSendTime()
    {
        return $this->sendTime;
    }

    /**
     * @param mixed $sendTime
     */
    public function setSendTime($sendTime)
    {
        $this->sendTime = $sendTime;
    }

    /**
     * @return mixed
     */
    public function getReceiveTime()
    {
        return $this->receiveTime;
    }

    /**
     * @param mixed $receiveTime
     */
    public function setReceiveTime($receiveTime)
    {
        $this->receiveTime = $receiveTime;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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

    /**
     * @return mixed
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param mixed $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }


}