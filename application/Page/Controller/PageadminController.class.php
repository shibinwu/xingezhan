<?php
namespace Page\Controller;

use Common\Controller\AdminbaseController;

class PageadminController extends AdminbaseController
{
    //当前使用语言 常量  LANG_SET 
    protected $page_model;
    protected $category_model;
    function _initialize()
    {
        parent::_initialize();
        $this->page_model = M("Page");
        $this->category_model = M("Category");
    }

    // 后台单页管理信息列表
    public function pageindex()
    {
        $where = array();
        $request = I('request.');

        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        $where['l'] = LANG_SET;

        $count = $this->page_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->page_model
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $arr = array();
        foreach ($list as $k => $val) {

            $list[$k]['column'] = $this->category_model->where(array('id' => $val['cid']))->getfield('name');
            $arr[] = $this->category_model->where(array('id' => $val['cid']))->getfield('name');
        }
        $arrs = array_unique($arr);

        $this->assign('list', $list);
        $this->assign('arr', $arrs);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }


    // 后台信息添加
    public function pageadd()
    {
        if (IS_POST) {
            $_POST['post']['pagepic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            $id = $_POST['post']['cid'];
            $article['cat_name'] = $this->category_model->where("id = '$id'")->getField('name');
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->page_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $info = $this->category_model->where('pid = 0 AND channelid = 3')->getField('id,name');
        $this->assign('info',$info);
        $this->display();
    }

    // 后台信息编辑
    public function pageedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['pics'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->page_model->save($article);
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
        $info = $this->page_model->where($where)->find();
        $data = $this-> category_model ->where('pid = 0 AND channelid =3')->getField('id,name');

        $this->assign('post', $info);
        $this->assign('data', $data);
        $this->display();
    }

    // 后台信息删除
    public function pagedelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->page_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->page_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }
}
