<?php
$changesFile = './kde_pots_location.csv';
$componentsListFile = './components.json';

// Read CSV
$fp = fopen($changesFile, 'r');
$csv = [];
while ( ($data = fgetcsv($fp) ) !== FALSE ) {
    $csv[] = $data;
}

$newComponentsLocs = [];
foreach ($csv as $item) {
  $componentLoc = $item[1] . '/' . $item[0];
  $newComponentLoc = $item[2] . '/' . $item[0];
  $newComponentsLocs[$componentLoc] = $newComponentLoc;
}

$components = json_decode(file_get_contents($componentsListFile), true);
$newComponents = $components;

// File moving bash script
$script = '';

foreach ($components as $key => $item) {
  $componentName = $item['name']; // same as the relative file loc too
  $newComponentName = $newComponentsLocs[$componentName]; // relative fileloc = component name

  $script .= "mkdir --parents `dirname $newComponentName` && mv $componentName.po $newComponentName.po\n";

  $filemask = $item['filemask'];

  $newComponents[$key]['name'] = $newComponentName;
  $newComponents[$key]['slug'] = str_replace('/', '', $newComponentName); // no slashes allowed in slug
  $newComponents[$key]['filemask'] = str_replace($componentName, $newComponentName, $filemask);
}

print_r($newComponents);
file_put_contents('new-components.json', json_encode($newComponents));
file_put_contents('new-components-repo-restructure.sh', $script);
