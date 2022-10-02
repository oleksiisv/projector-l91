<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class Transaction extends Model
{
    use HasFactory;

    /**
     * @param $transaction
     *
     * @return void
     */
    public function saveToCache($transaction)
    {
        $redis = Redis::connection('cache');
        Redis::connection('cache')->set($transaction->psp_reference, json_encode($transaction), 'EX', 60);
        echo 'Saved to cache' . $transaction->psp_reference . '<br>';

        return $transaction;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getFromCache($key)
    {
        $redis = Redis::connection('cache');
        echo 'Loaded from cache:' . $key. '(ttl:' . $redis->ttl($key) . ')' . '<br>';
        return json_decode($redis->get($key));
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getTransactions()
    {
        return DB::table('transactions')->get('psp_reference');
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getTransaction($key)
    {
        $result = $this->getFromCache($key);
        if ($result == null) {
            $record = DB::select('select * from transactions where psp_reference = ?', [$key]);
            $result = $record[0];
            echo 'Loaded from database:' . $key . '<br>';
            $this->saveToCache($result);
        }

        return $result;
    }
}
