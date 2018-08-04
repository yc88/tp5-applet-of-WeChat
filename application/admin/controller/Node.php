<?php
/**后端节点管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-14
 * Time: 11:09
 */
namespace app\admin\controller;

use app\admin\model\NodeModel;

class Node extends  Base
{
    // 节点列表
    public function index()
    {
        if(request()->isAjax()){

            $node = new NodeModel();
            $nodes = $node->getNodeList();

            $nodes = getTree(objToArray($nodes), false);
            putMsg(1,$nodes);
        }
        return $this->fetch();
    }

    // 添加节点
    public function nodeAdd()
    {
        $param = input('post.');
        $node = new NodeModel();
        $flag = $node->insertNode($param);
        $this->cache_role_del();
        if(!$flag){
            putMsg(0,'添加失败');
        }
        putMsg(1,'添加成功');
    }

    // 编辑节点
    public function nodeEdit()
    {
        $param = input('post.');

        $node = new NodeModel();
        $flag = $node->editNode($param);
        $this->cache_role_del();
        if(0 == $flag){
            putMsg(0,'编辑失败');
        }
        putMsg(1,'编辑成功');
    }

    // 删除节点
    public function nodeDel()
    {
        $id = input('param.id');

        $role = new NodeModel();
        $flag = $role->delNode($id);
        $this->cache_role_del();
        if(!$flag){
            putMsg(0,'操作失败');
        }
        putMsg(1,'操作成功');
    }
}