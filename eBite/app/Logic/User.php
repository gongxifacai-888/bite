<?php

namespace App\Logic;

use Illuminate\Support\Facades\Log;
use App\Models\User\User as UserModel;

class User
{
    /**
     * 查询用户的指定代数的上级(根据parents_path信息)
     * @param $user      用户模型实例
     * @param null $qty 要取的上级代数,不传或传null则取全部
     * @return array    返回包含上级id的数组
     */
    public static function getParentsPathDesc($user, $qty = null)
    {
        $path = $user->parents_path;
        if ($path == null || empty($path)) {
            return [];
        }
        $parents = explode(',', $path);
        $parents = array_filter($parents);
        krsort($parents);
        $parents = array_slice($parents, 0, $qty);
        return $parents;
    }

    /**
     * 递归查询上级
     *
     * @param \App\Models\User\User $user 用户模型实例
     * @return array
     */
    public static function getRealParents($user)
    {
        $found_parent_node = [];
        $parents = self::findParent($user, $found_parent_node);
        return $parents;
    }

    /**
     * 递归查询上级(字符串)
     *
     * @param \App\Models\User\User $user 用户模型实例
     * @return string 返回逗号间隔的path
     */
    public static function getRealParentsPath($user)
    {
        $parents = self::getRealParents($user);
        if (count($parents) > 0) {
            return implode(',', $parents);
        }
        return '';
    }

    private static function findParent($user, &$found_parent_node)
    {
        $parent_id = $user->parent_id;

        if ($parent_id) {
            //检测节点关系是否有死循环
            if (in_array($parent_id, $found_parent_node)) {
                $context = [
                    'user_id'           => $user->id,
                    'parent_id'         => $parent_id,
                    'found_parent_node' => $found_parent_node,
                ];
                //记录错误日志
                Log::useDailyFiles(base_path('storage/logs/user/'), 7);
                Log::critical('id:' . $user->id . '的用户,上级关系存在死循环', $context);
                return [];
            }
            array_unshift($found_parent_node, $parent_id);
            $parent = UserModel::find($parent_id);
            $result = self::findParent($parent, $found_parent_node);
            unset($parent);
            array_push($result, $parent_id);
            return $result;
        } else {
            return [];
        }
    }

}
