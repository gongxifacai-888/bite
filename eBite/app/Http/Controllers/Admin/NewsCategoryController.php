<?php


namespace App\Http\Controllers\Admin;

use App\Models\News\NewsCategory;

class NewsCategoryController extends Controller
{
    public function index()
    {
        return view('admin.newsCategory.index');
    }

    public function list()
    {
        $limit     = request('limit', 10);
        $news_list = NewsCategory::orderBy('sorts', 'ASC')->paginate($limit);
        return $this->layuiPageData($news_list);
    }

    public function add()
    {
        return view('admin.newsCategory.add');
    }

    public function save()
    {
        $name  = request('name', 0);
        $sorts = request('sorts', 0);

        if (!$name) {
            return $this->error('请输入名称');
        }
        if (NewsCategory::where('name', $name)->exists()) {
            return $this->error('名称已存在');
        }

        NewsCategory::create([
            'sorts' => $sorts,
            'name'  => $name,
        ]);
        if (!$name) {
            return $this->error('请输入名称');
        }

        return $this->success('操作成功');
    }

    public function edit()
    {
        $id   = request('id', 0);
        $info = NewsCategory::find($id);
        return view('admin.newsCategory.edit', [
            'info' => $info,
        ]);
    }

    public function update()
    {
        return transaction(function () {

            $id    = request('id', 0);
            $name  = request('name', 0);
            $sorts = request('sorts', 0);

            if (!$name) {
                return $this->error('请输入名称');
            }
            if (NewsCategory::where('name', $name)->where('id', '!=', $id)->exists()) {
                return $this->error('名称已存在');
            }


            NewsCategory::findOrFail($id)->update([
                'sorts' => $sorts,
                'name'  => $name,
            ]);

            return $this->success('操作成功');
        });
    }

    public function delete()
    {
        $id = request('id', 0);
        NewsCategory::destroy($id);
        return $this->success('删除成功');
    }
}
