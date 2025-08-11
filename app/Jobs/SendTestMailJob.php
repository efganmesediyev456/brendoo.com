<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendTestMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;

    public function __construct($to)
    {
        $this->to = $to;
    }

    public function handle()
    {
        Mail::raw('Salam qaqa, bu sadə bir test mailidir.', function ($message) {
            $message->to($this->to)
                    ->subject('Sadə Test Maili');
        });
    }
}
