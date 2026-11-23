<?php
namespace App\Support;
use InvalidArgumentException;
final class Decimal {
public static function units(string|int $v):int{$v=trim((string)$v);if(!preg_match('/^(0|[1-9][0-9]{0,9})(?:\.([0-9]{1,8}))?$/',$v,$m))throw new InvalidArgumentException('Invalid decimal amount.');return (int)$m[1]*100000000+(int)str_pad($m[2]??'',8,'0');}
public static function fromUnits(int $u):string{return sprintf('%d.%08d',intdiv($u,100000000),$u%100000000);}
public static function compare(string $a,string $b):int{return self::units($a)<=>self::units($b);}
public static function multiplyRatio(string $a,int $n,int $d):string{return self::fromUnits(intdiv(self::units($a)*$n,$d));}
}
