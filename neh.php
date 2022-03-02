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
 * Hashes a target string to a 192-bit encryption (48 chars long consistent of hexadecimal characters)
 *
 * @param string $string
 *
 * @return string
 */
function neh_hash(string $string): string {
	$bits = neh_string2bin($string);
	neh_pattern($bits);
	$table = neh_permutations_create($bits);
	
	$a = 0x21d1434d;
	$b = 0x4af028f7;
	$c = 0x37da831c;
	$d = 0x55c797c9;
	$e = 0x160da59b;
	$f = 0x51650669;
	foreach (str_split($bits, 128) as $k => $chunk) {
		$num = 8 * $k;
		for ($i = 0; $i < 8; $i++) {
			$a = $c;
			$b = $a;
			$c = $d & $f;
			$d = $e ^ $f;
			$e = $b + $f;
			$f = abs(((bindec(substr($chunk, $i * 8, 8)) + $a << 2) * $table[(($num + $i) % 16)]) % 0xffffffff);
		}
	}
	return neh_stringify($a, $b, $c, $d, $e, $f);
}

/**
 * @internal
 *
 * @param string $string
 *
 * @return string
 */
function neh_string2bin(string $string): string {
	$characters = str_split($string);
	$str = '';
	foreach ($characters as $character)
		$str .= substr('0000000' . base_convert(unpack('H*', $character)[1], 16, 2), -8);
	return $str;
}

/**
 * @internal
 *
 * @param int ...$numbers
 *
 * @return string
 */
function neh_stringify(int ...$numbers): string {
	$str = '';
	foreach ($numbers as $number) {
		$str .= implode('', array_map(fn($k) => substr('00' . dechex((int) $k % 256), -2), [
			$number,
			$number >> 8,
			$number >> 16,
			$number >> 24
		]));
	}
	return $str;
}

/**
 * @internal
 *
 * @param string $string
 *
 * @return void
 */
function neh_pattern(string &$string): void {
	if ($string % 128 === 0) return;
	$string .= '1' . str_repeat('0', (128 - (strlen($string) % 128) - 1));
}

/**
 * @internal
 *
 * @param string $bits
 *
 * @return int[]
 */
function neh_permutations_create(string $bits): array {
	$permutations = [];
	$last = 0x20c1a;
	foreach (str_split($bits, 16) as $i => $field) {
		if ($i > 15) break;
		$permutations[] = $t = ($last + ($i << 3) + bindec(substr($field, 0, 8))) * ($last + $i + bindec(substr($field, 8)));
		$last = (int) (($t * 1.5913) / $last);
	}
	if (count($permutations) !== 16) return [...array_reverse($permutations), ...array_reverse($permutations)];
	return array_reverse($permutations);
}