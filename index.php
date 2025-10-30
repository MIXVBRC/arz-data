<?php require __DIR__ . '/vendor/autoload.php';

try {

    $settings = json_decode(file_get_contents(__DIR__ . '/settings.json'), true);

    if ($settings['options']['stop']) die;

    $types = [
        'items_sell',
        'items_buy',
    ];

    $names = file_get_contents(__DIR__ . '/names.json');

    $api = $settings['options']['reserve'] ? $settings['reserve-api'] : $settings['api'];

    $proxyList = [];

    $file = fopen(__DIR__ . '/proxy.txt', 'r');

    if ($file) {

        while (($line = fgets($file)) !== false) {

            $explode = explode(':', $line);

            $ip = trim($explode[0]);
            $port = trim($explode[1]);

            $proxyList[] = [

                'check' => App\Request::init()
                    ->nobody(true)
                    ->proxy($ip, (int) $port, 5)
                    ->url($api['ping']),

                'get' => App\Request::init()
                    ->nobody(false)
                    ->timeout(120)
                    ->proxy($ip, (int) $port, 5)
                    ->url($api['data']),

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

                if (!empty($response['body'])) {

                    $mb = ceil(strlen($response['body']) / 1024 / 1024 * 100) / 100;

                    if ($mb > 0.1) {

                        foreach ($response['body'] as $indexShop => $shop) {
                            foreach ($types as $type) {
                                foreach ($shop[$type] as $indexItem => $itemName) {
                                    if (preg_match("/^(\d+)\(.*\)$/", $itemName, $matches)) {
                                        $response['body'][$indexShop][$type][$indexItem] = str_replace($matches[1], $names[$matches[1]], $matches[0]);
                                    } else {
                                        $response['body'][$indexShop][$type][$indexItem] = str_replace($itemName, $names[$itemName], $itemName);
                                    }
                                }
                            }
                        }

                        App\Git::init($settings['git']['token'])->push(
                            $settings['git']['owner'],
                            $settings['git']['repo'],
                            $settings['git']['branch'],
                            $settings['git']['filename'],
                            $response['body'],
                        );

                    } else {
                        throw new Exception("Small amount of data was received: {$mb}Mb");
                    }

                }

                break;
            }
        }
    }

} catch (Exception $exception) {

    echo date("d.m.y H:i:s") . ' | ' . $exception->getMessage() . PHP_EOL;

}