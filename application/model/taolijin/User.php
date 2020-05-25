<?php


namespace app\model\taolijin;


class User extends Base
{
    protected $table = 'ncnk_user';

    public function deduct(){
        return $this->hasOne("Deduct", "uid", "id");
    }
}