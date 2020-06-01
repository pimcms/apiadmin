<?php


namespace app\model\taolijin;


class Tbauth extends Base
{
    protected $table = "ncnk_taobao_auth";

    public function belongsToUser(){
        return $this->belongsTo("User", "uid", "id");
    }
}