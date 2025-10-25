<?php require __DIR__ . '/vendor/autoload.php';

$git = json_decode(file_get_contents(__DIR__ . '/git.json'));

if (!empty($git)) {

    $proxyList = [];

    $file = fopen('proxy.txt', 'r');

    if ($file) {

        while (($line = fgets($file)) !== false) {

            $explode = explode(':', $line);

            $ip = trim($explode[0]);
            $port = trim($explode[1]);

            $proxyList[] = [

                'check' => App\Request::init()
                    ->nobody(true)
                    ->proxy($ip, (int) $port, 5)
                    ->url('https://arz-market.moon.wh1teend.dev'),

                'get' => App\Request::init()
                    ->nobody(false)
                    ->timeout(120)
                    ->proxy($ip, (int) $port, 5)
                    ->url('https://arz-market.moon.wh1teend.dev/api/getArizonaMarkets'),

            ];
        }

        fclose($file);
    }

    shuffle($proxyList);

    foreach ($proxyList as $proxy) {

        $response = $proxy['check']->send();
        if (empty($response['error'])) {

            $response = $proxy['get']->send();
            if (empty($response['error'])) {

                App\Git::init($git['token'])->push(
                    $git['owner'],
                    $git['repo'],
                    $git['branch'],
                    $git['filename'],
                    $response['body'],
                );

                break;
            }
        }
    }
}
