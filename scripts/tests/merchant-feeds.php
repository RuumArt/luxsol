<?php
/** CLI-only checks: channel exclusions, price units, XML and HTTP failure boundary. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

$root = dirname(__DIR__, 2);
require $root . '/www/local/php_interface/room/classes/Tools/CatalogFeedData.php';
require $root . '/www/local/php_interface/room/classes/Tools/MerchantFeed.php';
require $root . '/www/local/php_interface/room/classes/Tools/MicrosoftMerchantFeed.php';

use Room\Tools\MerchantFeed;
use Room\Tools\MicrosoftMerchantFeed;

$passed = 0;
$check = static function (string $name, bool $ok) use (&$passed): void {
    if (!$ok) { throw new RuntimeException('FAIL: ' . $name); }
    echo 'PASS: ', $name, PHP_EOL;
    $passed++;
};
$item = [
    'id' => '320010', 'title' => "Sieť & <PP>\x01", 'description' => 'Siete — biela/čierna',
    'link' => 'https://luxsol.sk/catalog/net/?a=1&b=2',
    'image_link' => 'https://luxsol.sk/upload/net.jpg',
    'additional_image_link' => ['https://luxsol.sk/upload/net-2.jpg'],
    'availability' => 'in_stock', 'price' => '2.98 EUR', 'condition' => 'new',
    'brand' => 'Luxsol', 'identifier_exists' => 'no', 'product_type' => 'Siete > Ochranné siete',
    'custom_label_0' => 'Ochranné siete', 'shipping' => ['service' => 'Packeta', 'price' => 2.83],
    'handling' => [2, 2], 'site_id' => 564,
    'unit_pricing_measure' => '1 sqm', 'unit_pricing_base_measure' => '1 sqm',
    'made_to_measure' => true, 'ads_allowed' => true,
];
$accessory = array_replace($item, ['id' => '870001', 'site_id' => 999, 'ads_allowed' => false]);
$unavailable = array_replace($item, ['id' => '320022', 'availability' => 'out_of_stock']);
$data = ['items' => [$item, $accessory, $unavailable], 'skipped' => [[100, 'Missing photo', 'нет фото']]];
$feed = MicrosoftMerchantFeed::select($data);
$check('accessories excluded, unavailable goods retained', array_column($feed['items'], 'id') === ['320010', '320022']);
$check('source data is not mutated by Microsoft selection', count($data['items']) === 3 && $data['items'][1]['ads_allowed'] === false);
$check('exclusions explain the omission', count($feed['skipped']) === 2 && $feed['skipped'][1][0] === 999);
$check('public ID and VAT-inclusive price retained', $feed['items'][0]['id'] === '320010' && $feed['items'][0]['price'] === '2.98 EUR');
$check('square metre price units retained', $feed['items'][0]['unit_pricing_measure'] === '1 sqm');
$check('channel metadata does not leak to Microsoft', !array_intersect_key($feed['items'][0], array_flip(['made_to_measure', 'ads_allowed', 'handling'])));

$xml = MicrosoftMerchantFeed::render($feed['items']);
$doc = new DOMDocument();
$check('Microsoft XML parses', $doc->loadXML($xml));
$xpath = new DOMXPath($doc);
$xpath->registerNamespace('g', 'http://base.google.com/ns/1.0');
$check('namespace and item count correct', $xpath->query('/rss/channel/item/g:id')->length === 2);
$check('XML text escapes round trip and invalid control is removed', $xpath->evaluate('string(/rss/channel/item[1]/g:title)') === 'Sieť & <PP>');
$check('product URL query preserved', $xpath->evaluate('string(/rss/channel/item[1]/g:link)') === $item['link']);
$check('Slovak text preserved', $xpath->evaluate('string(/rss/channel/item[1]/g:description)') === $item['description']);
$check('Microsoft stock vocabulary correct', $xpath->evaluate('string(/rss/channel/item[1]/g:availability)') === 'in stock' && $xpath->evaluate('string(/rss/channel/item[2]/g:availability)') === 'out of stock');
$check('additional image present', $xpath->query('//g:additional_image_link')->length === 2);
$check('shipping price uses EUR', $xpath->evaluate('string(/rss/channel/item[1]/g:shipping/g:price)') === '2.83 EUR');
$check('internal fields and Google-only policies omitted', !str_contains($xml, '<g:site_id>') && !str_contains($xml, 'excluded_destination') && !str_contains($xml, 'return_policy_label') && !str_contains($xml, 'min_handling_time'));

$invalid = array_replace($item, ['id' => str_repeat('a', 51)]);
$check('oversized ID excluded with a reason', count(MicrosoftMerchantFeed::select(['items' => [$invalid], 'skipped' => []])['skipped']) === 1);
$caught = false;
try { MicrosoftMerchantFeed::select(['items' => [$item, $item], 'skipped' => []]); } catch (RuntimeException $e) { $caught = true; }
$check('duplicate IDs abort instead of silently overwriting products', $caught);
$caught = false;
try { MicrosoftMerchantFeed::render([]); } catch (RuntimeException $e) { $caught = true; }
$check('empty feed fails closed', $caught);
$check('Google and Microsoft cache directories isolated', MerchantFeed::CACHE_DIR !== MicrosoftMerchantFeed::CACHE_DIR);

$googleItem = $item;
unset($googleItem['made_to_measure'], $googleItem['ads_allowed']);
$googleItem['return_policy_label'] = 'na-mieru';
$googleItem['excluded_destination'] = ['Shopping_ads', 'Display_ads'];
$googleXml = MerchantFeed::render([$googleItem]);
$googleDoc = new DOMDocument();
$check('Google XML still parses', $googleDoc->loadXML($googleXml));
$check('Google exclusions and returns retained', str_contains($googleXml, '<g:excluded_destination>Shopping_ads</g:excluded_destination>') && str_contains($googleXml, '<g:return_policy_label>na-mieru</g:return_policy_label>'));
$check('Google stock spelling and delivery timing retained', str_contains($googleXml, '<g:availability>in_stock</g:availability>') && str_contains($googleXml, '<g:min_handling_time>2</g:min_handling_time>') && str_contains($googleXml, '<g:max_transit_time>3</g:max_transit_time>'));

require $root . '/www/urlrewrite.php';
$route = array_values(array_filter($arUrlRewrite, static fn($r) => $r['PATH'] === '/feeds/microsoft-merchant.php'))[0];
$check('XML route accepts plain path and cache-busting query', preg_match($route['CONDITION'], '/feeds/microsoft-merchant.xml') === 1 && preg_match($route['CONDITION'], '/feeds/microsoft-merchant.xml?check=1') === 1);
$check('XML route is exact', preg_match($route['CONDITION'], '/feeds/microsoft-merchant.xml/other') === 0);

// Exercise the real HTTP handler in child PHP processes using a fake prolog.
// No database, network requests, catalogue changes or Bitrix installation needed.
$dir = sys_get_temp_dir() . '/luxsol-feed-test-' . bin2hex(random_bytes(6));
$prolog = $dir . '/bitrix/modules/main/include/prolog_before.php';
mkdir(dirname($prolog), 0700, true);
try {
    foreach ([false, true] as $fails) {
        $method = $fails ? 'throw new \\RuntimeException("private-error-details");' : 'return "<rss><channel><item/></channel></rss>";';
        file_put_contents($prolog, '<?php namespace Room\\Tools; echo "bootstrap-noise"; class MicrosoftMerchantFeed { public static function xml(): string { ' . $method . ' } }');
        $run = '$_SERVER["DOCUMENT_ROOT"]=' . var_export($dir, true) . '; register_shutdown_function(function(){fwrite(STDERR,"HTTP=".http_response_code());}); require ' . var_export($root . '/www/feeds/microsoft-merchant.php', true) . ';';
        $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', '-d', 'display_errors=1', '-r', $run], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $body = stream_get_contents($pipes[1]); $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]); $exit = proc_close($process);
        $check('handler ' . ($fails ? 'error' : 'success') . ' returns expected HTTP status', $exit === 0 && str_contains($err, 'HTTP=' . ($fails ? '503' : '200')));
        $check('handler ' . ($fails ? 'error' : 'success') . ' strips bootstrap noise and private details', !str_contains($body, 'bootstrap-noise') && !str_contains($body, 'private-error-details'));
        $check('handler ' . ($fails ? 'error' : 'success') . ' returns a complete XML document', (new DOMDocument())->loadXML($body));
    }
} finally {
    unlink($prolog);
    for ($d = dirname($prolog); $d !== dirname($dir); $d = dirname($d)) { rmdir($d); }
}
echo $passed, ' feed checks passed', PHP_EOL;
