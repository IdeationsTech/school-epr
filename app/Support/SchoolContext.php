<?php
namespace App\Support;
use LogicException;
/** Request/job-scoped. Set only after membership authorization, never from unchecked request IDs. */
final class SchoolContext
{
    private ?int $schoolId = null;
    public function set(int $id): void {
        if ($id < 1) { throw new LogicException('Valid school ID required.'); }
        $this->schoolId = $id;
    }
    public function id(): ?int { return $this->schoolId; }
    public function requireId(): int { return $this->schoolId ?? throw new LogicException('School context required.'); }
    public function clear(): void { $this->schoolId = null; }
}
