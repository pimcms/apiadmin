<?php
namespace app\api\controller\taolijin;

use app\api\controller\Base;
use app\model\taolijin\User as UserModel;
use app\model\taolijin\Goods as GoodsModel;
use app\util\TaolijinCode;
use app\util\TaolijinDesc;

class Goods extends Base
{
    /**
     * 商品列表
     */
    public function listGoods(){
        $main_id = $this->request->get("main_id");
        if (!$main_id) return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "main_id不能为空");
        $user_model = new UserModel();
        $userObj = $user_model::with(["deduct"])->where("main_id", $main_id)->find();
        if (!$userObj) return $this->buildFailed(TaolijinCode::API_ERROR, TaolijinDesc::GET_LIST_GOODS_FAILED);
        $user = $userObj->toArray();
        $page = $this->request->get("page", 1);
        $limit = $this->request->get("limit", 10);
        $where = [
            "status"=>1
        ];
        $goods = new GoodsModel();
        $listObj = $goods->where($where)->order("create_time desc")->page($page, $limit)->select();
        if (false === $listObj) return $this->buildFailed(TaolijinCode::API_ERROR, TaolijinDesc::GET_LIST_GOODS_FAILED);
        $list = $listObj->toArray();
        $count = $goods->where($where)->count();
        return $this->buildSuccess(["count"=>$count, "deduct"=>$user["deduct"]["proportion"], "data"=>$list]);
    }
    /**
     * 商品详情
     */
    public function detailGoods(){
        $main_id = $this->request->get("main_id");
        if (!$main_id) return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "main_id不能为空");
        $goodsid = $this->request->get("goodsid");
        if (!$goodsid) return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "goodsid不能为空");
        $user_model = new UserModel();
        $userObj = $user_model::with(["deduct"])->where("main_id", $main_id)->find();
        if (!$userObj) return $this->buildFailed(TaolijinCode::API_ERROR, TaolijinDesc::GET_Detail_GOODS_FAILED);
        $user = $userObj->toArray();
        $where = [
            "status"=>1,
            "goodsid"=>$goodsid
        ];
        $goods = new GoodsModel();
        $goodsObj = $goods->where($where)->find();
        if (!$goodsObj) return $this->buildFailed(TaolijinCode::API_ERROR, TaolijinDesc::TAOLIJIN_END_ERROR);
        $detail_goods = $goodsObj->toArray();

        return $this->buildSuccess(["deduct"=>$user["deduct"]["proportion"], "data"=>$detail_goods]);
    }
}