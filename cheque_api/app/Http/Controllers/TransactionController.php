<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Cheque;
use App\Models\Partition;
use App\Models\Position;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function get_for_cheque(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);
        $cheque = Cheque::where('name', $validated['name'])->first();
        if (!$cheque)
        {
            return response([
                'error' => 'Cheque '.$validated['name'].' is absent'
            ]);
        }

        $bill = Bill::where('cheque_id', $cheque->id)->first();
        if ($bill)
        {
            $transactions = Transaction::where('bill_id', $bill->id)->get();
            $response = [];
            foreach ($transactions as $transaction)
            {
                $payer = User::where('id', $transaction->payer_id)->first();
                $receiver = User::where('id', $transaction->receiver_id)->first();
                $transaction['payer'] = $payer;
                $transaction['receiver'] = $receiver;
                array_push($response, $transaction);
            }
            return response($response);
        }

        $bill = Bill::create([
            'cheque_id' => $cheque->id,
        ]);
        $response = [];
        $positions = Position::where('cheque_id', $cheque->id)->get();
        $transactions = [];
        foreach ($positions as $position)
        {
            $partitions = Partition::where('position_id', $position->id)->get();
            foreach ($partitions as $partition)
            {
                $user = User::where('id', $partition->user_id)->first();
                $amt = $position->sum / count($partitions);
                if (!array_key_exists($user->id, $transactions))
                {
                    $transactions[$user->id] = $amt;
                }
                else
                {
                    $transactions[$user->id] += $amt;
                }
            }
        }
        foreach ($transactions as $payer_id=>$sum)
        {
            $payer = User::where('id', $payer_id)->first();
            $transaction = Transaction::create([
                'bill_id' => $bill->id,
                'payer_id' => $payer->id,
                'sum' => $sum,
            ]);
            $transaction['payer'] = $payer;
            array_push($response, $transaction);
        }
        return response($response, 201);
    }
}
