<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "amount" => $this->amount,
            "action" => $this->type,
            "phone_receiver" => $this->phone_receiver,
            "date" => $this->created_at,
            "sender" => new UserRessource($this->user)
        ];
    }
}
