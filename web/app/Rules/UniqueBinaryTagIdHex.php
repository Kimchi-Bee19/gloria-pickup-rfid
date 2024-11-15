<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueBinaryTagIdHex implements Rule
{
    private $ignoreId;

    public function __construct($ignoreId = null)
    {
        $this->ignoreId = $ignoreId;
    }

    public function passes($attribute, $value)
    {
        return !DB::table('vehicle_identities')
            ->whereRaw('tag_id = decode(?, \'hex\')', [$value])
            ->where('id', '!=', $this->ignoreId)
            ->exists();
    }

    public function message()
    {
        return 'RFID Tag sudah didaftarkan.';
    }
}
