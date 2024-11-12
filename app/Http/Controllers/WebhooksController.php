<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Webhook;

class WebhooksController extends Controller
{
    public function index(){
        $webhooks = Webhook::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Webhooks List',
            'data' => $webhooks
        ]);
    }

    public function webhook(Request $request){
        $r = $request->all();
        $ip = $request->header('CF-Connecting-IP') ?: $request->ip(); // when using cloudflare need to get real ip, not the nginx proxy ip.

        $webhook = Webhook::create([
            'request'=>json_encode($r),
            'ip'=>$ip,
            'action'=>'new_user'
        ]);

        $forwards = [];
        // marketing agency 1
        $forwards['marketing_agency_1'] = $this->forward(json_encode($r),'https://url....');

        // marketing agency 1
        $forwards['marketing_agency_2'] = $this->forward(json_encode($r),'https://url....');
        $webhook->forward = json_encode($forwards);
        $webhook->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook Forwarded!',
        ]);
    }

    private function forward($json,$url){
        try {
            $client = new Client([
                'verify' => true,
            ]);
            $headers = ['Content-Type' => 'application/json'];
            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $json
            ]);
            $body = $response->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'Unknown';
            $errorMessage = $e->hasResponse() ? $e->getResponse()->getReasonPhrase() : $e->getMessage();
            $body = "Error $statusCode: $errorMessage";
        } catch (\Throwable $th) {
            // Fallback
            $body = 'Error: ' . $th->getMessage();
        }

        return $body;
    }
}
