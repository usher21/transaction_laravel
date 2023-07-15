<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserRessource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TransactionRessource;

class TransactionController extends Controller
{
    public function index()
    {
        return TransactionRessource::collection(Transaction::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                "amount" => "required|integer|min:500",
                "type" => "required",
                "phone_receiver" => $request->type === 2 ? "required|regex:/^(7[76508]{1})(\\d{7})$/" : '',
                "user_id" => "required|integer"
            ],
            [
                "amount.required" => "le montant de la transaction est requis",
                "amount.integer" => "le montant doit être un nombre entier",
                "amount.min" => "le montant de la transaction doit être au minimum 500",
                "type.required" => "le type de transaction est requis",
                "user_id.required" => "l'identifiant de l'utilisteur est requis",
                "user_id.integer" => "l'identifiant de l'utilisteur doit être un nombre entier",
                "phone_receiver.required" => "le numero du destinataire est requis",
                "phone_receiver.regex" => "Le numéro de téléphone n'est pas valide",
            ]
        )->validated();

        $user = User::where('id', $request->user_id)->first();

        if (!$user) {
            return ["message" => "l'utilisateur est introuvable"];
        }

        if ($request->type == 0) {

            if ($user->balance < $request->amount) {
                return ["message" => "Transaction échouée !! votre solde est insuffisant"];
            }

            $newTransaction = $this->withdrawMoney($user, $request->amount, $validator);
            return new TransactionRessource($newTransaction);
        } elseif ($request->type == 1) {
            $newTransaction = $this->depositMoney($user, $request->amount, $validator);
            return new TransactionRessource($newTransaction);
        } else {
            if ($user->balance < $request->amount) {
                return ["message" => "le solde est insuffisant"];
            }

            $newTransaction = $this->transfertMoney($user, $request->amount, $request->phone_receiver, $validator);
            return new TransactionRessource($newTransaction);
        }
    }

    public function withdrawMoney(User $sender, int $amount, array $newTransaction)
    {
        DB::beginTransaction();
        try {
            $sender->update(['balance' => $sender->balance - $amount]);
            $transaction = Transaction::create($newTransaction);
            DB::commit();
            return $transaction;
        } catch (\Illuminate\Database\QueryException $exception) {
            DB::rollBack();
            dd($exception);
        }
    }

    public function depositMoney(User $sender, int $amount, array $newTransaction)
    {
        DB::beginTransaction();
        try {
            $sender->update(['balance'=> $sender->balance + $amount]);
            $transaction = Transaction::create($newTransaction);
            DB::commit();
            return $transaction;
        } catch (\Illuminate\Database\QueryException $exception) {
            DB::rollBack();
            dd($exception);
        }
    }

    public function transfertMoney(User $sender, int $amount, string $phoneReceiver, array $newTransaction)
    {
        DB::beginTransaction();
        try {
            $receiver = User::where('phone', $phoneReceiver)->first();

            if (!$receiver) {
                throw new Exception("l'utilisateur n'existe pas");
            }

            $sender->update(['balance' => $sender->balance - $amount]);
            $receiver->update(['balance'=> $receiver->balance + $amount]);

            $transaction = Transaction::create($newTransaction);
            DB::commit();
            return $transaction;
        } catch(Exception $exception) {
            DB::rollBack();
            dd($exception);
        } catch (\Illuminate\Database\QueryException $exception) {
            DB::rollBack();
            dd($exception);
        }
    }
}
