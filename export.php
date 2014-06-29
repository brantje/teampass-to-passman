<?php
require_once('functions.php');
$bdd = teampass_connect();


$tree = "SELECT a.*, b.title as parent_title FROM `".$GLOBALS['pre']."nested_tree` AS a LEFT JOIN  `".$GLOBALS['pre']."nested_tree` AS b ON  `a`.`parent_id` =  `b`.`id`";
$response = $bdd->query($tree);
$folders = array();
while ($data = $response->fetch())
{
	$folders[] = $data;
	echo "Folder: ".utf8_encode($data['title'])."\n";
} 

$response = $bdd->query("SELECT a.*,b.title as folder_title FROM `".$GLOBALS['pre']."items` AS a LEFT JOIN  `".$GLOBALS['pre']."nested_tree` AS b ON  `a`.`id_tree` =  `b`.`id`;");
$rand_key = teampass_get_randkey();
$items = array();
while ($data = $response->fetch())
{
	$id = $data['id'];
	$data['label'] = utf8_encode($data['label']);
	echo utf8_encode($data['label'])."\n";
	$data['login'] = utf8_encode($data['login']);
	$data['pw'] = teampass_decrypt_pw($data['pw'],SALT,$rand_key); 
	$items[] = $data;
}
$json['items'] = $items;
$json['folders'] = $folders;
file_put_contents('passwords.json',json_encode($json));
