<?php

namespace App\Console\Commands\Common;

use App\Models\Admin\AdminModuleType;
use App\Models\Currency\CurrencyMatch;
use App\Models\Lever\LeverTransaction;
use App\Models\Route\AdminRoute;
use App\Utils\Workerman\WsConnection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;


class RouteSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'common:route_set';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '保存路由';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $routes = Route::getRoutes();
        $list= AdminRoute::orderBy('id','desc')->pluck('url')->toArray();

        $route_data = [];
        foreach ($routes as $route) {
            if (substr($route->uri, 0, 5) != 'admin') {
                continue;
            }
            if (!$route->getName()) {
                continue;
            }
            if (!in_array($route->uri,$list)){
                $admin_route=new AdminRoute();
                $admin_route->name=$route->getName();
                $admin_route->url=$route->uri;
                $admin_route->is_add_log=2;
                $admin_route->save();
            }
        }

    }


}
