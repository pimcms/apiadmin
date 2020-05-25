<?php


namespace app\api\service;


use app\util\TaolijinCode;

class Taolijin
{
    /**
     * 获取淘礼金appkey
     */
    public function getTbTlj(){
        $data = config("taobao.");
        if (!$data["appkey"] || !$data["appsecret"] || !$data["pid"]){
            return $this->buildFailed(TaolijinCode::API_PARAMS_ERROR, "获取淘礼金相关配置失败");
        }
        $arr_pid = explode("_", $data["pid"]);
        if (!isset($arr_pid[3])){
            return $this->buildFailed(ReturnCode::INVALID, "pid格式错误");
        }
        $data["adzoneid"] = $arr_pid[3];
        return $this->buildSuccess($data);
    }

    /**
     * 生成淘口令
     * @param $url
     * @return array
     */
    public function createPwd($url){
        if (!isset($url)){
            return $this->buildFailed(ReturnCode::INVALID, "url不能为空");
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
            return $this->buildFailed(ReturnCode::INVALID, "生成淘口令失败", $res);
        }
        return $this->buildSuccess($res["data"]["model"]);
    }
}