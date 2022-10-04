<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class Transaction extends Model
{
    const LOG_DATA = 0;
    const TTl = 30;
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
     * @param $fromCache
     *
     * @return mixed|null
     */
    public function getTransaction($key, $fromCache = true)
    {
        $result = null;
        if ($fromCache) {
            $result = $this->getFromCache($key);
        }
        if ($result == null) {
            $start = time();
            $record = DB::select('select * from transactions where psp_reference = ?', [$key]);
            $result = $record[0];
            $delta = time() - $start;
            $result->delta = $delta;
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
        $this->redis->set($transaction->psp_reference, json_encode($transaction), 'EX', self::TTl);
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
        $result = json_decode($this->redis->get($key));
        if ($result !== null) {
            $ttl = $this->redis->ttl($key);
            $this->probabilisticExarlyExpire($result, $ttl);
            $this->log('Loaded from cache:' . $key . '(ttl:' . $ttl . ')');
        } else {
            $result = $this->getTransaction($key, false);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getTransactions()
    {
        $keys = $this->getTransactionsKeys();
        foreach ($keys as $key) {
            $result[] = $this->getTransaction($key);
        }

        return $result;
    }

    /**
     * @return mixed
     */
    private function getTransactionsKeys()
    {
        $result = json_decode($this->redis->get('transactions_keys'));
        if ($result == null) {
            $result = Transaction::pluck('psp_reference')->toArray();
            //EX is not defined to set it to -1 by default
            $this->redis->set('transactions_keys', json_encode($result));
            $this->log('Transactions saved to cache');
        }

        return $result;
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

    public function probabilisticExarlyExpire($result, $ttl)
    {
        $key = $result->psp_reference;
        $ttlLeft = $ttl / self::TTl;
        $cacheReset = false;
        $chance = 0;
        if (0.3 > $ttlLeft && $ttlLeft >= 0.2) {
            $chance = 40; //40%
            if (random_int(1, 100) <= $chance) {
                $cacheReset = true;
                $this->getTransaction($key, false);
            }
        }
        if (0.2 > $ttlLeft && $ttlLeft >= 0.1) {
            $chance = 70; //70%
            if (random_int(1, 100) <= $chance) {
                $cacheReset = true;
                $this->getTransaction($key, false);
            }
        }
        if (0.1 > $ttlLeft) {
            $chance = 90; //90%
            if (random_int(1, 100) <= $chance) {
                $cacheReset = true;
                $this->getTransaction($key, false);
            }
        }
        file_put_contents('cache.log',
            sprintf("key: %s, ttl: %s, chance: %s, cache reset: %s \n", $key, $ttl, $chance,
                $cacheReset),
            FILE_APPEND);
    }

    /**
     * @return void
     */
    public function siegeUrls()
    {
        $transactions = $this->getTransactions();
        foreach ($transactions as $transaction) {
            $url = "http://localhost/transaction/view?key=" . $transaction->psp_reference . "\r\n";
            file_put_contents('siege_urls.txt', $url, FILE_APPEND);
        }
    }
}
