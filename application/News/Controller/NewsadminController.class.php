<?php
namespace News\Controller;

use Common\Controller\AdminbaseController;

class NewsadminController extends AdminbaseController
{
    //当前使用语言 常量  LANG_SET 
    protected $article_model;
    protected $category_model;
    function _initialize()
    {
        parent::_initialize();
        $this->article_model = M("Article");
        $this->category_model = M("Category");
    }
    // 后台新闻列表
    public function newsindex()
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

        $count = $this->article_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->article_model
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

    // 后台新闻添加
    public function newsadd()
    {
        if (IS_POST) {
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            $id = $_POST['post']['cid'];
            $article['cat_name'] = $this->category_model->where("id = '$id'")->getField('name');
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->article_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $info = $this->category_model->where('pid = 0 AND channelid =1')->getField('id,name');
        $this->assign('info',$info);
        $this->display();
    }

    // 后台新闻编辑
    public function newsedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['pics'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['country'] = implode(",",array_filter(array($_POST['post']['country1'],$_POST['post']['country2'],$_POST['post']['country3'])));
//            $img = new \Think\Image(); //实例化
//            $img->open($_POST['post']['tpic']); //打开被处理的图片
//            $img->thumb(100,100); //制作缩略图(100*100)
//            $img->save($smallimg_path); //保存缩略图到服务器
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            if(!isset($article[tuijian])){
                $article[tuijian] = 0;
            }elseif(!isset($article[zhiding])){
                $article[zhiding] = 0;
            }elseif(!isset($article[working])){
                $article[working] = 0;
            }
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->article_model->save($article);
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
        $info = $this->article_model->where($where)->find();
        $data = $this-> category_model ->where('pid = 0 AND channelid =1')->getField('id,name');

        $this->assign('post', $info);
        $this->assign('data', $data);
        $this->display();
    }

    // 后台新闻删除
    public function newsdelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->article_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->article_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }
}
