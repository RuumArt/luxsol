<?php
$arUrlRewrite=array (
  0 => 
  array (
    'CONDITION' => '#^\\/?\\/mobileapp/jn\\/(.*)\\/.*#',
    'RULE' => 'componentName=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/mobileapp/jn.php',
    'SORT' => 100,
  ),
  2 => 
  array (
    'CONDITION' => '#^/bitrix/services/ymarket/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/bitrix/services/ymarket/index.php',
    'SORT' => 100,
  ),
  6 => 
  array (
    'CONDITION' => '#^/akciova-zlavy/(.+)/.*#',
    'RULE' => 'CODE=$1',
    'ID' => '',
    'PATH' => '/akciova-zlavy/detail.php',
    'SORT' => 100,
  ),
  7 => 
  array (
    'CONDITION' => '#^/projects/(.+)/.*#',
    'RULE' => 'CODE=$1',
    'ID' => '',
    'PATH' => '/projects/detail.php',
    'SORT' => 100,
  ),
  3 => 
  array (
    'CONDITION' => '#^/catalog/(.+)/.*#',
    'RULE' => 'CODE_PATH=$1',
    'ID' => '',
    'PATH' => '/catalog/index.php',
    'SORT' => 100,
  ),
  4 => 
  array (
    'CONDITION' => '#^/blog/(.+)/.*#',
    'RULE' => 'CODE=$1',
    'ID' => '',
    'PATH' => '/blog/detail.php',
    'SORT' => 100,
  ),
  1 => 
  array (
    'CONDITION' => '#^/rest/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/bitrix/services/rest/index.php',
    'SORT' => 100,
  ),
);
