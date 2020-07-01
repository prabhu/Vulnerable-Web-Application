<?php

$vendorDir = dirname(__DIR__);
$rootDir = dirname(dirname(__DIR__));

return array (
  'barrelstrength/sprout-forms' => 
  array (
    'class' => 'barrelstrength\\sproutforms\\SproutForms',
    'basePath' => $vendorDir . '/barrelstrength/sprout-forms/src',
    'handle' => 'sprout-forms',
    'aliases' => 
    array (
      '@barrelstrength/sproutforms' => $vendorDir . '/barrelstrength/sprout-forms/src',
    ),
    'name' => 'Sprout Forms',
    'version' => '3.8.8',
    'description' => 'Simple, beautiful forms. 100% control.',
    'developer' => 'Barrel Strength',
    'developerUrl' => 'https://www.barrelstrengthdesign.com/',
    'documentationUrl' => 'https://sprout.barrelstrengthdesign.com/docs/forms',
  ),
);
