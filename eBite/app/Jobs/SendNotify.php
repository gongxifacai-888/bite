<?php

namespace App\Jobs;

use App\Notify\SMS\Template\BaseEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNotify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Notify\Notify
     */
    protected $instance;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->instance->send();
    }
}
