<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class Transaction extends Model
{
    const LOG_DATA =0;
    use HasFactory;

    private $redis;

    /**
     * @param $redis
     */
    public function __construct()
    {
        parent::__construct();
        $this->redis = Redis::connection('cache');
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
            $this->log('Loaded from database:' . $key);
            $this->saveToCache($result);
        }

        return $result;
    }

    /**
     * @param $transaction
     *
     * @return void
     */
    private function saveToCache($transaction)
    {
        $this->redis->set($transaction->psp_reference, json_encode($transaction), 'EX', 60);
        $this->log('Saved to cache' . $transaction->psp_reference);

        return $transaction;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    private function getFromCache($key)
    {
        $this->log('Loaded from cache:' . $key . '(ttl:' . $this->redis->ttl($key) . ')');

        return json_decode($this->redis->get($key));
    }

    /**
     * @return array
     */
    public function getTransactions()
    {
        $transactions = $this->getTransactionsKeys();
        if ($transactions == null) {
            $this->cacheTransactionsKeys();
            $transactions = $this->getTransactions();
        }
        $this->log('Transactions loaded from cache');
        $result = [];
        foreach ($transactions as $key) {
            $result[] = $this->getTransaction($key);
        }

        return $result;
    }

    /**
     * @return mixed
     */
    private function getTransactionsKeys()
    {
        return json_decode($this->redis->get('transactions_keys'));
    }

    /**
     * @return void
     */
    private function cacheTransactionsKeys()
    {
        $transactionKeys = Transaction::pluck('psp_reference')->toArray();
        //EX is not defined to set it to -1 by default
        $this->redis->set('transactions_keys', json_encode($transactionKeys));
        $this->log('Transactions saved to cache');
    }

    /**
     * @param $message
     *
     * @return void
     */
    private function log($message)
    {
        if (self::LOG_DATA !== 1) {
            return;
        }
        echo $message . '<br>';
    }
}
