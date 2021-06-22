<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2019/3/21
 * Time: 14:17
 */

namespace App\Http\Controllers\Admin;


use App\Models\System\Lang;
use App\Models\News\News;
use App\Models\News\NewsCategory;

class NewsController extends Controller
{

    public function index()
    {
        $lang=Lang::all();
        return view('admin.news.index',['langs'=>$lang]);
    }

    public function list()
    {
        $limit = request('limit', 10);
        $lang_id = request('lang_id', -1);


        $news_list = News::where(function ($query) use ($lang_id){
            $lang_id>-1&&$query->where('lang_id',$lang_id);
        })->orderBy('id', 'DESC')->paginate($limit);
        return $this->layuiPageData($news_list);
    }

    /**新闻新增
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        $category_list = NewsCategory::all();
        $langs = Lang::get();
        return view('admin.news.add', [
            'category_list' => $category_list,
            'langs' => $langs,
        ]);
    }

    /**新闻保存
     * @return \Illuminate\Http\JsonResponse
     */
    public function save()
    {
        $category_id = request('category_id', 0);
        $title = request('title', '');
        $author = request('author', '');
        $keywords = request('keywords', '');
        $desc = request('desc', '');
        $content = request('content', '');
        $logo = request('logo', '');
        $banner = request('banner', '');
        $lang = request('lang_id', '');

        if (!$title) {
            return $this->error('请输入标题');
        }
        if (empty($lang)){
            return $this->error('参数错误');
        }
        News::create([
            'category_id' => $category_id,
            'title' => $title,
            'author' => $author,
            'keywords' => $keywords,
            'desc' => $desc,
            'content' => $content,
            'logo' => $logo,
            'banner' => $banner,
            'lang_id' => $lang,
        ]);

        return $this->success('操作成功');
    }

    /**新闻编辑
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit()
    {
        $id = request('id', 0);
        $info = News::find($id);
        $category_list = NewsCategory::all();
        $langs = Lang::get();
        return view('admin.news.edit', [
            'category_list' => $category_list,
            'info' => $info,
            'langs' => $langs,
        ]);
    }

    /**新闻更新
     * @return \Illuminate\Http\JsonResponse
     */
    public function update()
    {
        $id = request('id', 0);
        $category_id = request('category_id', 0);
        $title = request('title', 0);
        $author = request('author', 0);
        $keywords = request('keywords', 0);
        $desc = request('desc', 0);
        $content = request('content', 0);
        $logo = request('logo', 0);
        $banner = request('banner', 0);
        $lang = request('lang_id', '');

        if (!$title) {
            return $this->error('请输入标题');
        }
        if (empty($lang)){
            return $this->error('参数错误');
        }
        News::findOrFail($id)->update([
            'category_id' => $category_id,
            'title' => $title,
            'author' => $author,
            'keywords' => $keywords,
            'desc' => $desc,
            'content' => $content,
            'logo' => $logo,
            'banner' => $banner,
            'lang_id' => $lang,
        ]);

        return $this->success('操作成功');
    }

    /**新闻删除
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete()
    {
        $id = request('id', 0);
        News::destroy($id);
        return $this->success('删除成功');
    }
}
