<?php


namespace App\Http\Controllers\Api;

use App\Models\News\News;
use App\Models\News\NewsCategory;
use App\Models\User\User;
use Illuminate\Support\Facades\App;

class NewsController extends Controller
{
    /**新闻分类
     *
     */
    public function categories()
    {
        $categories = NewsCategory::get();
        return $this->success(__('api.请求成功'), $categories);
    }

    /**新闻接口
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {

        $category_id = request('category_id', 0);
        $limit = request('limit', 15);
        $list = News::with(['category', 'lang'])
            ->whereHas('lang', function ($query) {
                $lang = App::getLocale();
                $query->where('code', $lang);
            })->where('category_id', $category_id)
            ->orderByDesc('id')->paginate($limit);

        return $this->success(__('api.请求成功'), $list);
    }

    /**新闻详情
     *
     */
    public function info()
    {
        $id = request('id', 0);
        $news = News::find($id);
        return $this->success(__('api.请求成功'), $news);
    }

}
