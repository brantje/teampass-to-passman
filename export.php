<?php
require_once('functions.php');
$bdd = teampass_connect();
$response = $bdd->query("SELECT i.id, i.label, i.id_tree, i.pw AS 
pw , i.url, i.login, i.description, i.email, t.id AS treeid, t.title AS foldertitle, t.parent_id as folder_parent
FROM teampass_items AS i
JOIN  `teampass_nested_tree` AS t ON  `i`.`id_tree` =  `t`.`id`");
$rand_key = teampass_get_randkey();
while ($data = $response->fetch())
{
	$id = $data['id'];
	$data['label'] = utf8_encode($data['label']);
	echo utf8_encode($data['label'])."\n";
	$data['login'] = utf8_encode($data['login']);
	$data['pw'] = teampass_decrypt_pw($data['pw'],SALT,$rand_key);
	$json[] = $data;
}

file_put_contents('passwords.json',json_encode($json));