<?php
namespace Image\Controller;

use Common\Controller\AdminbaseController;

class ImageadminController extends AdminbaseController
{
    //当前使用语言 常量  LANG_SET
    protected $pmproduct_model;
    function _initialize()
    {
        parent::_initialize();
        $this->pmproduct_model = M("Pmproduct");
    }

    // 后台拍卖管理列表
    public function imageindex(){
        //获取目录路径
        $path = './data/upload/default/tupian/';
        //取出该目录下所有的文件及目录
        $result = scandir($path);
        //删除目录
        array_splice($result,0,2);
        //分页
        $count =count($result);
        $page = $this->page($count, 2);
        //获取每页显示的数据
        $list=array_slice($result,$page->firstRow,$page->listRows);
        $this->assign('result',$list);
        //赋值分页输出
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }
    // 后台拍卖信鸽编辑
    public function imageedit()
    {

        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            if(!isset($article[tuijian])){
                $article[tuijian] = 0;
            }elseif(!isset($article[zhiding])){
                $article[zhiding] = 1;
            }elseif(!isset($article[fobidden])){
                $article[fobidden] = 0;
            }
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> pmproduct_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this-> pmproduct_model->where($where)->find();
        $data = $this-> pmzt_model ->getField('id,tname');

        $this->assign('post', $info);
        $this->assign('data', $data);
        $this->display();
    }
    // 后台拍卖信鸽删除
    public function imagedelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->pmproduct_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->pmproduct_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

}
