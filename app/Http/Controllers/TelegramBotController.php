<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Api;

class TelegramBotController extends Controller
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api('6911855875:AAGItisToDgRsQaIBy7V-wpexVdL53f5Sw0'); // Replace with your actual bot token
    }

    public function webhook(Request $request)
    {
        $update = $request->get('update');

        // Handle /start command
        if (isset($update['message']['text']) && $update['message']['text'] === '/start') {
            $chatId = $update['message']['chat']['id'];
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Bot is running! You can use the commands /get and /post to interact with the bot.',
            ]);
        }

        // Handle /get command
        if (isset($update['message']['text']) && $update['message']['text'] === '/get') {
            $chatId = $update['message']['chat']['id'];
            $response = file_get_contents('https://api.my.thedhruvish.com/api/get-ipinfo');
            $data = json_decode($response, true);

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Received data: ' . json_encode($data),
            ]);
        }

        // Handle /post command
        if (preg_match('/\/post (.+)/', $update['message']['text'], $matches)) {
            $chatId = $update['message']['chat']['id'];
            $params = explode(' ', $matches[1]);

            if (count($params) !== 3) {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Please provide name, email, and year in this format: /post name email year',
                ]);
                return;
            }

            [$name, $email, $year] = $params;

            $response = $this->postDataToApi($name, $email, $year);

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Received response: ' . json_encode($response),
            ]);
        }
    }

    protected function postDataToApi($name, $email, $year)
    {
        $url = 'https://example.com/post';
        $data = [
            'name' => $name,
            'email' => $email,
            'year' => $year,
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        return json_decode($result, true);
    }
}
