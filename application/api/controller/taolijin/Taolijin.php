<?php


namespace app\api\controller\taolijin;


use app\api\controller\Base;
use app\util\TaolijinCode;

class Taolijin extends Base
{
    /**
     * 创建淘礼金
     */
    public function create_taolijin(){
        $tlj = $this->getTbTlj(); // 获取淘礼金相关配置
        if($tlj["code"] == "-1") {
            return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, $tlj["msg"]);
        }
        $itemid = $this->request->param("itemid"); // 商品id
        if (!$itemid){
            return $this->buildFailed(ReturnCode::INVALID, "商品id不能为空");
        }
        $perface = $this->request->param("perface"); // 单个淘礼金面额
        if (!$perface){
            return $this->buildFailed(ReturnCode::INVALID, "淘礼金面额");
        }
        $totalnum = "1";
        $name = "淘礼金红包";
        $winnum = "1";
        $useendtimemode = "1";
        $useendtime = "2";
        require_once EXTEND_PATH . "taobaosdk/TopSdk.php";
        $c = new \TopClient;
        $c->appkey = $tlj["data"]["appkey"];
        $c->secretKey = $tlj["data"]["appsecret"];
        $req = new \TbkDgVegasTljCreateRequest;
        //$req->setCampaignType("定向：DX；鹊桥：LINK_EVENT；营销：MKT"); // CPS佣金计划类型
        $req->setAdzoneId($tlj["data"]["adzoneid"]); // 妈妈广告位Id
        $req->setItemId($itemid); // 宝贝id
        $req->setTotalNum($totalnum); // 淘礼金总个数
        $req->setName($name); // 淘礼金名称，最大10个字符
        $req->setUserTotalWinNumLimit($winnum); // 单用户累计中奖次数上限
        $req->setSecuritySwitch("true"); // 安全开关
        $req->setPerFace($perface); // 单个淘礼金面额，支持两位小数，单位元
        $send_start_date = date("Y-m-d H:i:s");
        $req->setSendStartTime($send_start_date); // 发放开始时间
        $send_end_time = strtotime("+30 day");
        $send_end_date = date("Y-m-d H:i:s", $send_end_time); // 发放截止时间为1个月
        $req->setSendEndTime($send_end_date); // 发放截止时间
        $req->setUseEndTime($useendtime); // 使用结束日期。如果是结束时间模式为相对时间，时间格式为1-7直接的整数, 例如，1（相对领取时间1天）； 如果是绝对时间，格式为yyyy-MM-dd，例如，2019-01-29，表示到2019-01-29 23:59:59结束
        $req->setUseEndTimeMode($useendtimemode); // 结束日期的模式,1:相对时间，2:绝对时间
        //$req->setUseStartTime(""); // 使用开始日期。相对时间，无需填写，以用户领取时间作为使用开始时间。绝对时间，格式 yyyy-MM-dd，例如，2019-01-29，表示从2019-01-29 00:00:00 开始
        $resp = $c->execute($req);
        $res = json_decode(json_encode($resp), true);
        if ($res["result"]["success"] == "false"){
            return $this->buildFailed(ReturnCode::INVALID, "创建淘礼金失败", $res["result"]);
        }
        $send_url = $res["result"]["model"]["send_url"]; // 领取淘礼金url
        $res_pwd = $this->createPwd($send_url); // 生成淘口令
        if ($res_pwd["code"] == "-1"){
            return $this->buildFailed(ReturnCode::INVALID, $res_pwd["msg"], $res_pwd["data"]);
        }
        $pwd = $res_pwd["data"]; // 淘口令
        $rights_id = $res["result"]["model"]["rights_id"]; // rights_id,必须存储

        $data = ["main_id"=>"","sub_id"=>"","wxid"=>"","nikename"=>"","rights_id"=>$rights_id,"send_url"=>$send_url,"pwd"=>$pwd,"itemid"=>$itemid,"name"=>$name,"totalnum"=>$totalnum,"winnum"=>$winnum,"perface"=>$perface,"createdt"=>date("Y-m-d H:i:s"),"sendstartdt"=>$send_start_date,"sendenddt"=>$send_end_date,"useendtimemode"=>$useendtimemode,"useday"=>$useendtime];
        $model = new NcnkTbgift();
        $save = $model->save($data);
        if (!$save){
            return $this->buildFailed(ReturnCode::INVALID, "保存失败");
        }
    }
}