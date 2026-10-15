<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_permissions', function (Blueprint $table): void {
            $table->foreign(['role_id'], 't14_fk0')->references(['id'])->on('roles')->restrictOnDelete();
        });
        Schema::table('role_permissions', function (Blueprint $table): void {
            $table->foreign(['permission_id'], 't14_fk1')->references(['id'])->on('permissions')->restrictOnDelete();
        });
        Schema::table('campuses', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't15_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't16_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->foreign(['plan_id'], 't16_fk1')->references(['id'])->on('plans')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `subscriptions` ADD CONSTRAINT `t16_check0` CHECK (ends_on >= starts_on)');
        DB::statement('ALTER TABLE `subscriptions` ADD CONSTRAINT `t16_check1` CHECK (price >= 0)');
        Schema::table('subscription_invoices', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't17_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('subscription_invoices', function (Blueprint $table): void {
            $table->foreign(['school_id', 'subscription_id'], 't17_fk1')->references(['school_id', 'id'])->on('subscriptions')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `subscription_invoices` ADD CONSTRAINT `t17_check0` CHECK (amount >= 0)');
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't18_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'subscription_invoice_id'], 't18_fk1')->references(['school_id', 'id'])->on('subscription_invoices')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `subscription_payments` ADD CONSTRAINT `t18_check0` CHECK (amount > 0)');
        Schema::table('school_settings', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't19_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('school_memberships', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't20_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('school_memberships', function (Blueprint $table): void {
            $table->foreign(['user_id'], 't20_fk1')->references(['id'])->on('users')->restrictOnDelete();
        });
        Schema::table('membership_roles', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't21_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('membership_roles', function (Blueprint $table): void {
            $table->foreign(['school_id', 'membership_id'], 't21_fk1')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('membership_roles', function (Blueprint $table): void {
            $table->foreign(['role_id'], 't21_fk2')->references(['id'])->on('roles')->restrictOnDelete();
        });
        Schema::table('membership_campuses', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't22_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('membership_campuses', function (Blueprint $table): void {
            $table->foreign(['school_id', 'membership_id'], 't22_fk1')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('membership_campuses', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't22_fk2')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('files', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't23_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('files', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't23_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('files', function (Blueprint $table): void {
            $table->foreign(['school_id', 'uploaded_by'], 't23_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('staff_profiles', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't24_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('staff_profiles', function (Blueprint $table): void {
            $table->foreign(['school_id', 'membership_id'], 't24_fk1')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('leave_requests', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't25_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('leave_requests', function (Blueprint $table): void {
            $table->foreign(['school_id', 'staff_profile_id'], 't25_fk1')->references(['school_id', 'id'])->on('staff_profiles')->restrictOnDelete();
        });
        Schema::table('leave_requests', function (Blueprint $table): void {
            $table->foreign(['school_id', 'approved_by'], 't25_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `leave_requests` ADD CONSTRAINT `t25_check0` CHECK (ends_on >= starts_on)');
        Schema::table('academic_sessions', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't26_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `academic_sessions` ADD CONSTRAINT `t26_check0` CHECK (ends_on >= starts_on)');
        Schema::table('academic_terms', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't27_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('academic_terms', function (Blueprint $table): void {
            $table->foreign(['school_id', 'academic_session_id'], 't27_fk1')->references(['school_id', 'id'])->on('academic_sessions')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `academic_terms` ADD CONSTRAINT `t27_check0` CHECK (ends_on >= starts_on)');
        Schema::table('classes', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't28_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('sections', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't29_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('sections', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't29_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('sections', function (Blueprint $table): void {
            $table->foreign(['school_id', 'academic_session_id'], 't29_fk2')->references(['school_id', 'id'])->on('academic_sessions')->restrictOnDelete();
        });
        Schema::table('sections', function (Blueprint $table): void {
            $table->foreign(['school_id', 'class_id'], 't29_fk3')->references(['school_id', 'id'])->on('classes')->restrictOnDelete();
        });
        Schema::table('subjects', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't30_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('section_subjects', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't31_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('section_subjects', function (Blueprint $table): void {
            $table->foreign(['school_id', 'section_id'], 't31_fk1')->references(['school_id', 'id'])->on('sections')->restrictOnDelete();
        });
        Schema::table('section_subjects', function (Blueprint $table): void {
            $table->foreign(['school_id', 'subject_id'], 't31_fk2')->references(['school_id', 'id'])->on('subjects')->restrictOnDelete();
        });
        Schema::table('teacher_assignments', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't32_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('teacher_assignments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'staff_profile_id'], 't32_fk1')->references(['school_id', 'id'])->on('staff_profiles')->restrictOnDelete();
        });
        Schema::table('teacher_assignments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'section_id'], 't32_fk2')->references(['school_id', 'id'])->on('sections')->restrictOnDelete();
        });
        Schema::table('teacher_assignments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'subject_id'], 't32_fk3')->references(['school_id', 'id'])->on('subjects')->restrictOnDelete();
        });
        Schema::table('admission_inquiries', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't33_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('admission_inquiries', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't33_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('admission_applications', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't34_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('admission_applications', function (Blueprint $table): void {
            $table->foreign(['school_id', 'inquiry_id'], 't34_fk1')->references(['school_id', 'id'])->on('admission_inquiries')->restrictOnDelete();
        });
        Schema::table('admission_applications', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't34_fk2')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('admission_applications', function (Blueprint $table): void {
            $table->foreign(['school_id', 'class_id'], 't34_fk3')->references(['school_id', 'id'])->on('classes')->restrictOnDelete();
        });
        Schema::table('admission_applications', function (Blueprint $table): void {
            $table->foreign(['school_id', 'academic_session_id'], 't34_fk4')->references(['school_id', 'id'])->on('academic_sessions')->restrictOnDelete();
        });
        Schema::table('students', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't35_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('students', function (Blueprint $table): void {
            $table->foreign(['school_id', 'application_id'], 't35_fk1')->references(['school_id', 'id'])->on('admission_applications')->restrictOnDelete();
        });
        Schema::table('students', function (Blueprint $table): void {
            $table->foreign(['school_id', 'photo_file_id'], 't35_fk2')->references(['school_id', 'id'])->on('files')->restrictOnDelete();
        });
        Schema::table('guardians', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't36_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('student_guardians', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't37_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('student_guardians', function (Blueprint $table): void {
            $table->foreign(['school_id', 'student_id'], 't37_fk1')->references(['school_id', 'id'])->on('students')->restrictOnDelete();
        });
        Schema::table('student_guardians', function (Blueprint $table): void {
            $table->foreign(['school_id', 'guardian_id'], 't37_fk2')->references(['school_id', 'id'])->on('guardians')->restrictOnDelete();
        });
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't38_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'student_id'], 't38_fk1')->references(['school_id', 'id'])->on('students')->restrictOnDelete();
        });
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'section_id'], 't38_fk2')->references(['school_id', 'id'])->on('sections')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `enrollments` ADD CONSTRAINT `t38_check0` CHECK (ends_on IS NULL OR ends_on >= starts_on)');
        Schema::table('student_documents', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't39_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('student_documents', function (Blueprint $table): void {
            $table->foreign(['school_id', 'student_id'], 't39_fk1')->references(['school_id', 'id'])->on('students')->restrictOnDelete();
        });
        Schema::table('student_documents', function (Blueprint $table): void {
            $table->foreign(['school_id', 'file_id'], 't39_fk2')->references(['school_id', 'id'])->on('files')->restrictOnDelete();
        });
        Schema::table('fee_heads', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't40_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('fee_plans', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't41_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('fee_plans', function (Blueprint $table): void {
            $table->foreign(['school_id', 'academic_session_id'], 't41_fk1')->references(['school_id', 'id'])->on('academic_sessions')->restrictOnDelete();
        });
        Schema::table('fee_plans', function (Blueprint $table): void {
            $table->foreign(['school_id', 'class_id'], 't41_fk2')->references(['school_id', 'id'])->on('classes')->restrictOnDelete();
        });
        Schema::table('fee_plans', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't41_fk3')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `fee_plans` ADD CONSTRAINT `t41_check0` CHECK (ends_on IS NULL OR ends_on >= starts_on)');
        Schema::table('fee_plan_items', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't42_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('fee_plan_items', function (Blueprint $table): void {
            $table->foreign(['school_id', 'fee_plan_id'], 't42_fk1')->references(['school_id', 'id'])->on('fee_plans')->restrictOnDelete();
        });
        Schema::table('fee_plan_items', function (Blueprint $table): void {
            $table->foreign(['school_id', 'fee_head_id'], 't42_fk2')->references(['school_id', 'id'])->on('fee_heads')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `fee_plan_items` ADD CONSTRAINT `t42_check0` CHECK (amount >= 0)');
        Schema::table('student_concessions', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't43_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('student_concessions', function (Blueprint $table): void {
            $table->foreign(['school_id', 'enrollment_id'], 't43_fk1')->references(['school_id', 'id'])->on('enrollments')->restrictOnDelete();
        });
        Schema::table('student_concessions', function (Blueprint $table): void {
            $table->foreign(['school_id', 'fee_head_id'], 't43_fk2')->references(['school_id', 'id'])->on('fee_heads')->restrictOnDelete();
        });
        Schema::table('student_concessions', function (Blueprint $table): void {
            $table->foreign(['school_id', 'approved_by'], 't43_fk3')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `student_concessions` ADD CONSTRAINT `t43_check0` CHECK (kind IN (\'fixed\',\'percent\'))');
        DB::statement('ALTER TABLE `student_concessions` ADD CONSTRAINT `t43_check1` CHECK (value >= 0)');
        DB::statement('ALTER TABLE `student_concessions` ADD CONSTRAINT `t43_check2` CHECK (kind <> \'percent\' OR value <= 100)');
        DB::statement('ALTER TABLE `student_concessions` ADD CONSTRAINT `t43_check3` CHECK (ends_on IS NULL OR ends_on >= starts_on)');
        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't44_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreign(['school_id', 'enrollment_id'], 't44_fk1')->references(['school_id', 'id'])->on('enrollments')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `invoices` ADD CONSTRAINT `t44_check0` CHECK (total >= 0)');
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't45_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->foreign(['school_id', 'invoice_id'], 't45_fk1')->references(['school_id', 'id'])->on('invoices')->restrictOnDelete();
        });
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->foreign(['school_id', 'fee_head_id'], 't45_fk2')->references(['school_id', 'id'])->on('fee_heads')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `invoice_items` ADD CONSTRAINT `t45_check0` CHECK (quantity > 0)');
        DB::statement('ALTER TABLE `invoice_items` ADD CONSTRAINT `t45_check1` CHECK (unit_amount >= 0)');
        DB::statement('ALTER TABLE `invoice_items` ADD CONSTRAINT `t45_check2` CHECK (discount_amount >= 0)');
        DB::statement('ALTER TABLE `invoice_items` ADD CONSTRAINT `t45_check3` CHECK (line_total >= 0)');
        Schema::table('bank_accounts', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't46_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('bank_accounts', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't46_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't47_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't47_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'student_id'], 't47_fk2')->references(['school_id', 'id'])->on('students')->restrictOnDelete();
        });
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'bank_account_id'], 't47_fk3')->references(['school_id', 'id'])->on('bank_accounts')->restrictOnDelete();
        });
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'received_by'], 't47_fk4')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `payments` ADD CONSTRAINT `t47_check0` CHECK (amount > 0)');
        Schema::table('payment_allocations', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't48_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('payment_allocations', function (Blueprint $table): void {
            $table->foreign(['school_id', 'payment_id'], 't48_fk1')->references(['school_id', 'id'])->on('payments')->restrictOnDelete();
        });
        Schema::table('payment_allocations', function (Blueprint $table): void {
            $table->foreign(['school_id', 'invoice_id'], 't48_fk2')->references(['school_id', 'id'])->on('invoices')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `payment_allocations` ADD CONSTRAINT `t48_check0` CHECK (amount > 0)');
        Schema::table('payment_reversals', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't49_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('payment_reversals', function (Blueprint $table): void {
            $table->foreign(['school_id', 'payment_id'], 't49_fk1')->references(['school_id', 'id'])->on('payments')->restrictOnDelete();
        });
        Schema::table('payment_reversals', function (Blueprint $table): void {
            $table->foreign(['school_id', 'approved_by'], 't49_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `payment_reversals` ADD CONSTRAINT `t49_check0` CHECK (amount > 0)');
        Schema::table('reversal_allocations', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't50_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('reversal_allocations', function (Blueprint $table): void {
            $table->foreign(['school_id', 'payment_reversal_id'], 't50_fk1')->references(['school_id', 'id'])->on('payment_reversals')->restrictOnDelete();
        });
        Schema::table('reversal_allocations', function (Blueprint $table): void {
            $table->foreign(['school_id', 'payment_allocation_id'], 't50_fk2')->references(['school_id', 'id'])->on('payment_allocations')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `reversal_allocations` ADD CONSTRAINT `t50_check0` CHECK (amount > 0)');
        Schema::table('cashier_closings', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't51_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('cashier_closings', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't51_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('cashier_closings', function (Blueprint $table): void {
            $table->foreign(['school_id', 'cashier_membership_id'], 't51_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('cashier_closings', function (Blueprint $table): void {
            $table->foreign(['school_id', 'approved_by'], 't51_fk3')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `cashier_closings` ADD CONSTRAINT `t51_check0` CHECK (counted_cash >= 0)');
        Schema::table('expense_categories', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't52_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('expenses', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't53_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('expenses', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't53_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('expenses', function (Blueprint $table): void {
            $table->foreign(['school_id', 'category_id'], 't53_fk2')->references(['school_id', 'id'])->on('expense_categories')->restrictOnDelete();
        });
        Schema::table('expenses', function (Blueprint $table): void {
            $table->foreign(['school_id', 'bank_account_id'], 't53_fk3')->references(['school_id', 'id'])->on('bank_accounts')->restrictOnDelete();
        });
        Schema::table('expenses', function (Blueprint $table): void {
            $table->foreign(['school_id', 'approved_by'], 't53_fk4')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('expenses', function (Blueprint $table): void {
            $table->foreign(['school_id', 'file_id'], 't53_fk5')->references(['school_id', 'id'])->on('files')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `expenses` ADD CONSTRAINT `t53_check0` CHECK (amount > 0)');
        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't54_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->foreign(['school_id', 'section_id'], 't54_fk1')->references(['school_id', 'id'])->on('sections')->restrictOnDelete();
        });
        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->foreign(['school_id', 'submitted_by'], 't54_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't55_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->foreign(['school_id', 'attendance_session_id'], 't55_fk1')->references(['school_id', 'id'])->on('attendance_sessions')->restrictOnDelete();
        });
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->foreign(['school_id', 'enrollment_id'], 't55_fk2')->references(['school_id', 'id'])->on('enrollments')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `attendance_records` ADD CONSTRAINT `t55_check0` CHECK (status IN (\'present\',\'absent\',\'late\',\'leave\'))');
        Schema::table('diaries', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't56_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('diaries', function (Blueprint $table): void {
            $table->foreign(['school_id', 'section_id'], 't56_fk1')->references(['school_id', 'id'])->on('sections')->restrictOnDelete();
        });
        Schema::table('diaries', function (Blueprint $table): void {
            $table->foreign(['school_id', 'published_by'], 't56_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('diaries', function (Blueprint $table): void {
            $table->foreign(['school_id', 'supersedes_id'], 't56_fk3')->references(['school_id', 'id'])->on('diaries')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `diaries` ADD CONSTRAINT `t56_check0` CHECK (revision > 0)');
        Schema::table('diary_items', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't57_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('diary_items', function (Blueprint $table): void {
            $table->foreign(['school_id', 'diary_id'], 't57_fk1')->references(['school_id', 'id'])->on('diaries')->restrictOnDelete();
        });
        Schema::table('diary_items', function (Blueprint $table): void {
            $table->foreign(['school_id', 'subject_id'], 't57_fk2')->references(['school_id', 'id'])->on('subjects')->restrictOnDelete();
        });
        Schema::table('diary_items', function (Blueprint $table): void {
            $table->foreign(['school_id', 'author_membership_id'], 't57_fk3')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('diary_shares', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't58_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('diary_shares', function (Blueprint $table): void {
            $table->foreign(['school_id', 'diary_id'], 't58_fk1')->references(['school_id', 'id'])->on('diaries')->restrictOnDelete();
        });
        Schema::table('diary_shares', function (Blueprint $table): void {
            $table->foreign(['school_id', 'confirmed_by'], 't58_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('grading_schemes', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't59_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `grading_schemes` ADD CONSTRAINT `t59_check0` CHECK (version > 0)');
        Schema::table('exam_cycles', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't60_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('exam_cycles', function (Blueprint $table): void {
            $table->foreign(['school_id', 'academic_session_id'], 't60_fk1')->references(['school_id', 'id'])->on('academic_sessions')->restrictOnDelete();
        });
        Schema::table('exam_cycles', function (Blueprint $table): void {
            $table->foreign(['school_id', 'academic_term_id'], 't60_fk2')->references(['school_id', 'id'])->on('academic_terms')->restrictOnDelete();
        });
        Schema::table('exam_cycles', function (Blueprint $table): void {
            $table->foreign(['school_id', 'grading_scheme_id'], 't60_fk3')->references(['school_id', 'id'])->on('grading_schemes')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `exam_cycles` ADD CONSTRAINT `t60_check0` CHECK (ends_on >= starts_on)');
        Schema::table('tests', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't61_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('tests', function (Blueprint $table): void {
            $table->foreign(['school_id', 'section_id'], 't61_fk1')->references(['school_id', 'id'])->on('sections')->restrictOnDelete();
        });
        Schema::table('tests', function (Blueprint $table): void {
            $table->foreign(['school_id', 'subject_id'], 't61_fk2')->references(['school_id', 'id'])->on('subjects')->restrictOnDelete();
        });
        Schema::table('tests', function (Blueprint $table): void {
            $table->foreign(['school_id', 'exam_cycle_id'], 't61_fk3')->references(['school_id', 'id'])->on('exam_cycles')->restrictOnDelete();
        });
        Schema::table('tests', function (Blueprint $table): void {
            $table->foreign(['school_id', 'grading_scheme_id'], 't61_fk4')->references(['school_id', 'id'])->on('grading_schemes')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `tests` ADD CONSTRAINT `t61_check0` CHECK (max_marks > 0)');
        Schema::table('test_results', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't62_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('test_results', function (Blueprint $table): void {
            $table->foreign(['school_id', 'test_id'], 't62_fk1')->references(['school_id', 'id'])->on('tests')->restrictOnDelete();
        });
        Schema::table('test_results', function (Blueprint $table): void {
            $table->foreign(['school_id', 'enrollment_id'], 't62_fk2')->references(['school_id', 'id'])->on('enrollments')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `test_results` ADD CONSTRAINT `t62_check0` CHECK (status IN (\'pending\',\'present\',\'absent\',\'exempt\'))');
        DB::statement('ALTER TABLE `test_results` ADD CONSTRAINT `t62_check1` CHECK ((status = \'present\' AND marks IS NOT NULL AND marks >= 0) OR (status <> \'present\' AND marks IS NULL))');
        DB::statement('ALTER TABLE `test_results` ADD CONSTRAINT `t62_check2` CHECK (revision > 0)');
        Schema::table('report_snapshots', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't63_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('report_snapshots', function (Blueprint $table): void {
            $table->foreign(['school_id', 'test_result_id'], 't63_fk1')->references(['school_id', 'id'])->on('test_results')->restrictOnDelete();
        });
        Schema::table('report_snapshots', function (Blueprint $table): void {
            $table->foreign(['school_id', 'grading_scheme_id'], 't63_fk2')->references(['school_id', 'id'])->on('grading_schemes')->restrictOnDelete();
        });
        Schema::table('report_snapshots', function (Blueprint $table): void {
            $table->foreign(['school_id', 'published_by'], 't63_fk3')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `report_snapshots` ADD CONSTRAINT `t63_check0` CHECK (revision > 0)');
        Schema::table('exam_papers', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't64_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('exam_papers', function (Blueprint $table): void {
            $table->foreign(['school_id', 'test_id'], 't64_fk1')->references(['school_id', 'id'])->on('tests')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `exam_papers` ADD CONSTRAINT `t64_check0` CHECK (ends_at > starts_at)');
        Schema::table('assessment_weightages', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't65_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('assessment_weightages', function (Blueprint $table): void {
            $table->foreign(['school_id', 'exam_cycle_id'], 't65_fk1')->references(['school_id', 'id'])->on('exam_cycles')->restrictOnDelete();
        });
        Schema::table('assessment_weightages', function (Blueprint $table): void {
            $table->foreign(['school_id', 'class_id'], 't65_fk2')->references(['school_id', 'id'])->on('classes')->restrictOnDelete();
        });
        Schema::table('assessment_weightages', function (Blueprint $table): void {
            $table->foreign(['school_id', 'subject_id'], 't65_fk3')->references(['school_id', 'id'])->on('subjects')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `assessment_weightages` ADD CONSTRAINT `t65_check0` CHECK (weight >= 0 AND weight <= 100)');
        Schema::table('exam_candidates', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't66_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('exam_candidates', function (Blueprint $table): void {
            $table->foreign(['school_id', 'exam_cycle_id'], 't66_fk1')->references(['school_id', 'id'])->on('exam_cycles')->restrictOnDelete();
        });
        Schema::table('exam_candidates', function (Blueprint $table): void {
            $table->foreign(['school_id', 'enrollment_id'], 't66_fk2')->references(['school_id', 'id'])->on('enrollments')->restrictOnDelete();
        });
        Schema::table('consolidated_result_snapshots', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't67_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('consolidated_result_snapshots', function (Blueprint $table): void {
            $table->foreign(['school_id', 'exam_cycle_id'], 't67_fk1')->references(['school_id', 'id'])->on('exam_cycles')->restrictOnDelete();
        });
        Schema::table('consolidated_result_snapshots', function (Blueprint $table): void {
            $table->foreign(['school_id', 'enrollment_id'], 't67_fk2')->references(['school_id', 'id'])->on('enrollments')->restrictOnDelete();
        });
        Schema::table('consolidated_result_snapshots', function (Blueprint $table): void {
            $table->foreign(['school_id', 'grading_scheme_id'], 't67_fk3')->references(['school_id', 'id'])->on('grading_schemes')->restrictOnDelete();
        });
        Schema::table('consolidated_result_snapshots', function (Blueprint $table): void {
            $table->foreign(['school_id', 'published_by'], 't67_fk4')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `consolidated_result_snapshots` ADD CONSTRAINT `t67_check0` CHECK (revision > 0)');
        Schema::table('report_shares', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't68_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('report_shares', function (Blueprint $table): void {
            $table->foreign(['school_id', 'report_snapshot_id'], 't68_fk1')->references(['school_id', 'id'])->on('report_snapshots')->restrictOnDelete();
        });
        Schema::table('report_shares', function (Blueprint $table): void {
            $table->foreign(['school_id', 'consolidated_snapshot_id'], 't68_fk2')->references(['school_id', 'id'])->on('consolidated_result_snapshots')->restrictOnDelete();
        });
        Schema::table('report_shares', function (Blueprint $table): void {
            $table->foreign(['school_id', 'guardian_id'], 't68_fk3')->references(['school_id', 'id'])->on('guardians')->restrictOnDelete();
        });
        Schema::table('report_shares', function (Blueprint $table): void {
            $table->foreign(['school_id', 'confirmed_by'], 't68_fk4')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `report_shares` ADD CONSTRAINT `t68_check0` CHECK ((report_snapshot_id IS NULL) <> (consolidated_snapshot_id IS NULL))');
        Schema::table('staff_attendance_records', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't69_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('staff_attendance_records', function (Blueprint $table): void {
            $table->foreign(['school_id', 'staff_profile_id'], 't69_fk1')->references(['school_id', 'id'])->on('staff_profiles')->restrictOnDelete();
        });
        Schema::table('staff_attendance_records', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't69_fk2')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('staff_attendance_records', function (Blueprint $table): void {
            $table->foreign(['school_id', 'leave_request_id'], 't69_fk3')->references(['school_id', 'id'])->on('leave_requests')->restrictOnDelete();
        });
        Schema::table('staff_attendance_records', function (Blueprint $table): void {
            $table->foreign(['school_id', 'recorded_by'], 't69_fk4')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `staff_attendance_records` ADD CONSTRAINT `t69_check0` CHECK (status IN (\'present\',\'absent\',\'late\',\'leave\'))');
        Schema::table('salary_structures', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't70_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('salary_structures', function (Blueprint $table): void {
            $table->foreign(['school_id', 'staff_profile_id'], 't70_fk1')->references(['school_id', 'id'])->on('staff_profiles')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `salary_structures` ADD CONSTRAINT `t70_check0` CHECK (ends_on IS NULL OR ends_on >= starts_on)');
        Schema::table('salary_structure_items', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't71_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('salary_structure_items', function (Blueprint $table): void {
            $table->foreign(['school_id', 'salary_structure_id'], 't71_fk1')->references(['school_id', 'id'])->on('salary_structures')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `salary_structure_items` ADD CONSTRAINT `t71_check0` CHECK (kind IN (\'basic\',\'allowance\',\'deduction\'))');
        DB::statement('ALTER TABLE `salary_structure_items` ADD CONSTRAINT `t71_check1` CHECK (amount >= 0)');
        Schema::table('staff_advances', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't72_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('staff_advances', function (Blueprint $table): void {
            $table->foreign(['school_id', 'staff_profile_id'], 't72_fk1')->references(['school_id', 'id'])->on('staff_profiles')->restrictOnDelete();
        });
        Schema::table('staff_advances', function (Blueprint $table): void {
            $table->foreign(['school_id', 'approved_by'], 't72_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `staff_advances` ADD CONSTRAINT `t72_check0` CHECK (amount > 0)');
        DB::statement('ALTER TABLE `staff_advances` ADD CONSTRAINT `t72_check1` CHECK (monthly_recovery > 0)');
        Schema::table('payroll_runs', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't73_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('payroll_runs', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't73_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('payroll_runs', function (Blueprint $table): void {
            $table->foreign(['school_id', 'approved_by'], 't73_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `payroll_runs` ADD CONSTRAINT `t73_check0` CHECK (revision > 0)');
        Schema::table('payroll_items', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't74_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('payroll_items', function (Blueprint $table): void {
            $table->foreign(['school_id', 'payroll_run_id'], 't74_fk1')->references(['school_id', 'id'])->on('payroll_runs')->restrictOnDelete();
        });
        Schema::table('payroll_items', function (Blueprint $table): void {
            $table->foreign(['school_id', 'staff_profile_id'], 't74_fk2')->references(['school_id', 'id'])->on('staff_profiles')->restrictOnDelete();
        });
        Schema::table('payroll_items', function (Blueprint $table): void {
            $table->foreign(['school_id', 'salary_structure_id'], 't74_fk3')->references(['school_id', 'id'])->on('salary_structures')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `payroll_items` ADD CONSTRAINT `t74_check0` CHECK (gross_amount >= 0)');
        DB::statement('ALTER TABLE `payroll_items` ADD CONSTRAINT `t74_check1` CHECK (deduction_amount >= 0)');
        DB::statement('ALTER TABLE `payroll_items` ADD CONSTRAINT `t74_check2` CHECK (advance_recovery >= 0)');
        DB::statement('ALTER TABLE `payroll_items` ADD CONSTRAINT `t74_check3` CHECK (net_amount >= 0)');
        DB::statement('ALTER TABLE `payroll_items` ADD CONSTRAINT `t74_check4` CHECK (net_amount = gross_amount - deduction_amount - advance_recovery)');
        Schema::table('payroll_item_components', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't75_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('payroll_item_components', function (Blueprint $table): void {
            $table->foreign(['school_id', 'payroll_item_id'], 't75_fk1')->references(['school_id', 'id'])->on('payroll_items')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `payroll_item_components` ADD CONSTRAINT `t75_check0` CHECK (amount >= 0)');
        Schema::table('salary_payments', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't76_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('salary_payments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't76_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('salary_payments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'staff_profile_id'], 't76_fk2')->references(['school_id', 'id'])->on('staff_profiles')->restrictOnDelete();
        });
        Schema::table('salary_payments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'bank_account_id'], 't76_fk3')->references(['school_id', 'id'])->on('bank_accounts')->restrictOnDelete();
        });
        Schema::table('salary_payments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'recorded_by'], 't76_fk4')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `salary_payments` ADD CONSTRAINT `t76_check0` CHECK (amount > 0)');
        Schema::table('salary_payment_allocations', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't77_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('salary_payment_allocations', function (Blueprint $table): void {
            $table->foreign(['school_id', 'salary_payment_id'], 't77_fk1')->references(['school_id', 'id'])->on('salary_payments')->restrictOnDelete();
        });
        Schema::table('salary_payment_allocations', function (Blueprint $table): void {
            $table->foreign(['school_id', 'payroll_item_id'], 't77_fk2')->references(['school_id', 'id'])->on('payroll_items')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `salary_payment_allocations` ADD CONSTRAINT `t77_check0` CHECK (amount > 0)');
        Schema::table('advance_recoveries', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't78_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('advance_recoveries', function (Blueprint $table): void {
            $table->foreign(['school_id', 'staff_advance_id'], 't78_fk1')->references(['school_id', 'id'])->on('staff_advances')->restrictOnDelete();
        });
        Schema::table('advance_recoveries', function (Blueprint $table): void {
            $table->foreign(['school_id', 'payroll_item_id'], 't78_fk2')->references(['school_id', 'id'])->on('payroll_items')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `advance_recoveries` ADD CONSTRAINT `t78_check0` CHECK (amount > 0)');
        Schema::table('timetable_slots', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't79_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('timetable_slots', function (Blueprint $table): void {
            $table->foreign(['school_id', 'section_id'], 't79_fk1')->references(['school_id', 'id'])->on('sections')->restrictOnDelete();
        });
        Schema::table('timetable_slots', function (Blueprint $table): void {
            $table->foreign(['school_id', 'subject_id'], 't79_fk2')->references(['school_id', 'id'])->on('subjects')->restrictOnDelete();
        });
        Schema::table('timetable_slots', function (Blueprint $table): void {
            $table->foreign(['school_id', 'staff_profile_id'], 't79_fk3')->references(['school_id', 'id'])->on('staff_profiles')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `timetable_slots` ADD CONSTRAINT `t79_check0` CHECK (weekday BETWEEN 1 AND 7)');
        DB::statement('ALTER TABLE `timetable_slots` ADD CONSTRAINT `t79_check1` CHECK (ends_at > starts_at)');
        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't80_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't80_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `calendar_events` ADD CONSTRAINT `t80_check0` CHECK (ends_at >= starts_at)');
        Schema::table('ptm_appointments', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't81_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('ptm_appointments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'enrollment_id'], 't81_fk1')->references(['school_id', 'id'])->on('enrollments')->restrictOnDelete();
        });
        Schema::table('ptm_appointments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'guardian_id'], 't81_fk2')->references(['school_id', 'id'])->on('guardians')->restrictOnDelete();
        });
        Schema::table('ptm_appointments', function (Blueprint $table): void {
            $table->foreign(['school_id', 'staff_profile_id'], 't81_fk3')->references(['school_id', 'id'])->on('staff_profiles')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `ptm_appointments` ADD CONSTRAINT `t81_check0` CHECK (ends_at > starts_at)');
        Schema::table('certificate_templates', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't82_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('certificate_issues', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't83_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('certificate_issues', function (Blueprint $table): void {
            $table->foreign(['school_id', 'student_id'], 't83_fk1')->references(['school_id', 'id'])->on('students')->restrictOnDelete();
        });
        Schema::table('certificate_issues', function (Blueprint $table): void {
            $table->foreign(['school_id', 'template_id'], 't83_fk2')->references(['school_id', 'id'])->on('certificate_templates')->restrictOnDelete();
        });
        Schema::table('certificate_issues', function (Blueprint $table): void {
            $table->foreign(['school_id', 'issued_by'], 't83_fk3')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('notices', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't84_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('notices', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't84_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('notices', function (Blueprint $table): void {
            $table->foreign(['school_id', 'published_by'], 't84_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('support_tickets', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't85_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('support_tickets', function (Blueprint $table): void {
            $table->foreign(['school_id', 'opened_by'], 't85_fk1')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('support_replies', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't86_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('support_replies', function (Blueprint $table): void {
            $table->foreign(['school_id', 'support_ticket_id'], 't86_fk1')->references(['school_id', 'id'])->on('support_tickets')->restrictOnDelete();
        });
        Schema::table('support_replies', function (Blueprint $table): void {
            $table->foreign(['author_user_id'], 't86_fk2')->references(['id'])->on('users')->restrictOnDelete();
        });
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't87_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->foreign(['school_id', 'actor_membership_id'], 't87_fk1')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('visitor_logs', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't88_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('visitor_logs', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't88_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE `visitor_logs` ADD CONSTRAINT `t88_check0` CHECK (exited_at IS NULL OR exited_at >= entered_at)');
        Schema::table('student_gate_passes', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't89_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('student_gate_passes', function (Blueprint $table): void {
            $table->foreign(['school_id', 'enrollment_id'], 't89_fk1')->references(['school_id', 'id'])->on('enrollments')->restrictOnDelete();
        });
        Schema::table('student_gate_passes', function (Blueprint $table): void {
            $table->foreign(['school_id', 'pickup_guardian_id'], 't89_fk2')->references(['school_id', 'id'])->on('guardians')->restrictOnDelete();
        });
        Schema::table('student_gate_passes', function (Blueprint $table): void {
            $table->foreign(['school_id', 'approved_by'], 't89_fk3')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('parent_complaints', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't90_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('parent_complaints', function (Blueprint $table): void {
            $table->foreign(['school_id', 'campus_id'], 't90_fk1')->references(['school_id', 'id'])->on('campuses')->restrictOnDelete();
        });
        Schema::table('parent_complaints', function (Blueprint $table): void {
            $table->foreign(['school_id', 'student_id'], 't90_fk2')->references(['school_id', 'id'])->on('students')->restrictOnDelete();
        });
        Schema::table('parent_complaints', function (Blueprint $table): void {
            $table->foreign(['school_id', 'guardian_id'], 't90_fk3')->references(['school_id', 'id'])->on('guardians')->restrictOnDelete();
        });
        Schema::table('parent_complaints', function (Blueprint $table): void {
            $table->foreign(['school_id', 'assigned_to'], 't90_fk4')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('complaint_updates', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't91_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('complaint_updates', function (Blueprint $table): void {
            $table->foreign(['school_id', 'parent_complaint_id'], 't91_fk1')->references(['school_id', 'id'])->on('parent_complaints')->restrictOnDelete();
        });
        Schema::table('complaint_updates', function (Blueprint $table): void {
            $table->foreign(['school_id', 'author_membership_id'], 't91_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
        Schema::table('student_observations', function (Blueprint $table): void {
            $table->foreign(['school_id'], 't92_fk0')->references(['id'])->on('schools')->restrictOnDelete();
        });
        Schema::table('student_observations', function (Blueprint $table): void {
            $table->foreign(['school_id', 'enrollment_id'], 't92_fk1')->references(['school_id', 'id'])->on('enrollments')->restrictOnDelete();
        });
        Schema::table('student_observations', function (Blueprint $table): void {
            $table->foreign(['school_id', 'author_membership_id'], 't92_fk2')->references(['school_id', 'id'])->on('school_memberships')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_observations', function (Blueprint $table): void { $table->dropForeign('t92_fk2'); });
        Schema::table('student_observations', function (Blueprint $table): void { $table->dropForeign('t92_fk1'); });
        Schema::table('student_observations', function (Blueprint $table): void { $table->dropForeign('t92_fk0'); });
        Schema::table('complaint_updates', function (Blueprint $table): void { $table->dropForeign('t91_fk2'); });
        Schema::table('complaint_updates', function (Blueprint $table): void { $table->dropForeign('t91_fk1'); });
        Schema::table('complaint_updates', function (Blueprint $table): void { $table->dropForeign('t91_fk0'); });
        Schema::table('parent_complaints', function (Blueprint $table): void { $table->dropForeign('t90_fk4'); });
        Schema::table('parent_complaints', function (Blueprint $table): void { $table->dropForeign('t90_fk3'); });
        Schema::table('parent_complaints', function (Blueprint $table): void { $table->dropForeign('t90_fk2'); });
        Schema::table('parent_complaints', function (Blueprint $table): void { $table->dropForeign('t90_fk1'); });
        Schema::table('parent_complaints', function (Blueprint $table): void { $table->dropForeign('t90_fk0'); });
        Schema::table('student_gate_passes', function (Blueprint $table): void { $table->dropForeign('t89_fk3'); });
        Schema::table('student_gate_passes', function (Blueprint $table): void { $table->dropForeign('t89_fk2'); });
        Schema::table('student_gate_passes', function (Blueprint $table): void { $table->dropForeign('t89_fk1'); });
        Schema::table('student_gate_passes', function (Blueprint $table): void { $table->dropForeign('t89_fk0'); });
        DB::statement('ALTER TABLE `visitor_logs` DROP CHECK `t88_check0`');
        Schema::table('visitor_logs', function (Blueprint $table): void { $table->dropForeign('t88_fk1'); });
        Schema::table('visitor_logs', function (Blueprint $table): void { $table->dropForeign('t88_fk0'); });
        Schema::table('audit_logs', function (Blueprint $table): void { $table->dropForeign('t87_fk1'); });
        Schema::table('audit_logs', function (Blueprint $table): void { $table->dropForeign('t87_fk0'); });
        Schema::table('support_replies', function (Blueprint $table): void { $table->dropForeign('t86_fk2'); });
        Schema::table('support_replies', function (Blueprint $table): void { $table->dropForeign('t86_fk1'); });
        Schema::table('support_replies', function (Blueprint $table): void { $table->dropForeign('t86_fk0'); });
        Schema::table('support_tickets', function (Blueprint $table): void { $table->dropForeign('t85_fk1'); });
        Schema::table('support_tickets', function (Blueprint $table): void { $table->dropForeign('t85_fk0'); });
        Schema::table('notices', function (Blueprint $table): void { $table->dropForeign('t84_fk2'); });
        Schema::table('notices', function (Blueprint $table): void { $table->dropForeign('t84_fk1'); });
        Schema::table('notices', function (Blueprint $table): void { $table->dropForeign('t84_fk0'); });
        Schema::table('certificate_issues', function (Blueprint $table): void { $table->dropForeign('t83_fk3'); });
        Schema::table('certificate_issues', function (Blueprint $table): void { $table->dropForeign('t83_fk2'); });
        Schema::table('certificate_issues', function (Blueprint $table): void { $table->dropForeign('t83_fk1'); });
        Schema::table('certificate_issues', function (Blueprint $table): void { $table->dropForeign('t83_fk0'); });
        Schema::table('certificate_templates', function (Blueprint $table): void { $table->dropForeign('t82_fk0'); });
        DB::statement('ALTER TABLE `ptm_appointments` DROP CHECK `t81_check0`');
        Schema::table('ptm_appointments', function (Blueprint $table): void { $table->dropForeign('t81_fk3'); });
        Schema::table('ptm_appointments', function (Blueprint $table): void { $table->dropForeign('t81_fk2'); });
        Schema::table('ptm_appointments', function (Blueprint $table): void { $table->dropForeign('t81_fk1'); });
        Schema::table('ptm_appointments', function (Blueprint $table): void { $table->dropForeign('t81_fk0'); });
        DB::statement('ALTER TABLE `calendar_events` DROP CHECK `t80_check0`');
        Schema::table('calendar_events', function (Blueprint $table): void { $table->dropForeign('t80_fk1'); });
        Schema::table('calendar_events', function (Blueprint $table): void { $table->dropForeign('t80_fk0'); });
        DB::statement('ALTER TABLE `timetable_slots` DROP CHECK `t79_check1`');
        DB::statement('ALTER TABLE `timetable_slots` DROP CHECK `t79_check0`');
        Schema::table('timetable_slots', function (Blueprint $table): void { $table->dropForeign('t79_fk3'); });
        Schema::table('timetable_slots', function (Blueprint $table): void { $table->dropForeign('t79_fk2'); });
        Schema::table('timetable_slots', function (Blueprint $table): void { $table->dropForeign('t79_fk1'); });
        Schema::table('timetable_slots', function (Blueprint $table): void { $table->dropForeign('t79_fk0'); });
        DB::statement('ALTER TABLE `advance_recoveries` DROP CHECK `t78_check0`');
        Schema::table('advance_recoveries', function (Blueprint $table): void { $table->dropForeign('t78_fk2'); });
        Schema::table('advance_recoveries', function (Blueprint $table): void { $table->dropForeign('t78_fk1'); });
        Schema::table('advance_recoveries', function (Blueprint $table): void { $table->dropForeign('t78_fk0'); });
        DB::statement('ALTER TABLE `salary_payment_allocations` DROP CHECK `t77_check0`');
        Schema::table('salary_payment_allocations', function (Blueprint $table): void { $table->dropForeign('t77_fk2'); });
        Schema::table('salary_payment_allocations', function (Blueprint $table): void { $table->dropForeign('t77_fk1'); });
        Schema::table('salary_payment_allocations', function (Blueprint $table): void { $table->dropForeign('t77_fk0'); });
        DB::statement('ALTER TABLE `salary_payments` DROP CHECK `t76_check0`');
        Schema::table('salary_payments', function (Blueprint $table): void { $table->dropForeign('t76_fk4'); });
        Schema::table('salary_payments', function (Blueprint $table): void { $table->dropForeign('t76_fk3'); });
        Schema::table('salary_payments', function (Blueprint $table): void { $table->dropForeign('t76_fk2'); });
        Schema::table('salary_payments', function (Blueprint $table): void { $table->dropForeign('t76_fk1'); });
        Schema::table('salary_payments', function (Blueprint $table): void { $table->dropForeign('t76_fk0'); });
        DB::statement('ALTER TABLE `payroll_item_components` DROP CHECK `t75_check0`');
        Schema::table('payroll_item_components', function (Blueprint $table): void { $table->dropForeign('t75_fk1'); });
        Schema::table('payroll_item_components', function (Blueprint $table): void { $table->dropForeign('t75_fk0'); });
        DB::statement('ALTER TABLE `payroll_items` DROP CHECK `t74_check4`');
        DB::statement('ALTER TABLE `payroll_items` DROP CHECK `t74_check3`');
        DB::statement('ALTER TABLE `payroll_items` DROP CHECK `t74_check2`');
        DB::statement('ALTER TABLE `payroll_items` DROP CHECK `t74_check1`');
        DB::statement('ALTER TABLE `payroll_items` DROP CHECK `t74_check0`');
        Schema::table('payroll_items', function (Blueprint $table): void { $table->dropForeign('t74_fk3'); });
        Schema::table('payroll_items', function (Blueprint $table): void { $table->dropForeign('t74_fk2'); });
        Schema::table('payroll_items', function (Blueprint $table): void { $table->dropForeign('t74_fk1'); });
        Schema::table('payroll_items', function (Blueprint $table): void { $table->dropForeign('t74_fk0'); });
        DB::statement('ALTER TABLE `payroll_runs` DROP CHECK `t73_check0`');
        Schema::table('payroll_runs', function (Blueprint $table): void { $table->dropForeign('t73_fk2'); });
        Schema::table('payroll_runs', function (Blueprint $table): void { $table->dropForeign('t73_fk1'); });
        Schema::table('payroll_runs', function (Blueprint $table): void { $table->dropForeign('t73_fk0'); });
        DB::statement('ALTER TABLE `staff_advances` DROP CHECK `t72_check1`');
        DB::statement('ALTER TABLE `staff_advances` DROP CHECK `t72_check0`');
        Schema::table('staff_advances', function (Blueprint $table): void { $table->dropForeign('t72_fk2'); });
        Schema::table('staff_advances', function (Blueprint $table): void { $table->dropForeign('t72_fk1'); });
        Schema::table('staff_advances', function (Blueprint $table): void { $table->dropForeign('t72_fk0'); });
        DB::statement('ALTER TABLE `salary_structure_items` DROP CHECK `t71_check1`');
        DB::statement('ALTER TABLE `salary_structure_items` DROP CHECK `t71_check0`');
        Schema::table('salary_structure_items', function (Blueprint $table): void { $table->dropForeign('t71_fk1'); });
        Schema::table('salary_structure_items', function (Blueprint $table): void { $table->dropForeign('t71_fk0'); });
        DB::statement('ALTER TABLE `salary_structures` DROP CHECK `t70_check0`');
        Schema::table('salary_structures', function (Blueprint $table): void { $table->dropForeign('t70_fk1'); });
        Schema::table('salary_structures', function (Blueprint $table): void { $table->dropForeign('t70_fk0'); });
        DB::statement('ALTER TABLE `staff_attendance_records` DROP CHECK `t69_check0`');
        Schema::table('staff_attendance_records', function (Blueprint $table): void { $table->dropForeign('t69_fk4'); });
        Schema::table('staff_attendance_records', function (Blueprint $table): void { $table->dropForeign('t69_fk3'); });
        Schema::table('staff_attendance_records', function (Blueprint $table): void { $table->dropForeign('t69_fk2'); });
        Schema::table('staff_attendance_records', function (Blueprint $table): void { $table->dropForeign('t69_fk1'); });
        Schema::table('staff_attendance_records', function (Blueprint $table): void { $table->dropForeign('t69_fk0'); });
        DB::statement('ALTER TABLE `report_shares` DROP CHECK `t68_check0`');
        Schema::table('report_shares', function (Blueprint $table): void { $table->dropForeign('t68_fk4'); });
        Schema::table('report_shares', function (Blueprint $table): void { $table->dropForeign('t68_fk3'); });
        Schema::table('report_shares', function (Blueprint $table): void { $table->dropForeign('t68_fk2'); });
        Schema::table('report_shares', function (Blueprint $table): void { $table->dropForeign('t68_fk1'); });
        Schema::table('report_shares', function (Blueprint $table): void { $table->dropForeign('t68_fk0'); });
        DB::statement('ALTER TABLE `consolidated_result_snapshots` DROP CHECK `t67_check0`');
        Schema::table('consolidated_result_snapshots', function (Blueprint $table): void { $table->dropForeign('t67_fk4'); });
        Schema::table('consolidated_result_snapshots', function (Blueprint $table): void { $table->dropForeign('t67_fk3'); });
        Schema::table('consolidated_result_snapshots', function (Blueprint $table): void { $table->dropForeign('t67_fk2'); });
        Schema::table('consolidated_result_snapshots', function (Blueprint $table): void { $table->dropForeign('t67_fk1'); });
        Schema::table('consolidated_result_snapshots', function (Blueprint $table): void { $table->dropForeign('t67_fk0'); });
        Schema::table('exam_candidates', function (Blueprint $table): void { $table->dropForeign('t66_fk2'); });
        Schema::table('exam_candidates', function (Blueprint $table): void { $table->dropForeign('t66_fk1'); });
        Schema::table('exam_candidates', function (Blueprint $table): void { $table->dropForeign('t66_fk0'); });
        DB::statement('ALTER TABLE `assessment_weightages` DROP CHECK `t65_check0`');
        Schema::table('assessment_weightages', function (Blueprint $table): void { $table->dropForeign('t65_fk3'); });
        Schema::table('assessment_weightages', function (Blueprint $table): void { $table->dropForeign('t65_fk2'); });
        Schema::table('assessment_weightages', function (Blueprint $table): void { $table->dropForeign('t65_fk1'); });
        Schema::table('assessment_weightages', function (Blueprint $table): void { $table->dropForeign('t65_fk0'); });
        DB::statement('ALTER TABLE `exam_papers` DROP CHECK `t64_check0`');
        Schema::table('exam_papers', function (Blueprint $table): void { $table->dropForeign('t64_fk1'); });
        Schema::table('exam_papers', function (Blueprint $table): void { $table->dropForeign('t64_fk0'); });
        DB::statement('ALTER TABLE `report_snapshots` DROP CHECK `t63_check0`');
        Schema::table('report_snapshots', function (Blueprint $table): void { $table->dropForeign('t63_fk3'); });
        Schema::table('report_snapshots', function (Blueprint $table): void { $table->dropForeign('t63_fk2'); });
        Schema::table('report_snapshots', function (Blueprint $table): void { $table->dropForeign('t63_fk1'); });
        Schema::table('report_snapshots', function (Blueprint $table): void { $table->dropForeign('t63_fk0'); });
        DB::statement('ALTER TABLE `test_results` DROP CHECK `t62_check2`');
        DB::statement('ALTER TABLE `test_results` DROP CHECK `t62_check1`');
        DB::statement('ALTER TABLE `test_results` DROP CHECK `t62_check0`');
        Schema::table('test_results', function (Blueprint $table): void { $table->dropForeign('t62_fk2'); });
        Schema::table('test_results', function (Blueprint $table): void { $table->dropForeign('t62_fk1'); });
        Schema::table('test_results', function (Blueprint $table): void { $table->dropForeign('t62_fk0'); });
        DB::statement('ALTER TABLE `tests` DROP CHECK `t61_check0`');
        Schema::table('tests', function (Blueprint $table): void { $table->dropForeign('t61_fk4'); });
        Schema::table('tests', function (Blueprint $table): void { $table->dropForeign('t61_fk3'); });
        Schema::table('tests', function (Blueprint $table): void { $table->dropForeign('t61_fk2'); });
        Schema::table('tests', function (Blueprint $table): void { $table->dropForeign('t61_fk1'); });
        Schema::table('tests', function (Blueprint $table): void { $table->dropForeign('t61_fk0'); });
        DB::statement('ALTER TABLE `exam_cycles` DROP CHECK `t60_check0`');
        Schema::table('exam_cycles', function (Blueprint $table): void { $table->dropForeign('t60_fk3'); });
        Schema::table('exam_cycles', function (Blueprint $table): void { $table->dropForeign('t60_fk2'); });
        Schema::table('exam_cycles', function (Blueprint $table): void { $table->dropForeign('t60_fk1'); });
        Schema::table('exam_cycles', function (Blueprint $table): void { $table->dropForeign('t60_fk0'); });
        DB::statement('ALTER TABLE `grading_schemes` DROP CHECK `t59_check0`');
        Schema::table('grading_schemes', function (Blueprint $table): void { $table->dropForeign('t59_fk0'); });
        Schema::table('diary_shares', function (Blueprint $table): void { $table->dropForeign('t58_fk2'); });
        Schema::table('diary_shares', function (Blueprint $table): void { $table->dropForeign('t58_fk1'); });
        Schema::table('diary_shares', function (Blueprint $table): void { $table->dropForeign('t58_fk0'); });
        Schema::table('diary_items', function (Blueprint $table): void { $table->dropForeign('t57_fk3'); });
        Schema::table('diary_items', function (Blueprint $table): void { $table->dropForeign('t57_fk2'); });
        Schema::table('diary_items', function (Blueprint $table): void { $table->dropForeign('t57_fk1'); });
        Schema::table('diary_items', function (Blueprint $table): void { $table->dropForeign('t57_fk0'); });
        DB::statement('ALTER TABLE `diaries` DROP CHECK `t56_check0`');
        Schema::table('diaries', function (Blueprint $table): void { $table->dropForeign('t56_fk3'); });
        Schema::table('diaries', function (Blueprint $table): void { $table->dropForeign('t56_fk2'); });
        Schema::table('diaries', function (Blueprint $table): void { $table->dropForeign('t56_fk1'); });
        Schema::table('diaries', function (Blueprint $table): void { $table->dropForeign('t56_fk0'); });
        DB::statement('ALTER TABLE `attendance_records` DROP CHECK `t55_check0`');
        Schema::table('attendance_records', function (Blueprint $table): void { $table->dropForeign('t55_fk2'); });
        Schema::table('attendance_records', function (Blueprint $table): void { $table->dropForeign('t55_fk1'); });
        Schema::table('attendance_records', function (Blueprint $table): void { $table->dropForeign('t55_fk0'); });
        Schema::table('attendance_sessions', function (Blueprint $table): void { $table->dropForeign('t54_fk2'); });
        Schema::table('attendance_sessions', function (Blueprint $table): void { $table->dropForeign('t54_fk1'); });
        Schema::table('attendance_sessions', function (Blueprint $table): void { $table->dropForeign('t54_fk0'); });
        DB::statement('ALTER TABLE `expenses` DROP CHECK `t53_check0`');
        Schema::table('expenses', function (Blueprint $table): void { $table->dropForeign('t53_fk5'); });
        Schema::table('expenses', function (Blueprint $table): void { $table->dropForeign('t53_fk4'); });
        Schema::table('expenses', function (Blueprint $table): void { $table->dropForeign('t53_fk3'); });
        Schema::table('expenses', function (Blueprint $table): void { $table->dropForeign('t53_fk2'); });
        Schema::table('expenses', function (Blueprint $table): void { $table->dropForeign('t53_fk1'); });
        Schema::table('expenses', function (Blueprint $table): void { $table->dropForeign('t53_fk0'); });
        Schema::table('expense_categories', function (Blueprint $table): void { $table->dropForeign('t52_fk0'); });
        DB::statement('ALTER TABLE `cashier_closings` DROP CHECK `t51_check0`');
        Schema::table('cashier_closings', function (Blueprint $table): void { $table->dropForeign('t51_fk3'); });
        Schema::table('cashier_closings', function (Blueprint $table): void { $table->dropForeign('t51_fk2'); });
        Schema::table('cashier_closings', function (Blueprint $table): void { $table->dropForeign('t51_fk1'); });
        Schema::table('cashier_closings', function (Blueprint $table): void { $table->dropForeign('t51_fk0'); });
        DB::statement('ALTER TABLE `reversal_allocations` DROP CHECK `t50_check0`');
        Schema::table('reversal_allocations', function (Blueprint $table): void { $table->dropForeign('t50_fk2'); });
        Schema::table('reversal_allocations', function (Blueprint $table): void { $table->dropForeign('t50_fk1'); });
        Schema::table('reversal_allocations', function (Blueprint $table): void { $table->dropForeign('t50_fk0'); });
        DB::statement('ALTER TABLE `payment_reversals` DROP CHECK `t49_check0`');
        Schema::table('payment_reversals', function (Blueprint $table): void { $table->dropForeign('t49_fk2'); });
        Schema::table('payment_reversals', function (Blueprint $table): void { $table->dropForeign('t49_fk1'); });
        Schema::table('payment_reversals', function (Blueprint $table): void { $table->dropForeign('t49_fk0'); });
        DB::statement('ALTER TABLE `payment_allocations` DROP CHECK `t48_check0`');
        Schema::table('payment_allocations', function (Blueprint $table): void { $table->dropForeign('t48_fk2'); });
        Schema::table('payment_allocations', function (Blueprint $table): void { $table->dropForeign('t48_fk1'); });
        Schema::table('payment_allocations', function (Blueprint $table): void { $table->dropForeign('t48_fk0'); });
        DB::statement('ALTER TABLE `payments` DROP CHECK `t47_check0`');
        Schema::table('payments', function (Blueprint $table): void { $table->dropForeign('t47_fk4'); });
        Schema::table('payments', function (Blueprint $table): void { $table->dropForeign('t47_fk3'); });
        Schema::table('payments', function (Blueprint $table): void { $table->dropForeign('t47_fk2'); });
        Schema::table('payments', function (Blueprint $table): void { $table->dropForeign('t47_fk1'); });
        Schema::table('payments', function (Blueprint $table): void { $table->dropForeign('t47_fk0'); });
        Schema::table('bank_accounts', function (Blueprint $table): void { $table->dropForeign('t46_fk1'); });
        Schema::table('bank_accounts', function (Blueprint $table): void { $table->dropForeign('t46_fk0'); });
        DB::statement('ALTER TABLE `invoice_items` DROP CHECK `t45_check3`');
        DB::statement('ALTER TABLE `invoice_items` DROP CHECK `t45_check2`');
        DB::statement('ALTER TABLE `invoice_items` DROP CHECK `t45_check1`');
        DB::statement('ALTER TABLE `invoice_items` DROP CHECK `t45_check0`');
        Schema::table('invoice_items', function (Blueprint $table): void { $table->dropForeign('t45_fk2'); });
        Schema::table('invoice_items', function (Blueprint $table): void { $table->dropForeign('t45_fk1'); });
        Schema::table('invoice_items', function (Blueprint $table): void { $table->dropForeign('t45_fk0'); });
        DB::statement('ALTER TABLE `invoices` DROP CHECK `t44_check0`');
        Schema::table('invoices', function (Blueprint $table): void { $table->dropForeign('t44_fk1'); });
        Schema::table('invoices', function (Blueprint $table): void { $table->dropForeign('t44_fk0'); });
        DB::statement('ALTER TABLE `student_concessions` DROP CHECK `t43_check3`');
        DB::statement('ALTER TABLE `student_concessions` DROP CHECK `t43_check2`');
        DB::statement('ALTER TABLE `student_concessions` DROP CHECK `t43_check1`');
        DB::statement('ALTER TABLE `student_concessions` DROP CHECK `t43_check0`');
        Schema::table('student_concessions', function (Blueprint $table): void { $table->dropForeign('t43_fk3'); });
        Schema::table('student_concessions', function (Blueprint $table): void { $table->dropForeign('t43_fk2'); });
        Schema::table('student_concessions', function (Blueprint $table): void { $table->dropForeign('t43_fk1'); });
        Schema::table('student_concessions', function (Blueprint $table): void { $table->dropForeign('t43_fk0'); });
        DB::statement('ALTER TABLE `fee_plan_items` DROP CHECK `t42_check0`');
        Schema::table('fee_plan_items', function (Blueprint $table): void { $table->dropForeign('t42_fk2'); });
        Schema::table('fee_plan_items', function (Blueprint $table): void { $table->dropForeign('t42_fk1'); });
        Schema::table('fee_plan_items', function (Blueprint $table): void { $table->dropForeign('t42_fk0'); });
        DB::statement('ALTER TABLE `fee_plans` DROP CHECK `t41_check0`');
        Schema::table('fee_plans', function (Blueprint $table): void { $table->dropForeign('t41_fk3'); });
        Schema::table('fee_plans', function (Blueprint $table): void { $table->dropForeign('t41_fk2'); });
        Schema::table('fee_plans', function (Blueprint $table): void { $table->dropForeign('t41_fk1'); });
        Schema::table('fee_plans', function (Blueprint $table): void { $table->dropForeign('t41_fk0'); });
        Schema::table('fee_heads', function (Blueprint $table): void { $table->dropForeign('t40_fk0'); });
        Schema::table('student_documents', function (Blueprint $table): void { $table->dropForeign('t39_fk2'); });
        Schema::table('student_documents', function (Blueprint $table): void { $table->dropForeign('t39_fk1'); });
        Schema::table('student_documents', function (Blueprint $table): void { $table->dropForeign('t39_fk0'); });
        DB::statement('ALTER TABLE `enrollments` DROP CHECK `t38_check0`');
        Schema::table('enrollments', function (Blueprint $table): void { $table->dropForeign('t38_fk2'); });
        Schema::table('enrollments', function (Blueprint $table): void { $table->dropForeign('t38_fk1'); });
        Schema::table('enrollments', function (Blueprint $table): void { $table->dropForeign('t38_fk0'); });
        Schema::table('student_guardians', function (Blueprint $table): void { $table->dropForeign('t37_fk2'); });
        Schema::table('student_guardians', function (Blueprint $table): void { $table->dropForeign('t37_fk1'); });
        Schema::table('student_guardians', function (Blueprint $table): void { $table->dropForeign('t37_fk0'); });
        Schema::table('guardians', function (Blueprint $table): void { $table->dropForeign('t36_fk0'); });
        Schema::table('students', function (Blueprint $table): void { $table->dropForeign('t35_fk2'); });
        Schema::table('students', function (Blueprint $table): void { $table->dropForeign('t35_fk1'); });
        Schema::table('students', function (Blueprint $table): void { $table->dropForeign('t35_fk0'); });
        Schema::table('admission_applications', function (Blueprint $table): void { $table->dropForeign('t34_fk4'); });
        Schema::table('admission_applications', function (Blueprint $table): void { $table->dropForeign('t34_fk3'); });
        Schema::table('admission_applications', function (Blueprint $table): void { $table->dropForeign('t34_fk2'); });
        Schema::table('admission_applications', function (Blueprint $table): void { $table->dropForeign('t34_fk1'); });
        Schema::table('admission_applications', function (Blueprint $table): void { $table->dropForeign('t34_fk0'); });
        Schema::table('admission_inquiries', function (Blueprint $table): void { $table->dropForeign('t33_fk1'); });
        Schema::table('admission_inquiries', function (Blueprint $table): void { $table->dropForeign('t33_fk0'); });
        Schema::table('teacher_assignments', function (Blueprint $table): void { $table->dropForeign('t32_fk3'); });
        Schema::table('teacher_assignments', function (Blueprint $table): void { $table->dropForeign('t32_fk2'); });
        Schema::table('teacher_assignments', function (Blueprint $table): void { $table->dropForeign('t32_fk1'); });
        Schema::table('teacher_assignments', function (Blueprint $table): void { $table->dropForeign('t32_fk0'); });
        Schema::table('section_subjects', function (Blueprint $table): void { $table->dropForeign('t31_fk2'); });
        Schema::table('section_subjects', function (Blueprint $table): void { $table->dropForeign('t31_fk1'); });
        Schema::table('section_subjects', function (Blueprint $table): void { $table->dropForeign('t31_fk0'); });
        Schema::table('subjects', function (Blueprint $table): void { $table->dropForeign('t30_fk0'); });
        Schema::table('sections', function (Blueprint $table): void { $table->dropForeign('t29_fk3'); });
        Schema::table('sections', function (Blueprint $table): void { $table->dropForeign('t29_fk2'); });
        Schema::table('sections', function (Blueprint $table): void { $table->dropForeign('t29_fk1'); });
        Schema::table('sections', function (Blueprint $table): void { $table->dropForeign('t29_fk0'); });
        Schema::table('classes', function (Blueprint $table): void { $table->dropForeign('t28_fk0'); });
        DB::statement('ALTER TABLE `academic_terms` DROP CHECK `t27_check0`');
        Schema::table('academic_terms', function (Blueprint $table): void { $table->dropForeign('t27_fk1'); });
        Schema::table('academic_terms', function (Blueprint $table): void { $table->dropForeign('t27_fk0'); });
        DB::statement('ALTER TABLE `academic_sessions` DROP CHECK `t26_check0`');
        Schema::table('academic_sessions', function (Blueprint $table): void { $table->dropForeign('t26_fk0'); });
        DB::statement('ALTER TABLE `leave_requests` DROP CHECK `t25_check0`');
        Schema::table('leave_requests', function (Blueprint $table): void { $table->dropForeign('t25_fk2'); });
        Schema::table('leave_requests', function (Blueprint $table): void { $table->dropForeign('t25_fk1'); });
        Schema::table('leave_requests', function (Blueprint $table): void { $table->dropForeign('t25_fk0'); });
        Schema::table('staff_profiles', function (Blueprint $table): void { $table->dropForeign('t24_fk1'); });
        Schema::table('staff_profiles', function (Blueprint $table): void { $table->dropForeign('t24_fk0'); });
        Schema::table('files', function (Blueprint $table): void { $table->dropForeign('t23_fk2'); });
        Schema::table('files', function (Blueprint $table): void { $table->dropForeign('t23_fk1'); });
        Schema::table('files', function (Blueprint $table): void { $table->dropForeign('t23_fk0'); });
        Schema::table('membership_campuses', function (Blueprint $table): void { $table->dropForeign('t22_fk2'); });
        Schema::table('membership_campuses', function (Blueprint $table): void { $table->dropForeign('t22_fk1'); });
        Schema::table('membership_campuses', function (Blueprint $table): void { $table->dropForeign('t22_fk0'); });
        Schema::table('membership_roles', function (Blueprint $table): void { $table->dropForeign('t21_fk2'); });
        Schema::table('membership_roles', function (Blueprint $table): void { $table->dropForeign('t21_fk1'); });
        Schema::table('membership_roles', function (Blueprint $table): void { $table->dropForeign('t21_fk0'); });
        Schema::table('school_memberships', function (Blueprint $table): void { $table->dropForeign('t20_fk1'); });
        Schema::table('school_memberships', function (Blueprint $table): void { $table->dropForeign('t20_fk0'); });
        Schema::table('school_settings', function (Blueprint $table): void { $table->dropForeign('t19_fk0'); });
        DB::statement('ALTER TABLE `subscription_payments` DROP CHECK `t18_check0`');
        Schema::table('subscription_payments', function (Blueprint $table): void { $table->dropForeign('t18_fk1'); });
        Schema::table('subscription_payments', function (Blueprint $table): void { $table->dropForeign('t18_fk0'); });
        DB::statement('ALTER TABLE `subscription_invoices` DROP CHECK `t17_check0`');
        Schema::table('subscription_invoices', function (Blueprint $table): void { $table->dropForeign('t17_fk1'); });
        Schema::table('subscription_invoices', function (Blueprint $table): void { $table->dropForeign('t17_fk0'); });
        DB::statement('ALTER TABLE `subscriptions` DROP CHECK `t16_check1`');
        DB::statement('ALTER TABLE `subscriptions` DROP CHECK `t16_check0`');
        Schema::table('subscriptions', function (Blueprint $table): void { $table->dropForeign('t16_fk1'); });
        Schema::table('subscriptions', function (Blueprint $table): void { $table->dropForeign('t16_fk0'); });
        Schema::table('campuses', function (Blueprint $table): void { $table->dropForeign('t15_fk0'); });
        Schema::table('role_permissions', function (Blueprint $table): void { $table->dropForeign('t14_fk1'); });
        Schema::table('role_permissions', function (Blueprint $table): void { $table->dropForeign('t14_fk0'); });
    }
};
