<?php


namespace app\api\controller\taolijin;
use app\api\controller\Base;
use app\util\ReturnCode;
use app\util\TaolijinCode;
use app\api\service\Taolijin as TljService;
use app\api\service\Goods as GoodsService;
use app\model\taolijin\Tbgift as TbgiftModel;

class Taolijin extends Base
{
    /**
     * 创建淘礼金
     */
    public function createTaolijin(){
        $main_id = $this->request->post("main_id");//主账户id
        $sub_id = $this->request->post("sub_id");//机器人子账户
        $wx_id = $this->request->post("wx_id");//wxid
        $uid = $this->request->post("uid");//uid
        $goodsid = $this->request->post("goodsid");//商品id
        $perface = $this->request->post("perface"); //单个淘礼金面额
        if ($perface < 1) return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "淘礼金面额小于1.00元");
        $tljservice = new TljService();
        $tlj = $tljservice->getTbTlj($main_id, $sub_id);//获取淘礼金相关配置
        if($tlj["code"] != 1) return $this->buildFailed($tlj["code"], $tlj["msg"]);
        $detailGoods = $tljservice->detailGoods($goodsid);//检测商品是否可创建
        if (!$detailGoods["data"]) return $this->buildFailed(TaolijinCode::API_QUERY_ERROR, "获取商品详情失败");
        if ($detailGoods["data"]["status"] == -1) return $this->buildFailed(TaolijinCode::API_QUERY_ERROR, "该商品淘礼金已创建完");

        $totalnum = "1";
        $name = "淘礼金红包";
        $winnum = "1";
        $useendtimemode = "1";//结束日期的模式
        $useendtime = "2";//领取后，几天内可以使用
        $send_start_date = date("Y-m-d H:i:s");//发放开始时间
        $send_end_time = strtotime("+1 day");
        $send_end_date = date("Y-m-d H:i:s", $send_end_time);//发放截止时间
        $create_result = $tljservice->createTlj($tlj,$goodsid,$totalnum,$name,$winnum,$perface,$useendtime,$useendtimemode,$send_start_date,$send_end_date);
//        print_r($create_result);die;
        //判断淘礼金是否创建成功
        if ($create_result["result"]["success"] == "false"){
            //创建失败：商品问题，改变商品的状态
            if ($create_result["result"]["msg_code"] == "FAIL_CHECK_RULE_ERROR" || $create_result["result"]["msg_code"] == "FAIL_BIZ_ITEM_FORBIDDEN"){
                $goodsservice = new GoodsService();
                $goodsservice->setGoodsStatus($goodsid);
            }
            return $this->buildFailed(TaolijinCode::API_TAOBAO_ERROR, $create_result["result"]["msg_info"], $create_result["result"]);
        }
        $send_url = $create_result["result"]["model"]["send_url"]; // 领取淘礼金url
        $res_pwd = $tljservice->createPwd($send_url);//生成淘口令
        if ($res_pwd["code"] != 1){
            return $this->buildFailed(TaolijinCode::API_TAOBAO_ERROR, $res_pwd["msg"], $res_pwd["data"]);
        }
        $pwd = $res_pwd["data"]; // 淘口令
        $rights_id = $create_result["result"]["model"]["rights_id"]; // rights_id,必须存储
        //存储淘礼金相关信息
        $tljAuth = ["appkey"=>$tlj["data"]["appkey"],"appsecret"=>$tlj["data"]["appsecret"],"pid"=>$tlj["data"]["pid"],"adzoneid"=>$tlj["data"]["adzoneid"]];
        $remark = json_encode($tljAuth);
        $data = ["uid"=>$tlj["data"]["uid"],"memberid"=>$uid,"main_id"=>$main_id,"sub_id"=>$sub_id,"wxid"=>$wx_id,"nikename"=>"","rights_id"=>$rights_id,"remark"=>$remark,"send_url"=>$send_url,"pwd"=>$pwd,"goodsid"=>$goodsid,"goodstitle"=>$detailGoods["data"]["title"],"goods_img"=>$detailGoods["data"]["imgs"],"name"=>$name,"totalnum"=>$totalnum,"winnum"=>$winnum,"perface"=>$perface,"createdt"=>date("Y-m-d H:i:s"),"sendstartdt"=>$send_start_date,"sendenddt"=>$send_end_date,"useendtimemode"=>$useendtimemode,"useday"=>$useendtime];
        $tbgift = new TbgiftModel();
        $save = $tbgift->save($data);
        if (!$save) return $this->buildFailed(TaolijinCode::API_SAVE_ERROR, "淘礼金相关信息保存失败");
        return $this->buildSuccess(["name"=>$name,"tbpwd"=>$pwd,"perface"=>$perface,"send_start_date"=>$send_start_date,"send_end_date"=>$send_end_date,"useendtime"=>$useendtime]);
    }

    /**
     * 淘礼金使用记录
     */
    public function taolijinRecord(){
        $main_id = $this->request->post("main_id");//主账户id
        $sub_id = $this->request->post("sub_id");//机器人子账户
        $wx_id = $this->request->post("wx_id");//wxid
        $uid = $this->request->post("uid");//uid
        $page = $this->request->post("page", 1);//page
        $pageSize = $this->request->post("pageSize", 10);//limit
        $where = ["main_id"=>$main_id, "sub_id"=>$sub_id, "wxid"=>$wx_id, "memberid"=>$uid];
        $tliModel = new TbgiftModel();
        $count = $tliModel->where($where)->count();
        $listObj = $tliModel->where($where)->order("createdt desc")->page($page, $pageSize)->fetchSql(true)->select();
        print_r($listObj);die;
        if (false == $listObj) return $this->buildFailed(TaolijinCode::API_QUERY_ERROR, "查询失败");
        $list = $listObj->toArray();

        return $this->buildSuccess(["count"=>$count,"list"=>$list]);
    }
}