<?php


namespace App\Http\Controllers\Admin;


use App\Models\System\Area;
use App\Models\User\User;
use App\Models\User\UserReal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRealController extends Controller
{
    public function index()
    {
        return view('admin.userReal.index');
    }

    public function list(Request $request)
    {
        $limit = $request->get('limit', 10);
        $uid = $request->get('uid', '');
        $email = $request->get('email', '');
        $mobile = $request->get('mobile', '');

        $list = UserReal::with(['user'])->when($uid, function ($query, $uid) {
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
        })->orderBy('review_status')->orderByDesc('id')->paginate($limit);

        return $this->layuiPageData($list);
    }

    public function add()
    {

    }

    public function reviewStatus(Request $request)
    {
        $id = $request->get('id', 0);
        $userreal = UserReal::find($id);
        if (empty($userreal)) {
            return $this->error('参数错误');
        }
        if ($userreal->review_status == UserReal::REJECT) {
            $userreal->review_status = UserReal::CONFORM;
        } elseif ($userreal->review_status == UserReal::CONFORM) {
            $userreal->review_status = UserReal::REJECT;
        } else {
            $userreal->review_status = UserReal::REJECT;
        }
        try {
            $userreal->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function detail(Request $request)
    {
        $id = $request->get('id', 0);
        $user_real = UserReal::where('id', $id)->first();
        if (empty($user_real)) {
            return $this->error('参数错误');
        }
        return view('admin.userReal.detail', ['user_real' => $user_real]);
    }

    public function delete(Request $request)
    {
        $id = $request->get('id', 0);
        $user_real = UserReal::where('id', $id)->first();
        if (empty($user_real)) {
            return $this->error('参数错误');
        }
        try {
            $user_real->delete();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            return $this->error('操作失败');
        }
    }
}
