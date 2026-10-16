<?php
namespace App\Models\Domain;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StudentFeeAssignment extends SchoolModel
{
 protected $table='student_fee_assignments';
 protected $guarded=['*'];
 protected function casts():array{return ['starts_on'=>'date','ends_on'=>'date','enrollment_id'=>'integer','fee_plan_id'=>'integer','assigned_by'=>'integer'];}
 public function enrollment():BelongsTo{return $this->belongsTo(Enrollment::class);}
 public function feePlan():BelongsTo{return $this->belongsTo(FeePlan::class);}
 public function assignedBy():BelongsTo{return $this->belongsTo(SchoolMembership::class,'assigned_by');}
}
