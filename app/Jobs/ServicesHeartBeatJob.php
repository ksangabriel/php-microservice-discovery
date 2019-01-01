<?php

namespace App\Jobs;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use \DB;
use \GuzzleHttp;

class ServicesHeartBeatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $services = Service::all();
        $client = new GuzzleHttp\Client();

        foreach ($services as $service) {
            $fn_update_status = function () use ($client, $service) {
                try
                {
                    $res = $client->get($service->health_indicator_url, []);
                    $status_code = $res->getStatusCode();

                    if ($service->expect_http_status_code != $status_code) {
                        new Exception('Expecting status code ' . $service->expect_http_status_code . ' but got ' . $status_code);
                    }
                    $service->status = 'up';
                } catch (\Exception $e) {
                    echo $e->getMessage();
                    $service->status = 'down';
                } finally {
                    $service->save();
                }
            };
            DB::transaction($fn_update_status);
        }
    }
}
