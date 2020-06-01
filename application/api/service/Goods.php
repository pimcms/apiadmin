<?php


namespace app\api\service;
use app\model\taolijin\Goods as GoodsModel;

class Goods
{
    public function setGoodsStatus($goodsid){
        if (!$goodsid) return false;
        $goods = new GoodsModel();
        $goodsObj = $goods->where("goodsid", $goodsid)->find();
        if (!$goodsObj) return false;
        $goodsObj->status = -1;
        $goodsObj->save();
    }
}