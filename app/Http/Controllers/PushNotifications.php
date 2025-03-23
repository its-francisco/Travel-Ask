<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// to send push notificatiosn: PushNotifications::sendPushNotification("hello", "this is travelask");


class PushNotifications extends Controller
{
    public static function sendPushNotification($title, $body) {
        $interests = ['general'];
        $url = 'https://677e4aef-0c71-4a1f-bdab-14d416de61a9.pushnotifications.pusher.com/publish_api/v1/instances/677e4aef-0c71-4a1f-bdab-14d416de61a9/publishes';
        $bearerToken = env('PUSH_NOTIFICATIONS_BEARER');
    
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $bearerToken,
        ])->post($url, [
            'interests' => $interests,
            'web' => [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ],
        ]);
    
        return $response->json();
    }


    //
}
