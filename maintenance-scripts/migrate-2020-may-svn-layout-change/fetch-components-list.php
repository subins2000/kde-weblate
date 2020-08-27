<?php
// Fill this from cookie header in browser
$cookies = 'sessionid=h0773j0ouq97hwac1z2hsw0r05hp7mig';

$components = [];

$url = 'https://kde.smc.org.in/api/projects/kde/components/?format=json';
while ($url !== null) {
  $componentsListResponse = file_get_contents($url, false, stream_context_create([
    'http' => [
      'header' => 'Cookie: ' . $cookies . "\r\n"
    ]
  ]));
  $componentsList = json_decode($componentsListResponse, true);
  $components = array_merge($components, $componentsList['results']);

  $url = $componentsList['next'];
}

print_r($components);
file_put_contents('components.json', json_encode($components));
echo 'Done';