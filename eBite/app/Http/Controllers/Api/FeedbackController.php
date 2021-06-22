<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback\Feedback;
use App\Models\Feedback\FeedbackType;
use App\Models\User\User;


class FeedbackController extends Controller
{
    /**
     * 用户反馈
     */
    public function feedback()
    {
        $data['user_id'] = User::getUserId();//用户id
        $data['type_id'] = request('type_id', 0);//类别id
        $data['title'] = $title = request('title', '');//标题
        $data['content'] = $content = request('content', '');//反馈内容
        if (empty($title)) {
            return $this->error(__('api.标题不能为空'));
        }
        if (mb_strlen($content) < 10) {
            return $this->error(__('api.反馈内容过少'));
        }
        Feedback::create($data);
        return $this->success(__('api.请求成功'));
    }

    /**
     * 反馈类型列表
     */
    public function type()
    {
        $type_list = FeedbackType::orderBy('sorts', 'ASC')->get();
        return $this->success(__('api.请求成功'), $type_list);
    }

    /**
     * 反馈列表
     */
    public function list()
    {
        $limit = request('limit', 15);
        $list = Feedback::where('user_id', User::getUserId())->where('is_display', 1)->paginate($limit);
        return $this->success(__('api.请求成功'), $list);
    }
}
