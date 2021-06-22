<?php

namespace App\Http\Controllers\Admin;

use App\Models\Feedback\Feedback;
use App\Models\Feedback\FeedbackType;

class FeedbackTypeController extends Controller
{
    public function index()
    {
        return view('admin.feedbackType.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $list = FeedbackType::orderBy('sorts', 'ASC')->paginate($limit);
        return $this->layuiPageData($list);
    }

    public function add()
    {
        return view('admin.feedbackType.add');
    }

    public function save()
    {
        $name = request('name', 0);
        $sorts = request('sorts', 0);

        FeedbackType::create([
            'sorts' => $sorts,
            'name' => $name,
        ]);
        if (!$name) {
            return $this->error('请输入名称');
        }
        return $this->success('操作成功');
    }

    public function edit()
    {
        $id = request('id', 0);
        $info = FeedbackType::find($id);
        return view('admin.feedbackType.edit', [
            'info' => $info,
        ]);
    }

    public function update()
    {
        return transaction(function () {
            $id = request('id', 0);
            $name = request('name', 0);
            $sorts = request('sorts', 0);

            if (!$name) {
                return $this->error('请输入名称');
            }

            FeedbackType::findOrFail($id)->update([
                'sorts' => $sorts,
                'name' => $name,
            ]);

            return $this->success('操作成功');
        });
    }

    public function delete()
    {
        $id = request('id', 0);
        if (Feedback::where('type_id', $id)->exists()) {
            return $this->error('该类别存在于用户反馈列表中,无法删除');
        }
        FeedbackType::destroy($id);
        return $this->success('删除成功');
    }
}

