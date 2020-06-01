<?php


namespace app\api\service;
use app\api\controller\Base;
use app\util\TaolijinCode;
use app\util\TaolijinDesc;
use app\model\taolijin\Goods as GoodsModel;
use app\model\taolijin\Tbauth as TbauthModel;
use taobaosdk\top\request\TbkDgVegasTljCreateRequest;
use taobaosdk\top\TopClient;
use taobaosdk\top\request\TbkTpwdCreateRequest;


class Taolijin extends Base
{
    /**
     * 创建淘礼金
     */
    public function createTlj($tlj,$goodsid,$totalnum,$name,$winnum,$perface,$useendtime,$useendtimemode,$send_start_date,$send_end_date){
        $c = new TopClient;
        $c->appkey = $tlj["data"]["appkey"];
        $c->secretKey = $tlj["data"]["appsecret"];
        $req = new TbkDgVegasTljCreateRequest;
        //$req->setCampaignType("定向：DX；鹊桥：LINK_EVENT；营销：MKT"); // CPS佣金计划类型
        $req->setAdzoneId($tlj["data"]["adzoneid"]);//妈妈广告位Id
        $req->setItemId($goodsid);//宝贝id
        $req->setTotalNum($totalnum);//淘礼金总个数
        $req->setName($name);//淘礼金名称，最大10个字符
        $req->setUserTotalWinNumLimit($winnum); // 单用户累计中奖次数上限
        $req->setSecuritySwitch("true"); // 安全开关
        $req->setPerFace($perface); // 单个淘礼金面额，支持两位小数，单位元
        $req->setSendStartTime($send_start_date); // 发放开始时间
        $req->setSendEndTime($send_end_date); // 发放截止时间
        $req->setUseEndTime($useendtime); // 使用结束日期。如果是结束时间模式为相对时间，时间格式为1-7直接的整数, 例如，1（相对领取时间1天）； 如果是绝对时间，格式为yyyy-MM-dd，例如，2019-01-29，表示到2019-01-29 23:59:59结束
        $req->setUseEndTimeMode($useendtimemode); // 结束日期的模式,1:相对时间，2:绝对时间
        //$req->setUseStartTime(""); // 使用开始日期。相对时间，无需填写，以用户领取时间作为使用开始时间。绝对时间，格式 yyyy-MM-dd，例如，2019-01-29，表示从2019-01-29 00:00:00 开始

        $resp = $c->execute($req);
        $res = json_decode(json_encode($resp), true);

        return $res;
    }
    /**
     * 获取淘礼金相关配置
     */
    public function getTbTlj($main_id, $sub_id, $data=[]){
        $tbauth = new TbauthModel();
        $tljObj = $tbauth::with(["belongsToUser"])->where("main_id", $main_id)->where("sub_id", $sub_id)->order("create_dt asc")->find();
        if (!$tljObj) return $this->buildFailed(TaolijinCode::API_QUERY_ERROR, "淘礼金配置列表获取失败");
        $tlj = $tljObj->toArray();
        $arr_pid = explode("_", $tlj["pid"]);
        if (!isset($arr_pid[3])){
            return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "pid格式错误");
        }
        $data = [
            "appkey"=>$tlj["appkey"]
            ,"appsecret"=>$tlj["appsecret"]
            ,"pid"=>$tlj["pid"]
            ,"adzoneid"=>$arr_pid[3]
            ,"uid"=>$tlj["belongs_to_user"]["id"]
        ];
        return $this->buildSuccess($data);
    }
    /**
     * 获取用户账户信息
     */
    public function getUserInfo(){

    }

    /**
     * 生成淘口令
     * @param $url
     * @return array
     */
    public function createPwd($url){
        if (!$url){
            return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "url不能为空");
        }
        $taobao = config("taobao.");
        $c = new TopClient;
        $c->appkey = $taobao["appkey"];
        $c->secretKey = $taobao["appsecret"];
        $req = new TbkTpwdCreateRequest;
        $req->setUserId("");
        $req->setText("淘礼金红包");
        $req->setUrl($url);
        $req->setLogo("");
        $req->setExt("{}");
        $resp = $c->execute($req);
        $res = json_decode(json_encode($resp), true);
        if (!isset($res["data"]["model"])){
            return $this->buildFailed(TaolijinCode::API_TAOBAO_ERROR, "生成淘口令失败", $res);
        }
        return $this->buildSuccess($res["data"]["model"]);
    }
    /**
     * 查询商品
     */
    public function detailGoods($goodsid){
        if (!$goodsid) return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "缺少goodsid");
        $goods = new GoodsModel();
        $goodsObj = $goods->where("goodsid", $goodsid)->find();
        if (!$goodsObj) return $this->buildFailed(TaolijinCode::API_QUERY_ERROR);
        $data = $goodsObj->toArray();
        return $this->buildSuccess($data);
    }
    /**
     * 将商品状态改为隐藏
     */
    public function setGoodsStatus($goodsid){
        if (!$goodsid) return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "缺少goodsid");

        $goods = new GoodsModel();
        $goodsObj = $goods->where("goodsid", $goodsid)->find();
        if ($goodsObj){
            $goodsObj->status = -1;
            $goodsObj->save();
        }
    }
}