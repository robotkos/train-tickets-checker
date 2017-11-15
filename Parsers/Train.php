<?php
/**
 * Created by PhpStorm.
 * User: sergeyro
 * Date: 27.06.17
 * Time: 11:02
 */

namespace Parsers;

use Doctrine\ORM\Mapping\Entity;
use Goutte\Client;
use function GuzzleHttp\Psr7\str;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler;

class Train
{

    function __construct($client)
    {
        $this->client = $client;
    }

    public function start()
    {
        $guzzleClient = new GuzzleClient(
            [
                'timeout' => 0,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.62 Safari/537.36',
                    'Connection' => 'keep-alive',
//                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
//                    'X-Requested-With' => 'XMLHttpRequest',
//                    'If-None-Match' => '9db50d395293fb18eec0d4801f9a7759',
//                    'Upgrade-Insecure-Requests' => 1,

                ],
                //'proxy' => 'socks5://127.0.0.1:9050',
                'cookies' => TRUE,
                'expect' => FALSE,
                'http_errors' => FALSE,
                'debug' => FALSE,
                'allow_redirects' => true,
                'on_stats' => function (TransferStats $stats) use (&$urlProd) {
                    $urlProd = $stats->getEffectiveUri()->getPath();
                }
            ]
        );

        $postData['station_id_from'] = 2208536;
        $postData['station_id_till'] = 2200001;
        $postData['station_from'] = 'Николаев Пасс';
        $postData['station_till'] = 'Киев';
        $postData['date_dep'] = '29.12.2017';
        $postData['time_dep'] = '00:00';
        $postData['time_dep_till'] = '';
        $postData['another_ec'] = 0;
        $postData['search'] = '';

        $response = $guzzleClient->post('https://booking.uz.gov.ua/ru/purchase/search/', ['form_params' => $postData]);
        $trainsArray = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        $dataArray = [];
        foreach ($trainsArray['value'][1]['types'] as $type) {
            $dataArray[] = $type['title']. ' - '. $type['places'];
        }
        $massege = implode(PHP_EOL, $dataArray);
        $massegeForLog = implode('|', $dataArray);


        $fp = fopen(__DIR__."/../data/product_log.txt", "r");
        if ($fp) {
            $skulist = [];
            while (($buffer = fgets($fp, 4096)) !== false) {
                $skulist[] = $buffer;
            }
            if (!feof($fp)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($fp);
        }

        $oldValue = $this->removeNl(end($skulist));

        $f = fopen(__DIR__."/../data/product_log.txt", 'a+');
        fwrite($f, $massegeForLog . PHP_EOL);
        fclose($f);

        if ($oldValue != $massegeForLog){
            $bot = new \TelegramBot\Api\BotApi('191485590:AAGYozNclpq2uFVEoz3kQdvr3bSWkcm2rmI');
            $bot->sendMessage('135505641', $massege);
        }

        die();

    }

    public static function removeNl(string $string)
    {
        return trim(preg_replace('/\r\n|\r|\n/u', '', $string));
    }

}