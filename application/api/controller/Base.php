<?php
/**
 * 工程基类
 * @since   2017/02/28 创建
 * @author  zhaoxiang <zhaoxiang051405@gmail.com>
 */
namespace app\api\controller;

use app\util\ReturnCode;
use think\App;
use think\Controller;

class Base extends Controller {

    private $debug = [];
    protected $userInfo = [];

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=utf-8");
    }

    public function _initialize() {
//        $this->userInfo = "";
    }

    public function buildSuccess($data = [], $msg = '操作成功', $code = ReturnCode::SUCCESS) {
        $return = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];
        if (config('app.app_debug') && $this->debug) {
            $return['debug'] = $this->debug;
        }

        return $return;
    }

    public function buildFailed($code, $msg = '操作失败', $data = []) {
        $return = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];
        if (config('app.app_debug') && $this->debug) {
            $return['debug'] = $this->debug;
        }

        return $return;
    }

    protected function debug($data) {
        if ($data) {
            $this->debug[] = $data;
        }
    }
}
