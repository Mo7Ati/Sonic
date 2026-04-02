<?php

namespace App\Models;

use App\Enums\DaysEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchTimes extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'index',
        'from',
        'to',
    ];

    protected $casts = [

        'index' => 'integer',
    ];

    /**
     * Get the branch that owns the time.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getIndexLabelAttribute()
    {
        return DaysEnum::fromIndex($this->index);
    }
}
