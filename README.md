# NE - Natural Encryption

## Features
 - A 192-bit hash function
 - symmetric string encryption

## API

### Hash
```php
<?php

require 'neh.php';

echo neh_hash('Hello world');
```
**Output:** `8041a8808041a880c0913a0895ba8b31483764ec7dff62d0`

The hash function returns a 192-bit string (48 hexadecimal chars)

### Symmetric encryption
```php
<?php

require 'nes.php';

// assign a key to either random bytes or a string of your choice
$key = 'custom key can be anything';

$str = nes_encrypt('Hello world', $key);

echo $str . PHP_EOL;

echo nes_decrypt($str, $key) . PHP_EOL;

// note that this is another key (a -> A)
echo nes_decrypt($str, 'custom key can Anything');
```
**Output**
```
V8Hf��`F\�Z��	t�F�9�EHR��
Hello world
&- �7� �;�h��
```

A symmetric encrypted string is not hexadecimal, rather in bytes to shorten the output.
Currently, the length of the output is 3n (n = original input message length)
