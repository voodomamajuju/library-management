<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'member_id' => $this->member_id,
            'borrowed_at' => $this->borrowed_at,
            'due_at' => $this->due_at,
            'returned_at' => $this->returned_at,
            'book' => $this->book ? [
                'id' => $this->book->id,
                'title' => $this->book->title,
                'author' => $this->book->author,
                'isbn' => $this->book->isbn,
                'category' => $this->book->category,
            ] : null,
            'member' => $this->member ? [
                'id' => $this->member->id,
                'full_name' => $this->member->full_name,
                'email' => $this->member->email,
                'phone' => $this->member->phone,
                'status' => $this->member->status,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}