<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CustomResposeFormatter;

class TransactionController extends Controller
{
    public function all(Request $request) 
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $status = $request->input('status');

        if($id) 
        {
            $transaction = Transaction::with(['items.product'])->find($id);

            if($transaction) {
                return CustomResponseFormatter::success(
                    $transaction,
                    'Data Transaksi Berhasil di ambil'
                );
            }
            else {
                return CustomResponseFormatter::error(
                    null,
                    'Data Transaksi tidak ada',
                    404
                );
            }
        }

        $transaction = Transaction::with(['items.product'])->where('users_id', Auth::user()->id);

        if($status) {
            $transaction->where('status', $status);
        }

        return CustomResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list transaksi berhasil diambil'
        );
    }
}
