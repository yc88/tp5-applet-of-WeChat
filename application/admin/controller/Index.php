<?php
/**
 * 后端管理首页管理
 */
namespace app\admin\controller;

use app\admin\model\NodeModel;

class Index extends  Base
{
    /**后端首页左侧栏目
     * @return mixed
     */
    public function index(){
        $nodeModel = new NodeModel();
        $this->assign([
            'menu' => $nodeModel->getMenu(session('rule'))
        ]);
        return $this->fetch('/index');
    }
        //后端默认首页
    public function default_index(){
        // 生成从 8点 到 22点的时间数组
        $dateLine = array_map(function($vo){
            if($vo < 10){
                return '0' . $vo;
            }else{
                return $vo;
            }
        }, range(8, 22));

        // 初始化数据
        $line = [];
        foreach($dateLine as $key=>$vo){
            $line[$vo] = [
                'is_talking' => intval(rand(20, 120)),
                'in_queue' => intval(rand(0, 20)),
                'success_in' => intval(rand(50, 200)),
                'total_in' => intval(rand(150, 300))
            ];
        }

        $showData = [];
        foreach($line as $key=>$vo){
            $showData['is_talking'][] = $vo['is_talking'];
            $showData['in_queue'][] = $vo['in_queue'];
            $showData['success_in'][] = $vo['success_in'];
            $showData['total_in'][] = $vo['total_in'];
        }

        $this->assign([
            'show_data' => json_encode($showData)
        ]);

        return $this->fetch('index');
    }

}
