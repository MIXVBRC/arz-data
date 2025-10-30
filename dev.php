<?php require __DIR__ . '/vendor/autoload.php';

$names = file_get_contents(__DIR__ . '/names.json');
$names = json_decode($names, true);

$shops = file_get_contents(__DIR__ . '/data.json');
$shops = json_decode($shops, true);

$types = [
    'items_sell',
    'items_buy',
];

foreach ($shops as $indexShop => $shop) {
    foreach ($types as $type) {
        foreach ($shop[$type] as $indexItem => $itemName) {
            if (preg_match("/^(\d+)\(.*\)$/", $itemName, $matches)) {
                $shops[$indexShop][$type][$indexItem] = str_replace($matches[1], $names[$matches[1]], $matches[0]);
            } else {
                $shops[$indexShop][$type][$indexItem] = str_replace($itemName, $names[$itemName], $itemName);
            }
        }
    }
}

\App\Pre::print($shops);
