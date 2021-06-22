<?php


namespace App\Http\Controllers\Admin;

use App\Models\Chain\ChainProtocol;

/**链上协议控制器
 * Class ChainProtocolController
 *
 * @package App\Http\Controllers\Admin
 */
class ChainProtocolController extends Controller
{

    public function index()
    {
        return view('admin.chainProtocol.index');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $chainProtocol = ChainProtocol::paginate($limit);
        return $this->layuiPageData($chainProtocol);
    }

    public function edit()
    {
        $id = request('id', 0);
        $chainProtocol = ChainProtocol::findOrFail($id);
        return view("admin.chainProtocol.edit.{$chainProtocol->code}", [
            'chainProtocol' => $chainProtocol
        ]);
    }

    public function update()
    {
        $id = request('id', 0);
        $data = request()->all();
        ChainProtocol::findOrFail($id)->update($data);
        return $this->success('更改成功');
    }
}
