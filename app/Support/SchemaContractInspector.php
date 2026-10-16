<?php
namespace App\Support;
use Illuminate\Support\Facades\DB;
class SchemaContractInspector
{
 private function sql(string $value):string {
  $value=strtolower(str_replace('`','',$value));$value=preg_replace("/_[a-z0-9]+(?=')/",'',$value);
  $value=preg_replace("/([a-z_][a-z_0-9]*)\\s+regexp\\s+('[^']*')/i",'regexp_like($1,$2)',$value);
  return preg_replace('/[\\s()]+/','',$value);
 }
 public function inspect():array {
  $spec=json_decode(file_get_contents(database_path('schema/schema.json')),true,512,JSON_THROW_ON_ERROR);$byName=array_column($spec,null,'name');$db=DB::connection()->getDatabaseName();$issues=[];
  $columns=[];foreach(DB::select('SELECT TABLE_NAME AS t,COLUMN_NAME AS c,DATA_TYPE AS type,COLUMN_TYPE AS declaration,IS_NULLABLE AS nullable,CHARACTER_MAXIMUM_LENGTH AS length,NUMERIC_PRECISION AS precision_value,NUMERIC_SCALE AS scale_value FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ?',[$db]) as $c)$columns[$c->t][$c->c]=$c;
  $unique=[];$indices=[];foreach(DB::select('SELECT TABLE_NAME AS t,INDEX_NAME AS n,COLUMN_NAME AS c,SUB_PART AS prefix_length FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND NON_UNIQUE = 0 ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX',[$db]) as $i)$indices[$i->t][$i->n][]= $i->prefix_length===null?$i->c:$i->c.':prefix';
  foreach($indices as $table=>$sets)foreach($sets as $cols)$unique[$table][]=implode(',',$cols);
  $fks=[];foreach(DB::select('SELECT k.TABLE_NAME AS t,k.CONSTRAINT_NAME AS n,k.COLUMN_NAME AS c,k.REFERENCED_TABLE_NAME AS parent_table,k.REFERENCED_COLUMN_NAME AS parent_column,r.DELETE_RULE AS delete_rule FROM information_schema.KEY_COLUMN_USAGE k JOIN information_schema.REFERENTIAL_CONSTRAINTS r ON r.CONSTRAINT_SCHEMA=k.CONSTRAINT_SCHEMA AND r.CONSTRAINT_NAME=k.CONSTRAINT_NAME AND r.TABLE_NAME=k.TABLE_NAME WHERE k.CONSTRAINT_SCHEMA = ? AND k.REFERENCED_TABLE_NAME IS NOT NULL ORDER BY k.TABLE_NAME,k.CONSTRAINT_NAME,k.ORDINAL_POSITION',[$db]) as $f){$key=$f->t.'.'.$f->n;$fks[$key]['table']=$f->t;$fks[$key]['columns'][]=$f->c;$fks[$key]['parent']=$f->parent_table;$fks[$key]['references'][]=$f->parent_column;$fks[$key]['delete']=$f->delete_rule;}
  $actualChecks=[];foreach(DB::select('SELECT t.TABLE_NAME AS t,c.CHECK_CLAUSE AS expression_value,t.ENFORCED AS enforced FROM information_schema.TABLE_CONSTRAINTS t JOIN information_schema.CHECK_CONSTRAINTS c ON c.CONSTRAINT_SCHEMA=t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME=t.CONSTRAINT_NAME WHERE t.CONSTRAINT_SCHEMA = ? AND t.CONSTRAINT_TYPE = ?',[$db,'CHECK']) as $c)if($c->enforced==='YES')$actualChecks[$c->t][]=$this->sql($c->expression_value);
  $expectedFk=0;$expectedChecks=0;
  foreach($spec as $t){$name=$t['name'];if(!isset($columns[$name])){$issues[]='Missing table '.$name;continue;}
   $declared=array_merge([['name'=>'id','type'=>'id','nullable'=>false],['name'=>'created_at','type'=>'timestamp','nullable'=>true],['name'=>'updated_at','type'=>'timestamp','nullable'=>true]],$t['tenant']?[['name'=>'school_id','type'=>'id','nullable'=>false]]:[],$t['columns']);
   foreach($declared as $c){$field=$name.'.'.$c['name'];$a=$columns[$name][$c['name']]??null;if(!$a){$issues[]='Missing column '.$field;continue;}
    if(($a->nullable==='YES')!==$c['nullable'])$issues[]='Nullability differs: '.$field;
    $types=['id'=>'bigint','uint'=>'int','string'=>'varchar','bool'=>'tinyint','money'=>'decimal','decimal'=>'decimal'];$type=$types[$c['type']]??$c['type'];
    if($a->type!==$type)$issues[]='Storage type differs: '.$field;
    if(in_array($c['type'],['id','uint'])&&!str_contains($a->declaration,'unsigned'))$issues[]='Unsigned identity/count expected: '.$field;
    if($c['type']==='string'&&(int)$a->length!==($c['length']??160))$issues[]='String length differs: '.$field;
    if(in_array($c['type'],['money','decimal'])&&((int)$a->precision_value!==12||(int)$a->scale_value!==2))$issues[]='DECIMAL(12,2) expected: '.$field;
   }
   $requiredUnique=$t['unique'];if($t['tenant'])$requiredUnique[]=['school_id','id'];
   foreach($requiredUnique as $set)if(!in_array(implode(',',$set),$unique[$name]??[],true))$issues[]='Missing complete UNIQUE key: '.$name.'('.implode(',',$set).')';
   $relationships=$t['composite_refs']??[];
   if($t['tenant'])$relationships[]=['columns'=>['school_id'],'table'=>'schools','references'=>['id']];
   foreach($t['refs'] as $column=>$parent){$composite=$t['tenant']&&($byName[$parent]['tenant']??false);$relationships[]=['columns'=>$composite?['school_id',$column]:[$column],'table'=>$parent,'references'=>$composite?['school_id','id']:['id']];}
   foreach($relationships as $r){$expectedFk++;$found=false;foreach($fks as $actual)if($actual['table']===$name&&$actual['columns']===$r['columns']&&$actual['parent']===$r['table']&&$actual['references']===$r['references']&&in_array($actual['delete'],['RESTRICT','NO ACTION'])){$found=true;break;}if(!$found)$issues[]='Missing/mismatched restricted FK: '.$name.'('.implode(',',$r['columns']).') → '.$r['table'].'('.implode(',',$r['references']).')';}
   foreach($t['checks'] as $expression){$expectedChecks++;if(!in_array($this->sql($expression),$actualChecks[$name]??[],true))$issues[]='Missing/changed enforced CHECK: '.$name.' / '.$expression;}
  }
  $tables=DB::select('SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?',[$db,'BASE TABLE'])[0]->n;
  return ['database'=>$db,'tables'=>(int)$tables,'foreign_keys'=>count($fks),'expected_foreign_keys'=>$expectedFk,'checks'=>array_sum(array_map('count',$actualChecks)),'expected_checks'=>$expectedChecks,'issues'=>$issues];
 }
}
