<?php


/*
 *   ____   ____
 *  /    \_/ __ \
 * |   |  \  ___/
 * |___|  /\___  >
 *      \/     \/
 *
 * NATURAL ENCRYPTION LIBRARY FOR PHP © 2022
 * https://github.com/HimmelKreis4865/ne
 */


/**
 * Encrypts a string using given key
 * Encrypted string length is 3n (n = length of the original message)
 * Encryption Complexity 16711425 ** n
 *
 * Can be decrypted using the key and @see nes_decrypt()
 *
 * @param string $string
 * @param string $key
 *
 * @return string
 */
function nes_encrypt(string $string, string $key): string {
	$bytes = nes_string2bin($string);
	$keyBytes = nes_string2bin($key);
	$table = nes_create_permutations($keyBytes);
	$mod = 0xf2a;
	$addition = nes_string_mod($keyBytes);
	$str = '';
	foreach (str_split($bytes, 8) as $i => $byte) {
		$perm = $table[$i % 32];
		$mod ^= ($perm / floor($perm / 100) * 100000 - 10000000) * $addition;
		$num = hexdec(base_convert($byte, 2, 16)) * (($mod * $perm) % 0xffff);
		$str .= chr(($num >> 16) & 0xff) . chr(($num >> 8) & 0xff) . chr($num & 0xff);
	}
	return $str;
}

/**
 * Decrypts a string encrypted using the same key and @see nes_encrypt()
 *
 * @param string $string
 * @param string $key
 *
 * @return string
 */
function nes_decrypt(string $string, string $key): string {
	$keyBytes = nes_string2bin($key);
	$table = nes_create_permutations($keyBytes);
	$mod = 0xf2a;
	$addition = nes_string_mod($keyBytes);
	$str = '';
	foreach (str_split($string, 3) as $i => $set) {
		$num = (ord($set[0]) << 16) | (ord($set[1]) << 8) | ord($set[2]);
		$perm = $table[$i % 32];
		$mod ^= ($perm / floor($perm / 100) * 100000 - 10000000) * $addition;
		$str .= pack('H*', dechex($num / (($mod * $perm) % 0xffff)));
	}
	return $str;
}

/**
 * @internal
 *
 * @param string $keyBytes
 *
 * @return int[]
 */
function nes_create_permutations(string $keyBytes): array {
	$byteMap = str_split(substr(str_repeat($keyBytes, ceil(256 / strlen($keyBytes))), 0, 256), 8);
	$mod = nes_string_mod($keyBytes);
	$permutations = [];
	$last = 0x1a39f3;
	for ($i = 0; $i < 32; $i++) {
		$permutations[] = $t = ((($byteMap[$i] % $mod) * ($i & 0xf) >> 2) ^ $last) | (($byteMap[$i] + $mod) << 2);
		$last = $t;
	}
	return $permutations;
}

/**
 * @param string $keyBytes
 *
 * @return int
 */
function nes_string_mod(string $keyBytes): int {
	$v = 0;
	foreach (str_split($keyBytes, 8) as $part) $v += bindec($part);
	return $v % 2048;
}

/**
 * @internal
 *
 * @param string $string
 *
 * @return string
 */
function nes_string2bin(string $string): string {
	$characters = str_split($string);
	$str = '';
	foreach ($characters as $character)
		$str .= substr('00' . base_convert(unpack('H*', $character)[1], 16, 2), -8);
	return $str;
}