<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class VerifyDatabase extends Command
{
 protected $signature='database:verify';
 protected $description='Read-only verification of declared tables, columns and school-isolated foreign keys';
 public function handle():int {
  $spec=json_decode(file_get_contents(database_path('schema/schema.json')),true,512,JSON_THROW_ON_ERROR);$missing=[];
  foreach($spec as $t){if(!Schema::hasTable($t['name'])){$missing[]='Missing table '.$t['name'];continue;}foreach(array_merge(['id','created_at','updated_at'],$t['tenant']?['school_id']:[],array_column($t['columns'],'name')) as $col)if(!Schema::hasColumn($t['name'],$col))$missing[]=$t['name'].'.'.$col;}
  $db=DB::connection()->getDatabaseName();$tables=DB::select('SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?',[$db,'BASE TABLE'])[0]->n;
  $fk=DB::select('SELECT COUNT(*) AS n FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND CONSTRAINT_TYPE = ?',[$db,'FOREIGN KEY'])[0]->n;
  $checks=DB::select('SELECT COUNT(*) AS n FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND CONSTRAINT_TYPE = ?',[$db,'CHECK'])[0]->n;
  $expectedFk=array_sum(array_map(fn($t)=>count($t['refs'])+(int)$t['tenant'],$spec));
  $expectedChecks=array_sum(array_map(fn($t)=>count($t['checks']),$spec));
  $this->table(['Check','Observed'],[['Database',$db],['Tables',$tables],['Foreign keys',$fk.' (expected '.$expectedFk.')'],['CHECK constraints',$checks.' (expected '.$expectedChecks.')'],['Declared column issues',count($missing)]]);
  foreach($missing as $issue)$this->error($issue);
  if($missing || (int)$fk<$expectedFk || (int)$checks<$expectedChecks){$this->error('Schema verification failed. Run pending migrations; this command changed no data.');return self::FAILURE;}
  $this->info('Declared schema present. Read-only inspection; no student/guardian values were printed.');return self::SUCCESS;
 }
}
