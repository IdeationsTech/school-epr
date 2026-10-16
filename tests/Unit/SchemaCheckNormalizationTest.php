<?php
namespace Tests\Unit;
use App\Support\SchemaContractInspector;
use PHPUnit\Framework\TestCase;
class SchemaCheckNormalizationTest extends TestCase
{
 private function normalized(string $sql):string{return (new \ReflectionMethod(SchemaContractInspector::class,'sql'))->invoke(new SchemaContractInspector,$sql);}
 public function test_mysql_escaped_charset_literals_match_without_changing_literal_contents():void {
  $expected="kind IN ('b_form','bank_transfer')";
  $mysql="(`kind` in (_utf8mb4\\'b_form\\',_utf8mb4\\'bank_transfer\\'))";
  $this->assertSame($this->normalized($expected),$this->normalized($mysql));
  $this->assertNotSame($this->normalized($expected),$this->normalized("kind IN ('b','bank')"));
  $this->assertNotSame($this->normalized("kind = 'A (B)'"),$this->normalized("kind = 'aB'"));
 }
 public function test_mysql_regexp_function_matches_operator_representation():void {
  $this->assertSame($this->normalized("cnic IS NULL OR cnic REGEXP '^[0-9]{13}$'"),$this->normalized("((`cnic` is null) or regexp_like(`cnic`,_utf8mb4\\'^[0-9]{13}$\\'))"));
 }
 public function test_boolean_grouping_changes_are_detected():void {
  $this->assertSame($this->normalized('(a > 0 AND b > 0) OR c > 0'),$this->normalized('(((`a` > 0) and (`b` > 0)) or (`c` > 0))'));
  $this->assertNotSame($this->normalized('(a > 0 OR b > 0) AND c > 0'),$this->normalized('a > 0 OR (b > 0 AND c > 0)'));
 }
}
