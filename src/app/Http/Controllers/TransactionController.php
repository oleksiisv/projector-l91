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
    /**
     * @return Application|Factory|View
     */
    public function list()
    {
        $transactions = DB::table('transactions')->get();

        return view('transaction.list', [
            'count' => $transactions->count(),
            'transactions' => $transactions,
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
        $factory = new TransactionFactory();
        for ($i = 0; $i < 20; $i++){
            $params = $factory->definition();
            $url = "http://localhost/transaction/create?" . http_build_query($params) . "\r\n";
            file_put_contents('siege_urls.txt', $url, FILE_APPEND);
        }


        return 'sample requests written to siege_urls.txt';
    }
}
