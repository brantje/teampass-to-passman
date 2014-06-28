<?php
/**
 *
 * @file          configapi.php
 * @author        Nils Laumaillé
 * @version       2.1.20
 * @copyright     (c) 2009-2014 Nils Laumaillé
 * @licensing     GNU AFFERO GPL 3.0
 * @link		  http://www.teampass.net
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

$teampass_config_file = "../includes/settings.php";
$_SESSION['CPM'] = 1;

function teampass_api_enabled() {
	$bdd = teampass_connect();
	$response = $bdd->query("select valeur from ".$GLOBALS['pre']."misc WHERE type = 'admin' AND intitule = 'api'");
	return $response->fetch(PDO::FETCH_ASSOC);
}

function teampass_connect() {
	require_once($GLOBALS['teampass_config_file']);
	try
	{
		$bdd = new PDO("mysql:host=".$GLOBALS['server'].";dbname=".$GLOBALS['database'], $GLOBALS['user'], $GLOBALS['pass']);
		return ($bdd);
	}
	catch (Exception $e)
	{
		rest_error('MYSQLERR', 'Error : ' . $e->getMessage());
	}

}

function teampass_get_keys() {
	$bdd = teampass_connect();
	$response = $bdd->query("select value from ".$GLOBALS['pre']."api WHERE type = 'key'");

	return $response->fetch(PDO::FETCH_ASSOC);
}

function teampass_get_randkey() {
	$bdd = teampass_connect();
	$response = $bdd->query("select rand_key from ".$GLOBALS['pre']."keys limit 0,1");

	$array = $response->fetch(PDO::FETCH_OBJ);

	return $array->rand_key;
}


function teampass_pbkdf2_hash($p, $s, $c, $kl, $st = 0, $a = 'sha256')
{
    $kb = $st + $kl;
    $dk = '';

    for ($block = 1; $block <= $kb; $block++) {
        $ib = $h = hash_hmac($a, $s . pack('N', $block), $p, true);
        for ($i = 1; $i < $c; $i++) {
            $ib ^= ($h = hash_hmac($a, $h, $p, true));
        }
        $dk .= $ib;
    }

    return substr($dk, $st, $kl);
}

function teampass_decrypt_pw($encrypted, $salt, $rand_key, $itcount = 2072)
{
    $encrypted = base64_decode($encrypted);
    $pass_salt = substr($encrypted, -64);
    $encrypted = substr($encrypted, 0, -64);
    $key       = teampass_pbkdf2_hash($salt, $pass_salt, $itcount, 16, 32);
    $iv        = base64_decode(substr($encrypted, 0, 43) . '==');
    $encrypted = substr($encrypted, 43);
    $mac       = substr($encrypted, -64);
    $encrypted = substr($encrypted, 0, -64);
    if ($mac !== hash_hmac('sha256', $encrypted, $salt)) return null;
    return substr(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $encrypted, 'ctr', $iv), "\0\4"), strlen($rand_key));
}
