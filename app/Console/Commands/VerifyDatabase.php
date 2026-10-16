<?php
namespace App\Console\Commands;
use App\Support\SchemaContractInspector;
use Illuminate\Console\Command;
class VerifyDatabase extends Command
{
 protected $signature='database:verify';
 protected $description='Read-only schema audit: types, nullability, unique keys, FK column pairs and enforced CHECK rules';
 public function handle():int {
  $a=app(SchemaContractInspector::class)->inspect();
  $this->table(['Check','Observed'],[['Database',$a['database']],['Tables',$a['tables']],['Foreign keys',$a['foreign_keys'].' (expected '.$a['expected_foreign_keys'].')'],['Enforced CHECK constraints',$a['checks'].' (expected '.$a['expected_checks'].')'],['Contract mismatches',count($a['issues'])]]);
  foreach($a['issues'] as $issue)$this->error($issue);
  if($a['issues']){$this->error('Schema differs from the agreed contract. Inspection changed no data.');return self::FAILURE;}
  $this->info('Schema contract verified. No student/guardian values were printed or modified.');return self::SUCCESS;
 }
}
