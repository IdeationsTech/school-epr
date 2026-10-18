<?php
namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
/** Bounded CSV/XLSX reader: values only, no formula evaluation or external resources. */
class StudentImportReader
{
 public const HEADERS=['admission_no','first_name','last_name','birth_date','gender','admitted_on','identity_type','identity_number','address','previous_school','section_id','starts_on','roll_no','guardian_name','guardian_cnic','guardian_phone','guardian_whatsapp','guardian_email','relationship'];
 private function fail(string $m):never{throw ValidationException::withMessages(['file'=>$m]);}
 private function xml(string $raw):\SimpleXMLElement {
  if(strlen($raw)>8*1024*1024||stripos($raw,'<!DOCTYPE')!==false||stripos($raw,'<!ENTITY')!==false)$this->fail('Unsafe or oversized workbook XML.');
  $old=libxml_use_internal_errors(true);try{$xml=simplexml_load_string($raw,\SimpleXMLElement::class,LIBXML_NONET);if(!$xml)$this->fail('Invalid workbook XML.');return $xml;}finally{libxml_clear_errors();libxml_use_internal_errors($old);}
 }
 public function read(UploadedFile $file,?array $expectedHeaders=null,string $entity="students"):array {
  $expectedHeaders ??= self::HEADERS;
  $ext=strtolower($file->getClientOriginalExtension());$rows=[];
  if($ext==='csv'){
   $h=fopen($file->getRealPath(),'r');try{while(($row=fgetcsv($h,65536,',','"',''))!==false){if(count($rows)>501)$this->fail("Import at most 500 {$entity} per file.");if(count($row)>count($expectedHeaders))$this->fail('Too many columns. Use the downloadable template.');$rows[]=$row;}}finally{fclose($h);}
  }elseif($ext==='xlsx'){
   if(!class_exists(\ZipArchive::class))$this->fail('Enable the PHP zip extension to read XLSX, or upload CSV.');$z=new \ZipArchive;if($z->open($file->getRealPath())!==true)$this->fail('Invalid XLSX workbook.');
   try{
    if($z->numFiles>1000)$this->fail('Workbook contains too many entries.');$total=0;for($i=0;$i<$z->numFiles;$i++){$total+=$z->statIndex($i)['size'];if($total>16*1024*1024)$this->fail('Workbook is too large after decompression.');}
    $shared=[];$str=$z->getFromName('xl/sharedStrings.xml');if($str!==false){$x=$this->xml($str);foreach($x->si as $si){$text='';foreach($si->xpath('.//*[local-name()="t"]') as $t)$text.=(string)$t;$shared[]=$text;}}
    $sheet=$z->getFromName('xl/worksheets/sheet1.xml');if($sheet===false)$this->fail('The workbook needs its first sheet.');$x=$this->xml($sheet);
    foreach($x->sheetData->row as $row){if(count($rows)>501)$this->fail("Import at most 500 {$entity} per file.");$values=array_fill(0,count($expectedHeaders),'');foreach($row->c as $cell){$ref=(string)$cell['r'];if(!preg_match('/^([A-Z]+)[0-9]+$/',$ref,$m))$this->fail('Invalid cell reference.');$col=0;foreach(str_split($m[1]) as $letter)$col=$col*26+ord($letter)-64;$col--;if($col>=count($expectedHeaders))$this->fail('Unexpected columns. Use the template.');if(isset($cell->f))$this->fail('Formulas are not accepted. Paste values and store dates/IDs as text.');$type=(string)$cell['t'];$v=(string)$cell->v;if($type==='s')$v=$shared[(int)$v]??'';elseif($type==='inlineStr'){$v='';foreach($cell->is->xpath('.//*[local-name()="t"]') as $t)$v.=(string)$t;}$values[$col]=$v;}$rows[]=$values;}
   }finally{$z->close();}
  }else $this->fail('Upload CSV or XLSX. Old .xls files must be saved as XLSX or CSV.');
  if(!$rows)$this->fail('The file is empty.');$headers=array_map(fn($v)=>trim((string)$v),array_shift($rows));$headers[0]=preg_replace('/^\xEF\xBB\xBF/','',$headers[0]??'');if($headers!==$expectedHeaders)$this->fail('Headers must match the downloadable template exactly.');
  $result=[];foreach($rows as $i=>$row){$row=array_map(fn($v)=>trim((string)$v),$row);if(!array_filter($row,fn($v)=>$v!==''))continue;if(count($row)!==count($expectedHeaders))$this->fail('Row '.($i+2).' has the wrong number of columns.');$result[]=['line'=>$i+2,'data'=>array_combine($expectedHeaders,$row)];}if(!$result)$this->fail("Add at least one {$entity} row below the headers.");if(count($result)>500)$this->fail("Import at most 500 {$entity} per file.");return $result;
 }
}
