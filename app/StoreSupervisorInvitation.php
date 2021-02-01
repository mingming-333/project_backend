<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreSupervisorInvitation extends Model
{
    protected $table = 'store_supervisor_invitation';
    protected $primaryKey = "id";

    protected $fillable = [
        'StoreID','Email','Token'
    ];
}
