<?php
namespace Tests\Feature;
use App\Models\Domain\Campus;
use App\Support\SchoolContext;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        $database = config('database.connections.mysql.database');
        if (!is_string($database) || !str_ends_with($database, '_test')) {
            throw new \RuntimeException('Refusing database tests outside an explicitly named _test database.');
        }
    }

    private function school(string $code): int
    {
        return DB::table('schools')->insertGetId([
            'name'=>$code,'code'=>$code,'timezone'=>'Asia/Karachi','currency'=>'PKR','status'=>'active',
        ]);
    }

    public function test_all_declared_tables_exist(): void
    {
        $spec=json_decode(file_get_contents(database_path('schema/schema.json')),true,512,JSON_THROW_ON_ERROR);
        foreach ($spec as $table) {
            $this->assertTrue(Schema::hasTable($table['name']),$table['name']);
        }
    }

    public function test_default_seeding_is_repeatable_and_has_no_demo_school(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $this->seed(ReferenceDataSeeder::class);
        $this->assertDatabaseCount('roles',5);
        $this->assertDatabaseCount('schools',0);
        $this->assertDatabaseCount('users',0);
        $this->assertDatabaseCount('plans',1);
    }

    public function test_tenant_model_queries_fail_closed_and_scope_to_selected_school(): void
    {
        $a=$this->school('A'); $b=$this->school('B');
        foreach ([$a,$b] as $sid) {
            DB::table('campuses')->insert(['school_id'=>$sid,'name'=>'Main','code'=>'MAIN','status'=>'active']);
        }
        app(SchoolContext::class)->clear();
        $this->assertSame(0,Campus::count());
        app(SchoolContext::class)->set($a);
        $this->assertSame(1,Campus::count());
        $this->assertSame($a,(int)Campus::firstOrFail()->school_id);
    }

    public function test_database_rejects_cross_school_foreign_key(): void
    {
        $a=$this->school('A'); $b=$this->school('B');
        $user=DB::table('users')->insertGetId(['name'=>'Test','email'=>'test@example.test','password'=>'not-a-login']);
        $membership=DB::table('school_memberships')->insertGetId(['school_id'=>$a,'user_id'=>$user,'status'=>'active']);
        $campus=DB::table('campuses')->insertGetId(['school_id'=>$b,'name'=>'Main','code'=>'MAIN','status'=>'active']);
        $this->expectException(QueryException::class);
        DB::table('membership_campuses')->insert(['school_id'=>$a,'membership_id'=>$membership,'campus_id'=>$campus]);
    }

    public function test_negative_financial_amount_is_rejected(): void
    {
        $sid=$this->school('A');
        $campus=DB::table('campuses')->insertGetId(['school_id'=>$sid,'name'=>'Main','code'=>'MAIN','status'=>'active']);
        $category=DB::table('expense_categories')->insertGetId(['school_id'=>$sid,'name'=>'Utilities','code'=>'UTIL']);
        $this->expectException(QueryException::class);
        DB::table('expenses')->insert(['school_id'=>$sid,'campus_id'=>$campus,'category_id'=>$category,
            'amount'=>'-1.00','incurred_on'=>'2026-10-15','description'=>'Invalid','method'=>'cash','status'=>'draft']);
    }

    public function test_health_route_is_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
