<?php

namespace App\Http\Controllers\Admin;

use App\Models\Feedback\FeedbackType;
use App\Models\Feedback\Feedback;

class FeedbackController extends Controller
{
    /**
     * 用户反馈
     */
    public function index()
    {
        $types = FeedbackType::all();
        return view('admin.feedback.index', [
            'types' => $types
        ]);

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 反馈列表
     */
    public function list()
    {
        $limit = request('limit', 10);
        $uid = request('uid', 0);//uid
        $type_id = request('type_id', 0);//type_id

        $email = request('email', '');
        $mobile = request('mobile', '');
        $list = Feedback::with(['user'])->when($uid, function ($query, $uid) {
            $query->whereHas('user', function ($query) use ($uid) {
                $query->where('uid', $uid);
            });
        })->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })
        ->when($type_id, function ($query, $type_id) {
            $query->where('type_id', $type_id);
        })->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiPageData($list);
    }

    /**用户反馈编辑
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit()
    {
        $id = request('id');
        return view('admin.feedback.edit', [
            'info' => Feedback::find($id)
        ]);
    }

    /**后台回复更新
     * @return \Illuminate\Http\JsonResponse
     */
    public function update()
    {
        $id = request('id', 0);
        $reply = request('reply', '');

        if (strlen($reply) == 0) {
            $is_replied = 0;//未回复
        } else {
            $is_replied = 1;//已回复
        }

        Feedback::findOrFail($id)->update([
            'reply' => $reply,
            'is_replied' => $is_replied
        ]);
        return $this->success('操作成功');
    }

    /**删除用户反馈
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete()
    {
        $id = request('id', 0);
        Feedback::destroy($id);
        return $this->success('删除成功');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * 回复
     */
    public function reply()
    {
        $id = request('id');
        return view('admin.feedback.reply', [
            'info' => Feedback::find($id)
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 回复
     */
    public function answer()
    {
        $id = request('id', 0);
        $reply = request('reply', '');

        if (strlen($reply) == 0) {
            $is_replied = 0;//未回复
        } else {
            $is_replied = 1;//已回复
        }

        Feedback::findOrFail($id)->update([
            'reply' => $reply,
            'is_replied' => $is_replied
        ]);
        return $this->success('操作成功');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 是否展示
     */
    public function isDisplay()
    {
        $id = request('id', 0);
        $info = Feedback::find($id);
        if (empty($info)) {
            return $this->error('参数错误');
        }
        $change = $info->is_display == 1 ? 0 : 1;
        $info->update([
            'is_display' => $change
        ]);
        return $this->success('操作成功');
    }
}
