<?php require __DIR__ . '/vendor/autoload.php';

/** < settings.json >
 * options      | array
 * - stop       | bool
 * api          | array
 * - ping       | string
 * - data       | string
 * git          | array
 * - token      | string
 * - owner      | string
 * - repo       | string
 * - branch     | string
 * - filename   | string
 */

$settings = json_decode(file_get_contents(__DIR__ . '/settings.json'), true);

if ($settings['options']['stop']) die;

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
                ->url($settings['api']['ping']),

            'get' => App\Request::init()
                ->nobody(false)
                ->timeout(120)
                ->proxy($ip, (int) $port, 5)
                ->url($settings['api']['data']),

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

                if ($mb > 1) {

                    App\Git::init($settings['git']['token'])->push(
                        $settings['git']['owner'],
                        $settings['git']['repo'],
                        $settings['git']['branch'],
                        $settings['git']['filename'],
                        $response['body'],
                    );

                } else {
                    die("Response {$mb} Mb");
                }

            }

            break;
        }
    }
}

