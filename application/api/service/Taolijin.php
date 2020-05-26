<?php


namespace app\api\service;
use app\util\TaolijinCode;
use app\util\TaolijinDesc;
use app\model\taolijin\Goods as GoodsModel;
use app\model\taolijin\Tbauth as TbauthModel;

class Taolijin
{
    /**
     * 获取淘礼金相关配置
     */
    public function getTbTlj($main_id, $sub_id){
        if (!$main_id) return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "缺少参数main_id");
        if (!$sub_id) return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "缺少参数sub_id");
        $taolijin = new TbauthModel();
        $tljObj = $taolijin->where("main_id", $main_id)->where("sub_id", $sub_id)->select();
        if (!$tljObj) return $this->buildFailed(TaolijinCode::API_QUERY_ERROR, "淘礼金配置列表获取失败");
        $tlj = $tljObj->toArray();
        print_r($tlj);die;
//        if (!$tlj["appkey"] || !$tlj["appsecret"] || !$tlj["pid"]){
//            return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "获取淘礼金相关配置失败");
//        }
//        $arr_pid = explode("_", $data["pid"]);
//        if (!isset($arr_pid[3])){
//            return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "pid格式错误");
//        }
//        $data["adzoneid"] = $arr_pid[3];
//        return $this->buildSuccess($data);
    }

    /**
     * 生成淘口令
     * @param $url
     * @return array
     */
    public function createPwd($url){
        if (!isset($url)){
            return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "url不能为空");
        }
        $taobao = config("taobao.");
        $c = new \TopClient;
        $c->appkey = $taobao["appkey"];
        $c->secretKey = $taobao["appsecret"];
        $req = new \TbkTpwdCreateRequest;
        $req->setUserId("");
        $req->setText("淘礼金红包");
        $req->setUrl($url);
        $req->setLogo("");
        $req->setExt("{}");
        $resp = $c->execute($req);
        $res = json_decode(json_encode($resp), true);
        if (!isset($res["data"]["model"])){
            return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "生成淘口令失败", $res);
        }
        return $this->buildSuccess($res["data"]["model"]);
    }

    /**
     * 将商品状态改为隐藏
     */
    public function setGoodsStatus($goodsid){
        if (!$goodsid){
            return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "缺少goodsid");
        }
        $goods = new GoodsModel();
        $goodsObj = $goods->where("goodsid", $goodsid)->find();
        if ($goodsObj){
            $goodsObj->status = -1;
            $goodsObj->save();
        }
    }
}