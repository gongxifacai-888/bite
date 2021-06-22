<?php

namespace App\Console\Commands\Common;

use App\Models\News\News;
use Illuminate\Console\Command;

class NewsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    protected $searches = [
        'BTC360' => '本公司',
        'www.beauglobal.com' => 'xxx.xxx.com',
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $news_list = News::get();

        foreach ($news_list as $news) {
            foreach ($this->searches as $k => $v) {
                $news->content = str_replace($k, $v, $news->content);
                $news->title = str_replace($k, $v, $news->title);
                $news->author = str_replace($k, $v, $news->author);
                $news->keywords = str_replace($k, $v, $news->keywords);
                $news->desc = str_replace($k, $v, $news->desc);
            }
            $news->save();
        }
    }
}
