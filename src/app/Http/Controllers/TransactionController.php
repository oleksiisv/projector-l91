<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use Database\Factories\TransactionFactory;

class TransactionController extends Controller
{
    private Transaction $transaction;

    /**
     * @param Transaction $transaction
     */
    public function __construct(
        Transaction $transaction
    ) {
        $this->transaction = $transaction;
    }

    /**
     * @return Application|Factory|View
     */
    public function list()
    {
        $result = $this->transaction->getTransactions();

        return view('transaction.list', [
            'transactions' => $result
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function view(Request $request)
    {
        $key = $request->input('key');
        $result = $this->transaction->getTransaction($key);
        return view('transaction.view', [
            'key' => $key,
            'transaction' => $result
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function create(Request $request): RedirectResponse
    {
        DB::table('transactions')->insert($request->input());

        return redirect()->action([TransactionController::class, 'list']);
    }

    /**
     * @return string
     */
    public function sample(): string
    {
        $this->transaction->siegeUrls();

        return 'sample requests written to siege_urls.txt';
    }
}
