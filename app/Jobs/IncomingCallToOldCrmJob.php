<?php

namespace App\Jobs;

use App\Models\IncomingCallRequest;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class IncomingCallToOldCrmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\IncomingCallRequest $row
     * @return void
     */
    public function __construct(
        public IncomingCallRequest $row
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = env('CRM_OLD_API_SERVER', 'http://localhost:8000');
        $url .= "/api/eventHandling/callFromIncominget";

        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->withOptions(['verify' => false])
                ->post($url, [
                    'call' => $this->row->incoming_event_id
                ]);

            $old['response_code'] = $response->getStatusCode();
            $old['response'] = $response->json();
        } catch (Exception $e) {
            $old['error'] = $e->getMessage();
        }

        $response_data = $this->row->response_data;

        if (!is_object($response_data))
            $response_data = (object) $response_data;

        $response_data->crm_old = $old ?? [];

        $this->row->response_data = $response_data;
        $this->row->save();
    }
}
